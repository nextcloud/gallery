<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lukas Reschke <lukas@owncloud.com>
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Lukas Reschke 2014-2015
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\Middleware;

use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;

use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\IControllerMethodReflector;

use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Checks whether the "sharing check" is enabled
 *
 * @package OCA\Files_Sharing\Middleware
 */
class SharingCheckMiddleware extends CheckMiddleware {

	/**
	 * @type IConfig
	 * */
	private $config;
	/**
	 * @type IControllerMethodReflector
	 */
	protected $reflector;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $appConfig
	 * @param IControllerMethodReflector $reflector
	 * @param IURLGenerator $urlGenerator
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IConfig $appConfig,
		IControllerMethodReflector $reflector,
		IURLGenerator $urlGenerator,
		SmarterLogger $logger
	) {
		parent::__construct(
			$appName,
			$request,
			$urlGenerator,
			$logger
		);

		$this->config = $appConfig;
		$this->reflector = $reflector;
	}

	/**
	 * Check if sharing is enabled before the controllers is executed
	 *
	 * Inspects the controller method annotations and if PublicPage is found
	 * it makes sure that sharing is enabled in the configuration settings
	 *
	 * The check is not performed on "guest" pages which don't require sharing
	 * to be enabled
	 *
	 * @inheritDoc
	 */
	public function beforeController($controller, $methodName) {
		$sharingEnabled = $this->isSharingEnabled();

		$isPublicPage = $this->reflector->hasAnnotation('PublicPage');
		$isGuest = $this->reflector->hasAnnotation('Guest');

		if ($isPublicPage && !$isGuest && !$sharingEnabled) {
			$this->logAndThrow("'Sharing is disabled'", Http::STATUS_SERVICE_UNAVAILABLE);
		}
	}

	/**
	 * Checks whether public sharing (via links) is enabled
	 *
	 * @return bool
	 */
	private function isSharingEnabled() {
		$shareApiAllowLinks = $this->config->getAppValue('core', 'shareapi_allow_links', 'yes');

		if ($shareApiAllowLinks !== 'yes') {
			return false;
		}

		return true;
	}

}