<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Authors of \OCA\Files_Sharing\Helper
 *
 * @copyright Olivier Paroz 2016
 * @copyright Authors of \OCA\Files_Sharing\Helper 2014-2016
 */

namespace OCA\Gallery\Environment;

use OCP\IUserManager;
use OCP\Share;
use OCP\Share\IShare;
use OCP\ILogger;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;
use OCP\Files\Node;
use OCP\Files\File;
use OCP\Files\NotFoundException;

/**
 * Builds the environment so that the services have access to the files and folders' owner
 *
 * @package OCA\Gallery\Environment
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
	 * @var File|Folder
	 */
	private $sharedNode;
	/**
	 * @var IRootFolder
	 */
	private $rootFolder;
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
	 * @var string|null
	 */
	private $sharePassword;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param string|null $userId
	 * @param Folder|null $userFolder
	 * @param IUserManager $userManager
	 * @param IRootFolder $rootFolder
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		$userId,
		$userFolder,
		IUserManager $userManager,
		IRootFolder $rootFolder,
		ILogger $logger
	) {
		$this->appName = $appName;
		$this->userId = $userId;
		$this->userFolder = $userFolder;
		$this->userManager = $userManager;
		$this->rootFolder = $rootFolder;
		$this->logger = $logger;
	}

	/**
	 * Creates the environment based on the share the token links to
	 *
	 * @param IShare $share
	 */
	public function setTokenBasedEnv($share) {
		$origShareOwnerId = $share->getShareOwner();
		$this->userFolder = $this->rootFolder->getUserFolder($origShareOwnerId);

		$this->sharedNodeId = $share->getNodeId();
		$this->sharedNode = $share->getNode();
		$this->fromRootToFolder = $this->buildFromRootToFolder($this->sharedNodeId);

		$this->folderName = $share->getTarget();
		$this->userId = $origShareOwnerId;
		$this->sharePassword = $share->getPassword();
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
	 * Returns true if the environment has been setup using a token
	 *
	 * @return bool
	 */
	public function isTokenBasedEnv() {
		return !empty($this->sharedNodeId);
	}

	/**
	 * Returns the Node based on a path starting from the virtual root
	 *
	 * @param string $subPath
	 *
	 * @return File|Folder
	 */
	public function getNodeFromVirtualRoot($subPath) {
		$relativePath = $this->getRelativePath($this->fromRootToFolder);
		$path = $relativePath . '/' . $subPath;
		$node = $this->getNodeFromUserFolder($path);

		return $this->getResourceFromId($node->getId());
	}

	/**
	 * Returns the Node based on a path starting from the files' owner user folder
	 *
	 * When logged in, this is the current user's user folder
	 * When visiting a link, this is the sharer's user folder
	 *
	 * @param string $path
	 *
	 * @return File|Folder
	 *
	 * @throws NotFoundEnvException
	 */
	public function getNodeFromUserFolder($path) {
		$folder = $this->userFolder;
		if ($folder === null) {
			throw new NotFoundEnvException("Could not access the user's folder");
		} else {
			try {
				$node = $folder->get($path);
			} catch (NotFoundException $exception) {
				$message = 'Could not find anything at: ' . $exception->getMessage();
				throw new NotFoundEnvException($message);
			}
		}

		return $node;
	}

	/**
	 * Returns the resource identified by the given ID
	 *
	 * @param int $resourceId
	 *
	 * @return Node
	 *
	 * @throws NotFoundEnvException
	 */
	public function getResourceFromId($resourceId) {
		if ($this->isTokenBasedEnv()) {
			if ($this->sharedNode->getType() === 'dir') {
				$resource = $this->getResourceFromFolderAndId($this->sharedNode, $resourceId);
			} else {
				$resource = $this->sharedNode;
			}
		} else {
			$resource = $this->getResourceFromFolderAndId($this->userFolder, $resourceId);
		}

		return $resource;
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
	 * Returns the virtual root where the user lands after logging in or when following a link
	 *
	 * @return Folder
	 * @throws NotFoundEnvException
	 */
	public function getVirtualRootFolder() {
		$rootFolder = $this->userFolder;
		if ($this->isTokenBasedEnv()) {
			$node = $this->getSharedNode();
			$nodeType = $node->getType();
			if ($nodeType === 'dir') {
				$rootFolder = $node;
			} else {
				throw new NotFoundEnvException($node->getPath() . ' is not a folder');
			}
		}

		return $rootFolder;
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
	 * @throws NotFoundEnvException
	 */
	public function getDisplayName() {
		$user = null;
		$userId = $this->userId;

		if (isset($userId)) {
			$user = $this->userManager->get($userId);
		}
		if ($user === null) {
			throw new NotFoundEnvException('Could not find user');
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
	 * Returns the password for the share, if there is one
	 *
	 * @return string|null
	 */
	public function getSharePassword() {
		return $this->sharePassword;
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
	 * @param File|Folder|Node $node
	 *
	 * @return string
	 */
	public function getPathFromVirtualRoot($node) {
		$path = $node->getPath();
		$nodeType = $node->getType();

		// Needed because fromRootToFolder always ends with a slash
		if ($nodeType === 'dir') {
			$path .= '/';
		}

		$path = str_replace($this->fromRootToFolder, '', $path);
		$path = rtrim($path, '/');

		return $path;
	}

	/**
	 * Returns the resource found in a specific folder and identified by the given ID
	 *
	 * @param Folder $folder
	 * @param int $resourceId
	 *
	 * @return Node
	 * @throws NotFoundEnvException
	 */
	private function getResourceFromFolderAndId($folder, $resourceId) {
		$resourcesArray = $folder->getById($resourceId);

		if ($resourcesArray[0] === null) {
			throw new NotFoundEnvException('Could not locate node linked to ID: ' . $resourceId);
		}

		return $resourcesArray[0];
	}

	/**
	 * Returns the path from the shared folder to the root folder in the original
	 * owner's filesystem: /userId/files/parent_folder/shared_folder
	 *
	 * This cannot be calculated with paths and IDs, the share's file source is required
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

}
