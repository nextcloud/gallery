<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2016
 */

namespace OCA\Gallery\Service;

use OCP\Files\File;
use OCP\Files\Folder;
use OCP\Files\Node;

/**
 * Contains various methods to retrieve information from the filesystem
 *
 * @package OCA\Gallery\Service
 */
abstract class FilesService extends Service {

	/** @var int */
	protected $virtualRootLevel = null;
	/** @var string[] */
	protected $features;
	/** @var string */
	protected $ignoreAlbum = '.nomedia';

	/**
	 * Retrieves all files and sub-folders contained in a folder
	 *
	 * If we can't find anything in the current folder, we throw an exception as there is no point
	 * in doing any more work, but if we're looking at a sub-folder, we return an empty array so
	 * that it can be simply ignored
	 *
	 * @param Folder $folder
	 * @param int $subDepth
	 *
	 * @return array
	 */
	protected function getNodes($folder, $subDepth) {
		try {
			$nodes = $folder->getDirectoryListing();
		} catch (\Exception $exception) {
			$nodes = $this->recoverFromGetNodesError($subDepth, $exception);
		}

		return $nodes;
	}

	/**
	 * Determines if the files are hosted locally (shared or not) and can be used by the preview
	 * system
	 *
	 * isMounted() doesn't include externally hosted shares, so we need to exclude those from the
	 * non-mounted nodes
	 *
	 * @param Node $node
	 *
	 * @return bool
	 */
	protected function isAllowedAndAvailable($node) {
		try {
			return $node && $this->isAllowed($node) && $this->isAvailable($node);
		} catch (\Exception $exception) {
			$message = 'The folder is not available: ' . $exception->getMessage();
			$this->logger->error($message);

			return false;
		}
	}

	/**
	 * Returns the node type, either 'dir' or 'file'
	 *
	 * If there is a problem, we return an empty string so that the node can be ignored
	 *
	 * @param Node $node
	 *
	 * @return string
	 */
	protected function getNodeType($node) {
		try {
			$nodeType = $node->getType();
		} catch (\Exception $exception) {
			return '';
		}

		return $nodeType;
	}

	/**
	 * Returns various information about a node
	 *
	 * @param Node|File|Folder $node
	 *
	 * @return array<string,int|string|bool|array<string,int|string>>
	 */
	protected function getNodeData($node) {
		$imagePath = $this->environment->getPathFromVirtualRoot($node);
		$nodeId = $node->getId();
		$mTime = $node->getMTime();
		$etag = $node->getEtag();
		$size = $node->getSize();
		$sharedWithUser = $node->isShared();
		$ownerData = $this->getOwnerData($node);
		$permissions = $node->getPermissions();

		//$this->logger->debug("Image path : {var1}", ['var1' => $imagePath]);

		return $this->formatNodeData(
			$imagePath, $nodeId, $mTime, $etag, $size, $sharedWithUser, $ownerData, $permissions
		);
	}

	/**
	 * Returns various information about a folder
	 *
	 * @param Folder $node
	 *
	 * @return array<string,int|string|bool|array<string,int|string>>
	 */
	protected function getFolderData($node) {
		$folderData = $this->getNodeData($node);
		$folderData['freespace'] = $node->getFreeSpace();

		return $folderData;
	}

	/**
	 * Returns the node if it's a folder we have access to
	 *
	 * @param Folder $node
	 * @param string $nodeType
	 *
	 * @return array|Folder
	 */
	protected function getAllowedSubFolder($node, $nodeType) {
		if ($nodeType === 'dir') {
			/** @var Folder $node */
			if (!$node->nodeExists($this->ignoreAlbum)) {
				return [$node];
			}
		}

		return [];
	}

	/**
	 * Determines if we've reached the root folder
	 *
	 * @param Folder $folder
	 * @param int $level
	 *
	 * @return bool
	 */
	protected function isRootFolder($folder, $level) {
		$isRootFolder = false;
		$rootFolder = $this->environment->getVirtualRootFolder();
		if ($folder->getPath() === $rootFolder->getPath()) {
			$isRootFolder = true;
		}
		$virtualRootFolder = $this->environment->getPathFromVirtualRoot($folder);
		if (empty($virtualRootFolder)) {
			$this->virtualRootLevel = $level;
		}

		return $isRootFolder;
	}

	/**
	 * Throws an exception if this problem occurs in the current folder, otherwise just ignores the
	 * sub-folder
	 *
	 * @param int $subDepth
	 * @param \Exception $exception
	 *
	 * @return array
	 * @throws NotFoundServiceException
	 */
	private function recoverFromGetNodesError($subDepth, $exception) {
		if ($subDepth === 0) {
			throw new NotFoundServiceException($exception->getMessage());
		}

		return [];
	}

	/**
	 * Determines if we can consider the node mounted locally or if it's been authorised to be
	 * scanned
	 *
	 * @param Node $node
	 *
	 * @return bool
	 */
	private function isAllowed($node) {
		$allowed = true;
		if ($this->isExternalShare($node)) {
			$allowed = $this->isExternalShareAllowed();
		}

		if ($node->isMounted()) {
			$mount = $node->getMountPoint();
			$allowed = $mount && $mount->getOption('previews', true);
		}

		return $allowed;
	}

	/**
	 * Determines if the node is available, as in readable
	 *
	 * @todo Test to see by how much using file_exists slows things down
	 *
	 * @param Node $node
	 *
	 * @return bool
	 */
	private function isAvailable($node) {
		return $node->isReadable();
	}

	/**
	 * Determines if the user has allowed the use of external shares
	 *
	 * @return bool
	 */
	private function isExternalShareAllowed() {
		$rootFolder = $this->environment->getVirtualRootFolder();

		return ($this->isExternalShare($rootFolder)
				|| in_array('external_shares', $this->features));
	}

	/**
	 * Determines if the node is a share which is hosted externally
	 *
	 *
	 * @param Node $node
	 *
	 * @return bool
	 */
	private function isExternalShare($node) {
		$sid = explode(
			':',
			$node->getStorage()
				 ->getId()
		);

		return ($sid[0] === 'shared' && $sid[2][0] !== '/');
	}

	/**
	 * Returns what we known about the owner of a node
	 *
	 * @param Node $node
	 *
	 * @return null|array<string,int|string>
	 */
	private function getOwnerData($node) {
		$owner = $node->getOwner();
		$ownerData = [];
		if ($owner) {
			$ownerData = [
				'uid'         => $owner->getUID(),
				'displayname' => $owner->getDisplayName()
			];
		}

		return $ownerData;
	}

	/**
	 * Returns an array containing information about a node
	 *
	 * @param string $imagePath
	 * @param int $nodeId
	 * @param int $mTime
	 * @param string $etag
	 * @param int $size
	 * @param bool $sharedWithUser
	 * @param array <string,int|string> $ownerData
	 * @param int $permissions
	 *
	 * @return array
	 */
	private function formatNodeData(
		$imagePath, $nodeId, $mTime, $etag, $size, $sharedWithUser, $ownerData, $permissions
	) {
		return [
			'path'           => $imagePath,
			'nodeid'         => $nodeId,
			'mtime'          => $mTime,
			'etag'           => $etag,
			'size'           => $size,
			'sharedwithuser' => $sharedWithUser,
			'owner'          => $ownerData,
			'permissions'    => $permissions
		];
	}

}
