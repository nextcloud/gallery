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
	 * @var int
	 */
	protected $virtualRootLevel = null;

	/**
	 * @var string[]
	 */
	protected $features;

	/**
	 * This returns what we think is the current folder node based on a given path
	 *
	 * @param string $location
	 * @param string[] $features
	 *
	 * @return array <string,Folder,bool>
	 */
	public function getCurrentFolder($location, $features) {
		$this->features = $features;

		return $this->findFolder($location);
	}

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
	 * @return array <string,Folder,bool>
	 */
	public function findFolder($location, $depth = 0) {
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

			return $this->findFolder($folder, $depth);
		}
		$path = $this->environment->getPathFromVirtualRoot($node);
		$locationHasChanged = $this->hasLocationChanged($depth);

		return $this->sendFolder($path, $node, $locationHasChanged);
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
	 * Determines if the files are hosted locally (shared or not) and can be used by the preview
	 * system
	 *
	 * isMounted() includes externally hosted shares (s2s) and locally mounted shares, so we need
	 * to exclude those
	 *
	 * @param Node $node
	 *
	 * @return bool
	 */
	protected function isLocalAndAvailable($node) {
		if (!$node->isMounted()) {

			return $this->isLocal($node) && $this->isAvailable($node);
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
			/** @var Folder $node */
			if (!$node->nodeExists('.nomedia')) {
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
		$rootFolder = $this->environment->getNode('');
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
	 * Makes sure that the folder is not empty, does meet our requirements in terms of location and
	 * returns details about it
	 *
	 * @param string $path
	 * @param Folder $node
	 * @param bool $locationHasChanged
	 *
	 * @return array <string,Folder,bool>
	 *
	 * @throws NotFoundServiceException
	 */
	private function sendFolder($path, $node, $locationHasChanged) {
		if (is_null($node)) {
			// Something very wrong has just happened
			$this->logAndThrowNotFound('Oh Nooooes!');
		}
		if (!$this->isLocalAndAvailable($node)) {
			$this->logAndThrowForbidden('Album is private or unavailable');
		}

		return [$path, $node, $locationHasChanged];
	}

	/**
	 * Throws an exception if this problem occurs in the current folder, otherwise just ignores the
	 * sub-folder
	 *
	 * @param int $subDepth
	 * @param \Exception $exception
	 *
	 * @return array
	 *
	 * @throws NotFoundServiceException
	 */
	private function recoverFromGetNodesError($subDepth, $exception) {
		if ($subDepth === 0) {
			$this->logAndThrowNotFound($exception->getMessage());
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
	private function isLocal($node) {
		$mount = $node->getMountPoint();

		return !$this->isExternalShare($node) && $mount && $mount->getOption('previews', true);
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
	 * Determines if the node is a share which is hosted externally
	 *
	 *
	 * @param Node $node
	 *
	 * @return bool
	 */
	private function isExternalShare($node) {
		if ($this->isExternalShareAllowed()) {
			return false;
		}

		$sid = explode(
			':',
			$node->getStorage()
				 ->getId()
		);

		return ($sid[0] === 'shared' && $sid[2][0] !== '/');
	}

	/**
	 * Determines if the user has allowed the use of external shares
	 *
	 * @fixme Blocked by https://github.com/owncloud/core/issues/15551
	 *
	 * @return bool
	 */
	private function isExternalShareAllowed() {
		if (empty($this->features)) {
			return false;
		}

		return in_array('external_shares', $this->features);
	}

}
