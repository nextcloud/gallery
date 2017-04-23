<?php
/**
 * Nextcloud - Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Lukas Reschke 2017
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Middleware;

use OCP\IConfig;
use OCP\IRequest;
use OCP\ILogger;
use OCP\IURLGenerator;

use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\IControllerMethodReflector;

/**
 * Checks whether the "sharing check" is enabled
 *
 * @package OCA\Gallery\SharingCheckMiddleware
 */
class SharingCheckMiddleware extends CheckMiddleware {

	/** @var IConfig */
	private $config;
	/** @var IControllerMethodReflector */
	protected $reflector;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IConfig $appConfig
	 * @param IControllerMethodReflector $reflector
	 * @param IURLGenerator $urlGenerator
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IConfig $appConfig,
		IControllerMethodReflector $reflector,
		IURLGenerator $urlGenerator,
		ILogger $logger
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
	 * Checks if sharing is enabled before the controllers is executed
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
		if ($this->reflector->hasAnnotation('Guest')) {
			return;
		}

		$sharingEnabled = $this->isSharingEnabled();
		$isPublicPage = $this->reflector->hasAnnotation('PublicPage');
		if ($isPublicPage && !$sharingEnabled) {
			throw new CheckException("'Sharing is disabled'", Http::STATUS_SERVICE_UNAVAILABLE);
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
