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

use OCA\GalleryPlus\Environment\Environment;
use OCA\GalleryPlus\Environment\NotFoundEnvException;
use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Contains methods which all services will need
 *
 * @package OCA\GalleryPlus\Service
 */
abstract class Service {

	/**
	 * @type string
	 */
	protected $appName;
	/**
	 * @type Environment
	 */
	protected $environment;
	/**
	 * @type SmarterLogger
	 */
	protected $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param Environment $environment
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		$appName,
		Environment $environment,
		SmarterLogger $logger
	) {
		$this->appName = $appName;
		$this->environment = $environment;
		$this->logger = $logger;
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
	 * Logs the error and raises a "Not found" type exception
	 *
	 * @param string $message
	 *
	 * @throws NotFoundServiceException
	 */
	protected function logAndThrowNotFound($message) {
		$this->logger->error($message . ' (404)');

		throw new NotFoundServiceException($message);
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

}
