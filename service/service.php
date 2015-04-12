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

use OCA\GalleryPlus\Environment\Environment;
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
	 * Logs the error and raises a "Forbidden" type exception
	 *
	 * @param string $message
	 *
	 * @throws ForbiddenServiceException
	 */
	protected function logAndThrowForbidden($message) {
		$this->logger->error($message . ' (403)');

		throw new ForbiddenServiceException($message);
	}

}
