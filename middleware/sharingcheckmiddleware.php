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

// FIXME: Private API. Fix only available in OC8
use \OC\AppFramework\Utility\ControllerMethodReflector;

use OCP\IAppConfig;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ILogger;

use OCP\AppFramework\Http;

/**
 * Checks whether the "sharing check" is enabled
 *
 * @package OCA\Files_Sharing\Middleware
 */
class SharingCheckMiddleware extends CheckMiddleware {

	/**
	 * @type IAppConfig
	 * */
	private $appConfig;
	/**
	 * @type ControllerMethodReflector
	 */
	protected $reflector;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IAppConfig $appConfig
	 * @param ControllerMethodReflector $reflector
	 * @param IURLGenerator $urlGenerator
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IAppConfig $appConfig,
		ControllerMethodReflector $reflector,
		IURLGenerator $urlGenerator,
		ILogger $logger
	) {
		parent::__construct(
			$appName,
			$request,
			$urlGenerator,
			$logger
		);

		$this->appConfig = $appConfig;
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

		// This needs to be done here as the Dispatcher does not call our reflector
		$this->reflector->reflect($controller, $methodName);

		$isPublicPage = $this->reflector->hasAnnotation('PublicPage');
		$isGuest = $this->reflector->hasAnnotation('Guest');

		if ($isPublicPage && !$isGuest && !$sharingEnabled) {
			throw new CheckException(
				'Sharing is disabled',
				Http::STATUS_SERVICE_UNAVAILABLE
			);
		}
	}

	/**
	 * Checks whether sharing is enabled in the OC config
	 *
	 * @return bool
	 */
	private function isSharingEnabled() {
		// Check whether public sharing (via links) is enabled
		if ($this->appConfig->getValue('core', 'shareapi_allow_links', 'yes')
			!== 'yes'
		) {
			return false;
		}

		return true;
	}

}