<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Authors of \OCA\Files_Sharing\Helper
 *
 * @copyright Olivier Paroz 2014-2015
 * @copyright Authors of \OCA\Files_Sharing\Helper 2014-2015
 */

namespace OCA\GalleryPlus\Service;

use OCP\Files\Folder;
use OCP\IServerContainer;
use OCP\IUser;
use OCP\ISession;
use OCP\Share;
use OCP\IUserManager;
use OCP\Security\IHasher;

use OCP\AppFramework\Http;

use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Builds the environment so that the services have access to the proper user,
 * folder and files
 *
 * @package OCA\GalleryPlus\Service
 */
class EnvironmentService extends Service {

	/**
	 * @type string
	 */
	private $userId;
	/**
	 * @type Folder|null
	 */
	private $userFolder;
	/**
	 * @type IUserManager
	 */
	private $userManager;
	/**
	 * @type IServerContainer
	 */
	private $serverContainer;
	/**
	 * @type IHasher
	 * */
	private $hasher;
	/**
	 * @type ISession
	 * */
	private $session;
	/**
	 * @type array
	 */
	private $linkItem;
	/**
	 * @type array
	 */
	private $origShareData = array();

	/**
	 * @param string $appName
	 * @param string|null $userId
	 * @param Folder|null $userFolder
	 * @param IUserManager $userManager
	 * @param IServerContainer $serverContainer
	 * @param IHasher $hasher
	 * @param ISession $session
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		$appName,
		$userId,
		$userFolder,
		IUserManager $userManager,
		IServerContainer $serverContainer,
		IHasher $hasher,
		ISession $session,
		SmarterLogger $logger
	) {
		parent::__construct($appName, $logger);

		$this->userId = $userId;
		$this->userFolder = $userFolder;
		$this->userManager = $userManager;
		$this->serverContainer = $serverContainer;
		$this->hasher = $hasher;
		$this->session = $session;
	}

	/**
	 * Validates a token to make sure its linked to a valid resource
	 *
	 * Logic mostly duplicated from @see \OCA\Files_Sharing\Helper
	 * @fixme setIncognitoMode in 8.1 https://github.com/owncloud/core/pull/12912
	 *
	 * @param string $token
	 */
	public function checkToken($token) {
		// Allows a logged in user to access public links
		\OC_User::setIncognitoMode(true);

		$linkItem = Share::getShareByToken($token, false);

		$this->checkLinkItemExists($linkItem);
		$this->checkLinkItemIsValid($linkItem, $token);
		$this->checkItemType($linkItem);

		// Checks passed, let's store the linkItem
		$this->linkItem = $linkItem;
	}

	/**
	 * Checks if a password is required and validates it if it is provided in
	 * the request
	 *
	 * @param string $password optional password
	 */
	public function checkAuthorisation($password) {
		$passwordRequired = isset($this->linkItem['share_with']);

		if ($passwordRequired) {
			if ($password !== null) {
				$this->authenticate($password);
			} else {
				$this->checkSession();
			}
		}
	}

	/**
	 * Sets up the environment based on a token
	 *
	 * The token has already been vetted by checkToken via the token checking
	 * middleware
	 */
	public function setupTokenBasedEnv() {
		$linkItem = $this->linkItem;
		// Resolves reshares down to the last real share
		$rootLinkItem = Share::resolveReShare($linkItem);
		$origShareOwner = $rootLinkItem['uid_owner'];
		$user = $this->getUser($origShareOwner);
		$origOwnerDisplayName = $user->getDisplayName();
		// Setup FS for user
		\OC_Util::tearDownFS(); // FIXME: Private API
		\OC_Util::setupFS($origShareOwner); // FIXME: Private API
		$fileSource = $linkItem['file_source'];
		$origShareRelPath = $this->getPath($origShareOwner, $fileSource);

		// Checks passed, let's store the data
		$this->origShareData = [
			'origShareOwner'       => $origShareOwner,
			'origOwnerDisplayName' => $origOwnerDisplayName,
			'origShareRelPath'     => $origShareRelPath
		];
	}

	/**
	 * Returns an array with details about the environment
	 *
	 * @return array various environment variables
	 */
	public function getEnv() {
		$linkItem = $this->linkItem;

		if (isset($linkItem)) {
			$env = $this->getTokenBasedEnv($linkItem);
		} else {
			$env = array(
				'owner'        => $this->userId,
				'relativePath' => '/',
				'folder'       => $this->userFolder,
			);
		}

		return $env;
	}

	/**
	 * Returns the environment for a token
	 *
	 * @param $linkItem
	 *
	 * @return array
	 */
	private function getTokenBasedEnv($linkItem) {
		$origShareOwner = $this->origShareData['origShareOwner'];
		$origShareRelPath = $this->origShareData['origShareRelPath'];
		// Displayed in the top right corner of the gallery
		$origOwnerDisplayName = $this->origShareData['origOwnerDisplayName'];

		$shareOwner = $linkItem['uid_owner'];
		$folder = $this->serverContainer->getUserFolder($shareOwner);

		$albumName = trim($linkItem['file_target'], '//');

		return array(
			'owner'                    => $shareOwner,
			'relativePath'             => $origShareRelPath . '/',
			'folder'                   => $folder,
			'albumName'                => $albumName,
			'originalShareOwner'       => $origShareOwner,
			'originalOwnerDisplayName' => $origOwnerDisplayName,
		);
	}

	/**
	 * Makes sure that the token exists
	 *
	 * @param bool|array $linkItem
	 */
	private function checkLinkItemExists($linkItem) {
		if ($linkItem === false
			|| ($linkItem['item_type'] !== 'file'
				&& $linkItem['item_type'] !== 'folder')
		) {
			$message = 'Passed token parameter is not valid';
			$this->kaBoom($message, Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * Makes sure that the token contains all the information that we need
	 *
	 * @param array $linkItem
	 * @param string $token
	 */
	private function checkLinkItemIsValid($linkItem, $token) {
		if (!isset($linkItem['uid_owner'])
			|| !isset($linkItem['file_source'])
		) {
			$message =
				'Passed token seems to be valid, but it does not contain all necessary information . ("'
				. $token . '")';
			$this->kaBoom($message, Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Makes sure an item type was set for that token
	 *
	 * @param array $linkItem
	 */
	private function checkItemType($linkItem) {
		if (!isset($linkItem['item_type'])) {
			$message = 'No item type set for share id: ' . $linkItem['id'];
			$this->kaBoom($message, Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Authenticate link item with the given password
	 * or with the session if no password was given.
	 *
	 * @fixme Migrate old hashes to new hash format
	 * Due to the fact that there is no reasonable functionality to update the password
	 * of an existing share no migration is yet performed there.
	 * The only possibility is to update the existing share which will result in a new
	 * share ID and is a major hack.
	 *
	 * In the future the migration should be performed once there is a proper method
	 * to update the share's password. (for example `$share->updatePassword($password)`
	 * @link https://github.com/owncloud/core/issues/10671
	 *
	 * @param string $password
	 *
	 * @return bool true if authorized, an exception is raised otherwise
	 *
	 * @throws ServiceException
	 */
	private function authenticate($password = null) {
		$linkItem = $this->linkItem;

		if ($linkItem['share_type'] == Share::SHARE_TYPE_LINK) {
			$this->checkPassword($password);
		} else {
			$this->kaBoom(
				'Unknown share type ' . $linkItem['share_type'] . ' for share id '
				. $linkItem['id'], Http::STATUS_NOT_FOUND
			);
		}

		return true;
	}

	/**
	 * Validates the given password
	 *
	 * @param string $password
	 *
	 * @throws ServiceException
	 */
	private function checkPassword($password) {
		$linkItem = $this->linkItem;
		$newHash = '';
		if ($this->hasher->verify($password, $linkItem['share_with'], $newHash)) {

			// Save item id in session for future requests
			$this->session->set('public_link_authenticated', $linkItem['id']);
			if (!empty($newHash)) {
				// For future use
			}
		} else {
			$this->kaBoom("Wrong password", Http::STATUS_UNAUTHORIZED);
		}
	}

	/**
	 * Makes sure the user is already properly authenticated when a password is required and none
	 * was provided
	 *
	 * @throws ServiceException
	 */
	private function checkSession() {
		// not authenticated ?
		if (!$this->session->exists('public_link_authenticated')
			|| $this->session->get('public_link_authenticated') !== $this->linkItem['id']
		) {
			$this->kaBoom("Missing password", Http::STATUS_UNAUTHORIZED);
		}
	}

	/**
	 * Returns an instance of the user
	 *
	 * @param string $origShareOwner the user the share belongs to
	 *
	 * @return IUser an instance of the user
	 */
	private function getUser($origShareOwner) {
		$user = null;

		if (isset($origShareOwner)) {
			$user = $this->userManager->get($origShareOwner);
		}
		if ($user === null) {
			$this->kaBoom('Could not find user', Http::STATUS_NOT_FOUND);
		}

		return $user;
	}

	/**
	 * Returns the path the token gives access to
	 *
	 * getPath() on the file produces a path like:
	 * '/owner/files/my_folder/my_sub_folder'
	 *
	 * So we substract the path to the folder, giving us a relative path
	 * '/my_folder/my_sub_folder'
	 *
	 * @param string $origShareOwner
	 * @param int $fileSource
	 *
	 * @return string the path, relative to the folder
	 */
	private function getPath($origShareOwner, $fileSource) {
		$folder = $this->serverContainer->getUserFolder($origShareOwner);
		$resourcesArray = $folder->getById($fileSource);
		$resource = $resourcesArray[0];
		if ($resource === null) {
			$this->kaBoom('Could not resolve linkItem', Http::STATUS_NOT_FOUND);
		}
		$origSharePath = $resource->getPath();
		$folderPath = $folder->getPath();
		$origShareRelPath = str_replace($folderPath, '', $origSharePath);

		/*$this->logger->debug(
			'Full Path {origSharePath}, relative path {origShareRelPath}',
			array(
				'origSharePath'    => $origSharePath,
				'origShareRelPath' => $origShareRelPath
			)
		);*/

		return $origShareRelPath;
	}

}