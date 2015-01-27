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
	private $appName;
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
	public function __construct(
		$appName,
		SmarterLogger $logger
	) {
		$this->appName = $appName;
		$this->logger = $logger;
	}

	/**
	 * Logs the error and raises an exception
	 *
	 * @param string $message
	 *
	 * @throws ServiceException
	 */
	protected function logAndThrowNotFound($message) {
		$this->logger->error($message . ' (404)');

		throw new NotFoundServiceException($message);
	}
}
