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
 * @copyright Olivier Paroz 2015
 * @copyright Authors of \OCA\Files_Sharing\Helper 2014-2015
 */

namespace OCA\GalleryPlus\Environment;

use OCP\IServerContainer;
use OCP\IUserManager;
use OCP\Share;
use OCP\Files\Folder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;

use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Builds the environment so that the services have access to the files and folders' owner
 *
 * @todo remove the serverContainer once OCP\IUserManager has a getUserFolder() method
 *
 * @package OCA\GalleryPlus\Environment
 */
class Environment {

	/**
	 * @type string
	 */
	private $appName;
	/**
	 * The userId of the logged-in user or the person sharing a folder publicly
	 *
	 * @type string
	 */
	private $userId;
	/**
	 * The userFolder of the logged-in user or the ORIGINAL owner of the files which are shared
	 * publicly
	 *
	 * A share needs to be tracked back to its original owner in order to be able to access the
	 * resource
	 *
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
	 * @type SmarterLogger
	 */
	private $logger;
	/**
	 * The path to the userFolder for users with accounts: /userId/files
	 *
	 * For public folders, it's the path from the shared folder to the root folder in the original
	 * owner's filesystem: /userId/files/parent_folder/shared_folder
	 *
	 * @type string
	 */
	private $fromRootToFolder;
	/**
	 * The name of the shared folder
	 *
	 * @type string
	 */
	private $folderName;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param string|null $userId
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
		$this->appName = $appName;
		$this->userId = $userId;
		$this->userFolder = $userFolder;
		$this->userManager = $userManager;
		$this->serverContainer = $serverContainer;
		$this->logger = $logger;
	}

	/**
	 * Creates the environment based on the linkItem the token links to
	 *
	 * @param array $linkItem
	 */
	public function setTokenBasedEnv($linkItem) {
		// Resolves reshares down to the last real share
		$rootLinkItem = Share::resolveReShare($linkItem);
		$origShareOwner = $rootLinkItem['uid_owner'];
		$this->userFolder = $this->setupFilesystem($origShareOwner);

		$fileSource = $linkItem['file_source'];
		$this->fromRootToFolder = $this->buildFromRootToFolder($fileSource);

		$this->folderName = $linkItem['file_target'];
		$this->userId = $linkItem['uid_owner'];
	}

	/**
	 * Creates the environment for a logged-in user
	 *
	 * userId and userFolder are already known, we define fromRootToFolder
	 * so that the services can use one method to have access resources
	 * without having to know whether they're private or public
	 */
	public function setStandardEnv() {
		$this->fromRootToFolder = $this->userFolder->getPath() . '/';
	}

	/**
	 * Returns the resource located at the given path
	 *
	 * The path starts from the user's files folder
	 * The resource is either a File or a Folder
	 *
	 * @param string $subPath
	 *
	 * @return Node
	 */
	public function getResourceFromPath($subPath) {
		$path = $this->getImagePathFromFolder($subPath);
		$nodeInfo = $this->getNodeInfo($path);

		return $this->getResourceFromId($nodeInfo['fileid']);
	}

	/**
	 * Returns the Node based on the current user's files folder and a given
	 * path
	 *
	 * @param string $path
	 *
	 * @return array<string,int>|false
	 *
	 * @throws EnvironmentException
	 */
	public function getNodeInfo($path) {
		$nodeInfo = false;
		$folder = $this->userFolder;
		if ($folder === null) {
			$this->logAndThrowNotFound("Could not access the user's folder");
		} else {
			try {
				$node = $folder->get($path);
				$nodeInfo = [
					'fileid'      => $node->getId(),
					'permissions' => $node->getPermissions()
				];
			} catch (NotFoundException $exception) {
				$message = 'Could not find anything at: ' . $exception->getMessage();
				$this->logAndThrowNotFound($message);
			}
		}

		return $nodeInfo;
	}


	/**
	 * Returns the userId of the currently logged-in user or the sharer
	 *
	 * @return string
	 */
	public function getUserId() {
		return $this->userId;
	}

	/**
	 * Returns the name of the user sharing files publicly
	 *
	 * @return string
	 */
	public function getDisplayName() {
		$user = null;
		$userId = $this->userId;

		if (isset($userId)) {
			$user = $this->userManager->get($userId);
		}
		if ($user === null) {
			$this->logAndThrowNotFound('Could not find user');
		}

		return $user->getDisplayName();
	}

	/**
	 * Returns the name of shared folder
	 *
	 * @return string
	 */
	public function getSharedFolderName() {
		return trim($this->folderName, '//');
	}

	/**
	 * Returns /parent_folder/current_folder/_my_file
	 *
	 * getPath() on the file produces a path like:
	 * '/userId/files/my_folder/my_sub_folder'
	 *
	 * So we substract the path to the folder, giving us a relative path
	 * '/my_folder/my_sub_folder'
	 *
	 * @param string $image
	 *
	 * @return string
	 */
	public function getImagePathFromFolder($image) {
		$origSharePath = $this->fromRootToFolder;
		$folderPath = $this->userFolder->getPath();
		$origShareRelPath = str_replace($folderPath, '', $origSharePath);
		$relativePath = $origShareRelPath;

		/*$this->logger->debug(
			'Full Path {origSharePath}, folder path {folderPath}, relative path {relativePath}',
			[
				'origSharePath' => $origSharePath,
				'folderPath'    => $folderPath,
				'relativePath'  => $relativePath
			]
		);*/

		return $relativePath . '/' . $image;
	}

	/**
	 * Returns fromRootToFolder
	 *
	 * @see buildFromRootToFolder
	 *
	 * @return string
	 */
	public function getFromRootToFolder() {
		return $this->fromRootToFolder;
	}

	/**
	 * Sets up the filesystem for the original share owner so that we can
	 * retrieve the files and returns the userFolder for that user
	 *
	 * We can't use 'UserFolder' from Application as the user is not known
	 * at instantiation time
	 *
	 * @param $origShareOwner
	 *
	 * @return Folder
	 */
	private function setupFilesystem($origShareOwner) {
		\OC_Util::tearDownFS(); // FIXME: Private API
		\OC_Util::setupFS($origShareOwner); // FIXME: Private API

		$folder = $this->serverContainer->getUserFolder($origShareOwner);
		/*// Alternative which does not exist yet
		$user = $this->userManager->get($origShareOwner);
		$folder = $user->getUserFolder();*/

		return $folder;
	}

	/**
	 * Returns the path from the shared folder to the root folder in the original
	 * owner's filesystem: /userId/files/parent_folder/shared_folder
	 *
	 * @param string $fileSource
	 *
	 * @return string
	 */
	private function buildFromRootToFolder($fileSource) {
		$resource = $this->getResourceFromId($fileSource);
		$fromRootToFolder = $resource->getPath() . '/';

		return $fromRootToFolder;
	}

	/**
	 * Returns the resource identified by the given ID
	 *
	 * @param int $resourceId
	 *
	 * @return Node
	 *
	 * @throws EnvironmentException
	 */
	private function getResourceFromId($resourceId) {
		$resourcesArray = $this->userFolder->getById($resourceId);
		if ($resourcesArray[0] === null) {
			$this->logAndThrowNotFound('Could not resolve linkItem');
		}

		return $resourcesArray[0];
	}

	/**
	 * Logs the error and raises an exception
	 *
	 * @param string $message
	 *
	 * @throws NotFoundEnvException
	 */
	private function logAndThrowNotFound($message) {
		$this->logger->error($message . ' (404)');
		throw new NotFoundEnvException($message);
	}

}