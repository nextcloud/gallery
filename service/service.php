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

use OCP\ILogger;
use OCP\Files\Folder;
use OCP\Files\Node;
use OCP\Files\NotFoundException;

use OCP\AppFramework\Http;

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
	 * @type ILogger
	 */
	protected $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param ILogger $logger
	 */
	public function __construct($appName, ILogger $logger) {
		$this->appName = $appName;
		$this->logger = $logger;
	}

	/**
	 * Returns the Node based on the current user's folder and a given path
	 *
	 * @param Folder $folder
	 * @param string $path
	 *
	 * @return Node
	 */
	protected function getResource($folder, $path) {
		$resource = false;
		try {
			$node = $folder->get($path);
			$resourceId = $node->getId();
			$resourcesArray = $folder->getById($resourceId);

			$resource = $resourcesArray[0];
		} catch (NotFoundException $exception) {
			$message = $exception->getMessage();
			$code = Http::STATUS_NOT_FOUND;
			$this->kaBoom($message, $code);
		}

		return $resource;
	}

	/**
	 * Logs the error and raises an exception
	 *
	 * @param string $message
	 * @param int $code
	 *
	 * @throws ServiceException
	 */
	protected function kaBoom($message, $code) {
		$appName = $this->appName;
		$this->logger->error(
			$message . ' (' . $code . ')',
			array(
				'app' => $appName
			)
		);
		throw new ServiceException(
			$message,
			$code
		);
	}
}