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
use OCP\ILogger;
use OCP\Files\Folder;
use OCP\Files\Node;
use OCP\Files\File;
use OCP\Files\NotFoundException;

/**
 * Builds the environment so that the services have access to the files and folders' owner
 *
 * @todo remove the serverContainer once OCP\IUserManager has a getUserFolder() method
 *
 * @package OCA\GalleryPlus\Environment
 */
class Environment {

	/**
	 * @var string
	 */
	private $appName;
	/**
	 * The userId of the logged-in user or the person sharing a folder publicly
	 *
	 * @var string
	 */
	private $userId;
	/**
	 * The userFolder of the logged-in user or the ORIGINAL owner of the files which are shared
	 * publicly
	 *
	 * A share needs to be tracked back to its original owner in order to be able to access the
	 * resource
	 *
	 * @var Folder|null
	 */
	private $userFolder;
	/**
	 * @var IUserManager
	 */
	private $userManager;
	/**
	 * @var int
	 */
	private $sharedNodeId;
	/**
	 * @var IServerContainer
	 */
	private $serverContainer;
	/**
	 * @var ILogger
	 */
	private $logger;
	/**
	 * The path to the userFolder for users with accounts: /userId/files
	 *
	 * For public folders, it's the path from the shared folder to the root folder in the original
	 * owner's filesystem: /userId/files/parent_folder/shared_folder
	 *
	 * @var string
	 */
	private $fromRootToFolder;
	/**
	 * The name of the shared folder
	 *
	 * @var string
	 */
	private $folderName;
	/**
	 * @var string
	 */
	private $shareWith;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param string|null $userId
	 * @param Folder|null $userFolder
	 * @param IUserManager $userManager
	 * @param IServerContainer $serverContainer
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		$userId,
		$userFolder,
		IUserManager $userManager,
		IServerContainer $serverContainer,
		ILogger $logger
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

		// This is actually the node ID
		$this->sharedNodeId = $linkItem['file_source'];
		$this->fromRootToFolder = $this->buildFromRootToFolder($this->sharedNodeId);

		$this->folderName = $linkItem['file_target'];
		$this->userId = $rootLinkItem['uid_owner'];
		$this->shareWith = $linkItem['share_with'];
	}

	/**
	 * Creates the environment for a logged-in user
	 *
	 * userId and userFolder are already known, we define fromRootToFolder
	 * so that the services can use one method to have access to resources
	 * without having to know whether they're private or public
	 */
	public function setStandardEnv() {
		$this->fromRootToFolder = $this->userFolder->getPath() . '/';
	}

	/**
	 * Returns the resource located at the given path
	 *
	 * The path starts from the user's files folder because we'll query that folder to get the
	 * information we need. The resource is either a File or a Folder
	 *
	 * @param string $subPath
	 *
	 * @return File|Folder
	 */
	public function getResourceFromPath($subPath) {
		$relativePath = $this->getRelativePath($this->fromRootToFolder);
		$path = $relativePath . '/' . $subPath;
		$node = $this->getNode($path);

		return $this->getResourceFromId($node->getId());
	}

	/**
	 * Returns the Node based on the current user's files folder and a given
	 * path
	 *
	 * @param string $path
	 *
	 * @return File|Folder
	 *
	 * @throws EnvironmentException
	 */
	public function getNode($path) {
		$node = false;
		$folder = $this->userFolder;
		if ($folder === null) {
			$this->logAndThrowNotFound("Could not access the user's folder");
		} else {
			try {
				$node = $folder->get($path);
			} catch (NotFoundException $exception) {
				$message = 'Could not find anything at: ' . $exception->getMessage();
				$this->logAndThrowNotFound($message);
			}
		}

		return $node;
	}

	/**
	 * Returns the shared node
	 *
	 * @return File|Folder
	 */
	public function getSharedNode() {
		return $this->getResourceFromId($this->sharedNodeId);
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
	public function getResourceFromId($resourceId) {
		$resourcesArray = $this->userFolder->getById($resourceId);
		if ($resourcesArray[0] === null) {
			$this->logAndThrowNotFound('Could not locate file linked to ID: ' . $resourceId);
		}

		return $resourcesArray[0];
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
	 * Returns if the share is protected (share_with === true)
	 *
	 * @return string
	 */
	public function isShareProtected() {
		return $this->shareWith;
	}

	/**
	 * Returns the path which goes from the file, up to the user folder, based on a node:
	 * parent_folder/current_folder/my_file
	 *
	 * This is used for the preview system, which needs a full path
	 *
	 * getPath() on the file produces a path like:
	 * '/userId/files/my_folder/my_sub_folder/my_file'
	 *
	 * So we substract the path to the folder, giving us a relative path
	 * 'my_folder/my_sub_folder/my_file'
	 *
	 * @param Node $file
	 *
	 * @return string
	 */
	public function getPathFromUserFolder($file) {
		$path = $file->getPath();

		return $this->getRelativePath($path);
	}

	/**
	 * Returns the path which goes from the file, up to the root folder of the Gallery:
	 * current_folder/my_file
	 *
	 * That root folder changes when folders are shared publicly
	 *
	 * @param File|Folder $node
	 *
	 * @return string
	 */
	public function getPathFromVirtualRoot($node) {
		$path = $node->getPath();

		if ($node->getType() === 'dir') {
			// Needed because fromRootToFolder always ends with a slash
			$path .= '/';
		}

		$path = str_replace($this->fromRootToFolder, '', $path);
		$path = rtrim($path, '/');

		return $path;
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
	 * This cannot be calculated with paths and IDs, the linkitem's file source is required
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
	 * Returns the path which goes from the file, up to the user folder, based on a path:
	 * parent_folder/current_folder/my_file
	 *
	 * getPath() on the file produces a path like:
	 * '/userId/files/my_folder/my_sub_folder/my_file'
	 *
	 * So we substract the path to the user folder, giving us a relative path
	 * 'my_folder/my_sub_folder'
	 *
	 * @param string $fullPath
	 *
	 * @return string
	 */
	private function getRelativePath($fullPath) {
		$folderPath = $this->userFolder->getPath() . '/';
		$origShareRelPath = str_replace($folderPath, '', $fullPath);

		return $origShareRelPath;
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
