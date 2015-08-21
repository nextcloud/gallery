<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Lukas Reschke
 * @author Bernhard Posselt
 *
 * @copyright Olivier Paroz 2015
 * @copyright ownCloud Inc. 2015
 */

namespace OCA\Gallery\Middleware;

use OC\AppFramework\Utility\ControllerMethodReflector;

use OCP\IConfig;
use OCP\IRequest;
use OCP\ILogger;
use OCP\IURLGenerator;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\IControllerMethodReflector;

/**
 * @package OCA\Gallery\Middleware\SharingCheckMiddleware
 *
 * @todo It's not possible to test if beforeController still works as expected when sharing is
 *     disabled without creating a full working environment or by refactoring isSharingEnabled
 */
class SharingCheckMiddlewareTest extends \Test\TestCase {

	/** @var string */
	private $appName = 'gallery';
	/** @var IRequest */
	private $request;
	/** @var IConfig */
	private $config;
	/** @var IControllerMethodReflector */
	protected $reflector;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var ILogger */
	protected $logger;
	/** @var Controller */
	private $controller;
	/** @var SharingCheckMiddleware */
	private $middleware;

	/**
	 * Test set up
	 */
	protected function setUp() {
		parent::setUp();

		$this->request = $this->getMockBuilder('\OCP\IRequest')
							  ->disableOriginalConstructor()
							  ->getMock();
		$this->config = $this->getMockBuilder('\OCP\IConfig')
							 ->disableOriginalConstructor()
							 ->getMock();
		// We need to use a real reflector to be able to test our custom notation
		$this->reflector = new ControllerMethodReflector();
		$this->urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
								   ->disableOriginalConstructor()
								   ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->controller = $this->getMockBuilder('OCP\AppFramework\Controller')
								 ->disableOriginalConstructor()
								 ->getMock();

		$this->middleware = new SharingCheckMiddleware(
			$this->appName,
			$this->request,
			$this->config,
			$this->reflector,
			$this->urlGenerator,
			$this->logger
		);
	}

	public function testIsSharingEnabledWithSharingEnabled() {
		$this->mockSharingConfigTo('yes');

		$this->assertTrue(self::invokePrivate($this->middleware, 'isSharingEnabled'));
	}

	public function testIsSharingEnabledWithSharingDisabled() {
		$this->mockSharingConfigTo('no');

		$this->assertFalse(self::invokePrivate($this->middleware, 'isSharingEnabled'));
	}

	/**
	 * @Guest
	 * @PublicPage
	 *
	 * Guest pages don't need to be checked
	 */
	public function testBeforeControllerWithGuestNotation() {
		$this->reflector->reflect(__CLASS__, __FUNCTION__);
		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * @PublicPage
	 *
	 * Contains PublicPage notation, does not contain Guest notation and sharing is enabled, so
	 *     should not throw any exception
	 */
	public function testBeforeControllerWithAllRequirements() {
		$this->mockSharingConfigTo('yes');
		$this->reflector->reflect(__CLASS__, __FUNCTION__);
		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * Non-public pages don't need to be checked
	 */
	public function testBeforeControllerWithoutPublicPageNotation() {
		$this->mockSharingConfigTo('yes');

		$this->reflector->reflect(__CLASS__, __FUNCTION__);
		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * @PublicPage
	 *
	 * Sharing needs to be enabled on public pages
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testBeforeControllerWithSharingDisabled() {
		$this->mockSharingConfigTo('no');
		$this->reflector->reflect(__CLASS__, __FUNCTION__);
		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * Mocks IConfig->getAppValue
	 *
	 * @param $state
	 */
	private function mockSharingConfigTo($state) {
		$this->config->expects($this->once())
					 ->method('getAppValue')
					 ->with(
						 'core',
						 'shareapi_allow_links',
						 'yes'
					 )
					 ->willReturn($state);
	}

}
