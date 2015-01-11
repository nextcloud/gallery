<?php
/**
 * ownCloud - Gallery plus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Thomas Müller <deepdiver@owncloud.com>
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Thomas Müller 2014-2015
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\Middleware;

// FIXME: Private API. Fix only available in OC8
use \OC\AppFramework\Utility\ControllerMethodReflector;

use OCP\IRequest;
use OCP\ISession;

use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Middleware;


/**
 * Closes the session unless a controller methods specifically asks for it to
 * stay open
 *
 * @package OCA\GalleryPlus\Middleware
 */
class SessionMiddleware extends Middleware {

	/**
	 * @type IRequest
	 */
	private $request;
	/**
	 * @type ControllerMethodReflector
	 */
	private $reflector;

	/**
	 * @type ISession
	 */
	private $session;

	/**
	 * @param IRequest $request
	 * @param ControllerMethodReflector $reflector
	 * @param ISession $session
	 */
	public function __construct(
		IRequest $request,
		ControllerMethodReflector $reflector,
		ISession $session
	) {
		$this->request = $request;
		$this->reflector = $reflector;
		$this->session = $session;
	}

	/**
	 * Closes the session BEFORE calling the controller unless the method
	 * contains @UseSession
	 *
	 * @inheritDoc
	 */
	public function beforeController($controller, $methodName) {
		// This needs to be done here as the Dispatcher does not call our reflector
		$this->reflector->reflect($controller, $methodName);

		$useSession = $this->reflector->hasAnnotation('UseSession');
		if (!$useSession) {
			$this->session->close();
		}
	}

	/**
	 * Closes the session AFTER calling the controller unless the method
	 * contains @UseSession
	 *
	 * @inheritDoc
	 */
	public function afterController($controller, $methodName, Response $response) {
		$useSession = $this->reflector->hasAnnotation('UseSession');
		if ($useSession) {
			$this->session->close();
		}

		return $response;
	}

}
