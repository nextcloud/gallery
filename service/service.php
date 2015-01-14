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
use OCP\Files\NotFoundException;

use OCP\AppFramework\Http;

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
	 * @type SmarterLogger
	 */
	protected $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param SmarterLogger $logger
	 */
	public function __construct($appName, SmarterLogger $logger) {
		$this->appName = $appName;
		$this->logger = $logger;
	}

	/**
	 * Returns the resource identified by the given ID
	 *
	 * @param Folder $folder
	 * @param int $resourceId
	 *
	 * @return Node
	 * @throws ServiceException
	 */
	protected function getResourceFromId($folder, $resourceId) {
		$resourcesArray = $folder->getById($resourceId);
		if ($resourcesArray[0] === null) {
			$this->kaBoom('Could not resolve linkItem', Http::STATUS_NOT_FOUND);
		}

		return $resourcesArray[0];
	}

	/**
	 * Returns the resource located at the given path
	 *
	 * The path starts from the user's files folder
	 * The resource is either a File or a Folder
	 *
	 * @param Folder $folder
	 * @param string $path
	 *
	 * @return Node
	 */
	protected function getResourceFromPath($folder, $path) {
		$nodeInfo = $this->getNodeInfo($folder, $path);

		return $this->getResourceFromId($folder, $nodeInfo['fileid']);
	}

	/**
	 * Returns the Node based on the current user's files folder and a given
	 * path
	 *
	 * @param Folder $folder
	 * @param string $path
	 *
	 * @return int[]|false
	 */
	protected function getNodeInfo($folder, $path) {
		$nodeInfo = false;
		try {
			$node = $folder->get($path);
			$nodeInfo = array(
				'fileid'      => $node->getId(),
				'permissions' => $node->getPermissions()
			);
		} catch (NotFoundException $exception) {
			$message = $exception->getMessage();
			$code = Http::STATUS_NOT_FOUND;
			$this->kaBoom($message, $code);
		}

		return $nodeInfo;
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
		$this->logger->error($message . ' (' . $code . ')');

		throw new ServiceException(
			$message,
			$code
		);
	}
}