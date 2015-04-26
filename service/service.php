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

use OCA\GalleryPlus\Environment\Environment;

/**
 * Contains methods which all services will need
 *
 * @package OCA\GalleryPlus\Service
 */
abstract class Service {

	/**
	 * @var string
	 */
	protected $appName;
	/**
	 * @var Environment
	 */
	protected $environment;
	/**
	 * @var ILogger
	 */
	protected $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param Environment $environment
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		Environment $environment,
		ILogger $logger
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
