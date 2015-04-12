<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\Service;

use OCP\Files\Folder;
use OCP\Files\Node;

use OCA\GalleryPlus\Environment\NotFoundEnvException;

/**
 * Contains various methods to retrieve information from the filesystem
 *
 * @package OCA\GalleryPlus\Service
 */
class FilesService extends Service {

	/**
	 * This returns the current folder node based on a path
	 *
	 * If the path leads to a file, we'll return the node of the containing folder
	 *
	 * If we can't find anything, we try with the parent folder, up to the root or until we reach
	 * our recursive limit
	 *
	 * @param string $location
	 * @param int $depth
	 *
	 * @return array <Folder,string,bool>
	 */
	public function getCurrentFolder($location, $depth = 0) {
		$node = null;
		$location = $this->validateLocation($location, $depth);
		try {
			$node = $this->environment->getResourceFromPath($location);
			if ($node->getType() === 'file') {
				$node = $node->getParent();
			}
		} catch (NotFoundEnvException $exception) {
			// There might be a typo in the file or folder name
			$folder = pathinfo($location, PATHINFO_DIRNAME);
			$depth++;

			return $this->getCurrentFolder($folder, $depth);
		}
		$path = $this->environment->getPathFromVirtualRoot($node);
		$locationHasChanged = $this->hasLocationChanged($depth);

		return [$path, $node, $locationHasChanged];
	}

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
	 *
	 * @throws NotFoundServiceException
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
	 * Determines if the files are hosted locally (shared or not)
	 *
	 * isMounted() includes externally hosted shares, so we need to exclude those
	 *
	 * @param Node $node
	 *
	 * @return bool
	 */
	protected function isLocalAndAvailable($node) {
		if (!$node->isMounted() && $node->isReadable()) {
			if ($this->isExternalShare($node)) {
				return false;
			}

			return true;
		}

		return false;
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
	 * Returns the node if it's a folder we have access to
	 *
	 * @param Folder $node
	 * @param string $nodeType
	 *
	 * @return array|Folder
	 */
	protected function getAllowedSubFolder($node, $nodeType) {
		if ($nodeType === 'dir') {
			/** @type Folder $node */
			if (!$node->nodeExists('.nomedia')) {
				return [$node];
			}
		}

		return [];
	}

	/**
	 * Makes sure we don't go too far up before giving up
	 *
	 * @param string $location
	 * @param int $depth
	 *
	 * @return string
	 */
	private function validateLocation($location, $depth) {
		if ($depth === 4) {
			// We can't find anything, so we decide to return data for the root folder
			$location = '';
		}

		return $location;
	}

	/**
	 * @param $depth
	 *
	 * @return bool
	 */
	private function hasLocationChanged($depth) {
		$locationHasChanged = false;
		if ($depth > 0) {
			$locationHasChanged = true;
		}

		return $locationHasChanged;
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
			$this->logAndThrowNotFound($exception->getMessage());
		}

		return [];
	}

	/**
	 * Determines if the node is a share which is hosted externally
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

		if ($sid[0] === 'shared' && $sid[2][0] !== '/') {
			return true;
		}

		return false;
	}

}
