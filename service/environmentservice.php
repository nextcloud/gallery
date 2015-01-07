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
use OCP\Share;
use OCP\IUserManager;

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
	 * @type array
	 */
	private $linkItem;
	/**
	 * @type array
	 */
	private $origShareData = array();

	/**
	 * @param string $appName
	 * @param $userId
	 * @param Folder|null $userFolder
	 * @param IUserManager $userManager
	 * @param IServerContainer $serverContainer
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		$appName,
		$userId,
		$userFolder,
		IUserManager $userManager,
		IServerContainer $serverContainer,
		SmarterLogger $logger
	) {
		parent::__construct($appName, $logger);

		$this->userId = $userId;
		$this->userFolder = $userFolder;
		$this->userManager = $userManager;
		$this->serverContainer = $serverContainer;
	}

	/**
	 * Validates a token to make sure its linked to a valid resource
	 *
	 * Logic mostly duplicated from @see \OCA\Files_Sharing\Helper
	 *
	 * @param string $token
	 */
	public function checkToken($token) {
		// The user wants to access a resource anonymously since he's opened a public link
		\OC_User::setIncognitoMode(true); // FIXME: Private API

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
	 * @param string $password
	 */
	public function checkAuthorisation($password) {
		$linkItem = $this->linkItem;
		$passwordRequired = isset($linkItem['share_with']);

		if ($passwordRequired) {
			$authenticated = \OCA\Files_Sharing\Helper::authenticate(
				$linkItem, $password
			); // FIXME: Private API, but Hasher is not available in OC7

			if (!$authenticated) {
				$this->kaBoom("Missing password", Http::STATUS_UNAUTHORIZED);
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

		$rootLinkItem = Share::resolveReShare(
			$linkItem
		); // Resolves reshares down to the last real share

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
			$origShareOwner = $this->origShareData['origShareOwner'];
			$origShareRelPath = $this->origShareData['origShareRelPath'];
			// Displayed in the top right corner of the gallery
			$origOwnerDisplayName =
				$this->origShareData['origOwnerDisplayName'];

			$shareOwner = $linkItem['uid_owner'];
			$folder = $this->serverContainer->getUserFolder($shareOwner);

			$albumName = trim($linkItem['file_target'], '//');

			$env = array(
				'owner'                    => $shareOwner,
				'relativePath'             => $origShareRelPath . '/',
				'folder'                   => $folder,
				'albumName'                => $albumName,
				'originalShareOwner'       => $origShareOwner,
				'originalOwnerDisplayName' => $origOwnerDisplayName,
			);

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
			$message =
				'No item type set for share id: ' . $linkItem['id'];
			$this->kaBoom($message, Http::STATUS_NOT_FOUND);
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
		// This produces a path like /owner/files/my_folder/my_sub_folder
		$origSharePath = $resource->getPath();

		// This creates /my_folder/my_sub_folder
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