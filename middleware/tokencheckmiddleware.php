<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 *
 * @copyright Olivier Paroz 2014-2015
 * @copyright Bernhard Posselt 2012-2015
 */

namespace OCA\GalleryPlus\Middleware;

// FIXME: Private API. Fix only available in OC8
use \OC\AppFramework\Utility\ControllerMethodReflector;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ILogger;

use OCP\AppFramework\Http;

use OCA\GalleryPlus\Service\EnvironmentService;
use OCA\GalleryPlus\Service\ServiceException;


/**
 * Checks that we have a valid token linked to a valid resource and that the
 * user is authorised to access it
 *
 * @package OCA\GalleryPlus\Middleware
 */
class TokenCheckMiddleware extends CheckMiddleware {

	/**
	 * @type EnvironmentService
	 */
	private $environmentService;
	/**
	 * @type ControllerMethodReflector
	 */
	protected $reflector;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param EnvironmentService $environmentService
	 * @param ControllerMethodReflector $reflector
	 * @param IURLGenerator $urlGenerator
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		EnvironmentService $environmentService,
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

		$this->environmentService = $environmentService;
		$this->reflector = $reflector;
	}

	/**
	 * Checks that we have a valid token linked to a valid resource and that the
	 * user is authorised to access it
	 *
	 * Inspects the controller method annotations and if PublicPage is found
	 * it checks that we have token and an optional password giving access to a
	 * valid resource
	 *
	 * The check is not performed on "guest" pages which don't require a token
	 *
	 * @inheritDoc
	 */
	public function beforeController($controller, $methodName) {
		$token = $this->request->getParam('token');
		$password = $this->request->getParam('password');

		// This needs to be done here as the Dispatcher does not call our reflector
		$this->reflector->reflect($controller, $methodName);

		$isPublicPage = $this->reflector->hasAnnotation('PublicPage');
		$isGuest = $this->reflector->hasAnnotation('Guest');

		if ($isPublicPage && !$isGuest) {
			if (!$token) {
				throw new CheckException(
					"Can't access a public resource without a token",
					Http::STATUS_NOT_FOUND
				);
			} else { // We have a token

				// Let's see if it's linked to a valid resource
				try {
					$this->environmentService->checkToken($token);
				} catch (ServiceException $exception) {
					throw new CheckException(
						$exception->getMessage(),
						$exception->getCode()
					);
				}

				// Let's see if the user needs to provide a password
				try {
					$this->environmentService->checkAuthorisation($password);
				} catch (ServiceException $exception) {
					throw new CheckException(
						$exception->getMessage(),
						$exception->getCode()
					);
				}

				// Let's see if we can set up the environment for the controller
				try {
					$this->environmentService->setupTokenBasedEnv();
				} catch (ServiceException $exception) {
					throw new CheckException(
						$exception->getMessage(),
						$exception->getCode()
					);
				}
			}
		}
	}

}