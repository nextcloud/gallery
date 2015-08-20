<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\GalleryPlus\Middleware;

use OC\AppFramework\Utility\ControllerMethodReflector;

use OCP\IRequest;
use OCP\Security\IHasher;
use OCP\ISession;
use OCP\ILogger;
use OCP\IURLGenerator;
use OCP\Share;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\IControllerMethodReflector;

use OCA\GalleryPlus\Environment\Environment;

/**
 * @package OCA\GalleryPlus\Middleware\EnvCheckMiddlewareTest
 */
class EnvCheckMiddlewareTest extends \Test\TestCase {

	/** @var string */
	private $appName = 'gallery';
	/** @var IRequest */
	private $request;
	/** @var IHasher    * */
	private $hasher;
	/** @var ISession     * */
	private $session;
	/** @var Environment */
	private $environment;
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
		$this->hasher = $this->getMockBuilder('\OCP\Security\IHasher')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->session = $this->getMockBuilder('\OCP\ISession')
							  ->disableOriginalConstructor()
							  ->getMock();
		$this->environment = $this->getMockBuilder('\OCA\GalleryPlus\Environment\Environment')
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

		$this->middleware = new EnvCheckMiddleware(
			$this->appName,
			$this->request,
			$this->hasher,
			$this->session,
			$this->environment,
			$this->reflector,
			$this->urlGenerator,
			$this->logger
		);
	}

	public function testCheckSessionAfterPasswordEntry() {
		$linkItem['id'] = 12345;
		$this->mockSessionExists($linkItem['id']);
		$this->mockSessionWithLinkItemId($linkItem['id']);

		self::invokePrivate($this->middleware, 'checkSession', [$linkItem]);
	}

	/**
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testCheckSessionBeforePasswordEntry() {
		$linkItem['id'] = 12345;
		$this->mockSessionExists(false);

		self::invokePrivate($this->middleware, 'checkSession', [$linkItem]);
	}

	/**
	 * Ids of linkItem do not match
	 *
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testCheckSessionWithWrongSession() {
		$linkItem['id'] = 12345;
		$this->mockSessionExists(true);
		$this->mockSessionWithLinkItemId(99999);

		self::invokePrivate($this->middleware, 'checkSession', [$linkItem]);
	}

	public function testCheckPasswordAfterValidPasswordEntry() {
		$password = 'Je suis une pipe';
		$linkItem = [
			'id'         => 12345,
			'share_with' => $password
		];
		$this->mockHasherVerify($password, $linkItem['share_with'], true);

		self::invokePrivate($this->middleware, 'checkPassword', [$linkItem, $password]);
	}

	/**
	 * Given password and token password don't match
	 *
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testCheckPasswordAfterInvalidPasswordEntry() {
		$password = 'Je suis une pipe';
		$linkItem = [
			'id'         => 12345,
			'share_with' => 'Empyrion Galactic Survival'
		];
		$this->mockHasherVerify($password, $linkItem['share_with'], false);

		self::invokePrivate($this->middleware, 'checkPassword', [$linkItem, $password]);
	}

	public function testAuthenticateAfterValidPasswordEntry() {
		$password = 'Je suis une pipe';
		$linkItem = [
			'id'         => 12345,
			'share_with' => $password,
			'share_type' => Share::SHARE_TYPE_LINK
		];
		$this->mockHasherVerify($password, $linkItem['share_with'], true);

		$this->assertTrue(
			self::invokePrivate($this->middleware, 'authenticate', [$linkItem, $password])
		);
	}

	/**
	 * Given password and token password don't match
	 *
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testAuthenticateAfterInvalidPasswordEntry() {
		$password = 'Je suis une pipe';
		$linkItem = [
			'id'         => 12345,
			'share_with' => 'Empyrion Galactic Survival',
			'share_type' => Share::SHARE_TYPE_LINK
		];
		$this->mockHasherVerify($password, $linkItem['share_with'], false);

		self::invokePrivate($this->middleware, 'authenticate', [$linkItem, $password]);
	}

	public function testCheckAuthorisationAfterValidPasswordEntry() {
		$password = 'Je suis une pipe';
		$linkItem = [
			'id'         => 12345,
			'share_with' => $password,
			'share_type' => Share::SHARE_TYPE_LINK
		];
		$this->mockHasherVerify($password, $linkItem['share_with'], true);

		self::invokePrivate($this->middleware, 'checkAuthorisation', [$linkItem, $password]);
	}

	/**
	 * Given password and token password don't match
	 *
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testCheckAuthorisationAfterInvalidPasswordEntry() {
		$password = 'Je suis une pipe';
		$linkItem = [
			'id'         => 12345,
			'share_with' => 'Empyrion Galactic Survival',
			'share_type' => Share::SHARE_TYPE_LINK
		];
		$this->mockHasherVerify($password, $linkItem['share_with'], false);

		self::invokePrivate($this->middleware, 'checkAuthorisation', [$linkItem, $password]);
	}

	/**
	 * It will use the session, wich is a valid one in this case
	 * Other cases are in the checkSession tests
	 */
	public function testCheckAuthorisationWithNoPassword() {
		$password = null;
		$linkItem = [
			'id'         => 12345,
			'share_with' => 'Empyrion Galactic Survival'
		];
		$this->mockSessionExists($linkItem['id']);
		$this->mockSessionWithLinkItemId($linkItem['id']);

		self::invokePrivate($this->middleware, 'checkAuthorisation', [$linkItem, $password]);
	}

	public function testCheckItemTypeWithItemTypeSet() {
		$linkItem = [
			'id'        => 12345,
			'item_type' => 'folder'
		];

		self::invokePrivate($this->middleware, 'checkItemType', [$linkItem]);
	}

	/**
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testCheckItemTypeWithItemTypeNotSet() {
		$linkItem = [
			'id' => 12345,
		];

		self::invokePrivate($this->middleware, 'checkItemType', [$linkItem]);
	}

	public function testCheckLinkItemIsValidWithValidLinkItem() {
		$linkItem = [
			'id'          => 12345,
			'uid_owner'   => 'tester',
			'file_source' => 'folder1'
		];
		$token = 'aaaabbbbccccdddd';

		self::invokePrivate($this->middleware, 'checkLinkItemIsValid', [$linkItem, $token]);
	}

	/**
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testCheckLinkItemIsValidWithMissingOwner() {
		$linkItem = [
			'id'          => 12345,
			'file_source' => 'folder1'
		];
		$token = 'aaaabbbbccccdddd';

		self::invokePrivate($this->middleware, 'checkLinkItemIsValid', [$linkItem, $token]);
	}

	/**
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testCheckLinkItemIsValidWithMissingSource() {
		$linkItem = [
			'id'        => 12345,
			'uid_owner' => 'tester',
		];
		$token = 'aaaabbbbccccdddd';

		self::invokePrivate($this->middleware, 'checkLinkItemIsValid', [$linkItem, $token]);
	}

	/**
	 * @return array
	 */
	public function providesItemTypes() {
		return [
			['file'],
			['folder']
		];
	}

	/**
	 * @dataProvider providesItemTypes
	 *
	 * @param string $type
	 */
	public function testCheckLinkItemExistsWithValidLinkItem($type) {
		$linkItem = [
			'item_type' => $type
		];

		self::invokePrivate($this->middleware, 'checkLinkItemExists', [$linkItem]);
	}

	/**
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testCheckLinkItemExistsWithEmptyLinkItem() {
		$linkItem = false;

		self::invokePrivate($this->middleware, 'checkLinkItemExists', [$linkItem]);
	}

	/**
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testCheckLinkItemExistsWithWeirdLinkItem() {
		$linkItem = [
			'item_type' => 'cheese'
		];

		self::invokePrivate($this->middleware, 'checkLinkItemExists', [$linkItem]);
	}

	/**
	 * @expectedException \OCA\GalleryPlus\Middleware\CheckException
	 */
	public function testNoTokenFound() {
		self::invokePrivate($this->middleware, 'noTokenFound');
	}

	/**
	 * Mocks ISession->exists('public_link_authenticated')
	 *
	 * @param int $linkItemId
	 */
	private function mockSessionExists($linkItemId) {
		$this->session->expects($this->once())
					  ->method('exists')
					  ->with('public_link_authenticated')
					  ->willReturn($linkItemId);
	}

	/**
	 * Mocks ISession->get('public_link_authenticated')
	 *
	 * @param int $linkItemId
	 */
	private function mockSessionWithLinkItemId($linkItemId) {
		$this->session->expects($this->once())
					  ->method('get')
					  ->with('public_link_authenticated')
					  ->willReturn($linkItemId);
	}

	/**
	 * @param string $givenPassword
	 * @param string $tokenPassword
	 * @param bool $valid
	 */
	private function mockHasherVerify($givenPassword, $tokenPassword, $valid) {
		$this->hasher->expects($this->once())
					 ->method('verify')
					 ->with(
						 $givenPassword,
						 $tokenPassword,
						 ''
					 )
					 ->willReturn($valid);
	}

}
