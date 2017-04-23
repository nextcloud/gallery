<?php
/**
 * Nextcloud - Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Service;

use OCP\Files\Folder;

use OCA\Gallery\Environment\NotFoundEnvException;

/**
 * Looks for the folder to use, based on the request made by the client
 *
 * This is to make sure we were not:
 *    * given a file
 *    * given a folder name with a typo
 *
 * @package OCA\Gallery\Service
 */
class SearchFolderService extends FilesService {
	/**
	 * @var int
	 */
	protected $virtualRootLevel = null;

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
	private function findFolder($location, $depth = 0) {
		$node = null;
		$location = $this->validateLocation($location, $depth);
		try {
			$node = $this->environment->getNodeFromVirtualRoot($location);
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

		return $this->sendFolder($path, $node);
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
	 * Makes sure that the folder is not empty, does meet our requirements in terms of location and
	 * returns details about it
	 *
	 * @param string $path
	 * @param Folder $node
	 *
	 * @return array <string,Folder,bool>
	 * @throws ForbiddenServiceException|NotFoundServiceException
	 */
	private function sendFolder($path, $node) {
		if (is_null($node)) {
			// Something very wrong has just happened
			throw new NotFoundServiceException('Oh Nooooes!');
		} elseif (!$this->isAllowedAndAvailable($node)) {
			throw new ForbiddenServiceException(
				'The owner has placed a restriction or the storage location is unavailable'
			);
		}

		return [$path, $node];
	}

}
