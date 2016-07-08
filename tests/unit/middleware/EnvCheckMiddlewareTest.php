<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2016
 */

namespace OCA\Gallery\Middleware;

use OC\AppFramework\Utility\ControllerMethodReflector;

use OCP\Constants;
use OCP\IRequest;
use OCP\Security\IHasher;
use OCP\ISession;
use OCP\ILogger;
use OCP\IURLGenerator;
use OCP\Share;
use OCP\Share\IManager;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\IControllerMethodReflector;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;

use OCA\Gallery\Environment\EnvironmentException;

/**
 * @package OCA\Gallery\Middleware\EnvCheckMiddlewareTest
 */
class EnvCheckMiddlewareTest extends \Test\GalleryUnitTest {

	/** @var IRequest */
	private $request;
	/** @var IHasher */
	private $hasher;
	/** @var ISession */
	private $session;
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
	/** @var IManager */
	private $shareManager;

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
		$this->environment = $this->getMockBuilder('\OCA\Gallery\Environment\Environment')
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
		$this->shareManager = $this->getMockBuilder('\OCP\Share\IManager')
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
			$this->shareManager,
			$this->logger
		);
	}

	/**
	 * @todo Mock an environment response
	 */
	public function testBeforeControllerWithoutNotation() {
		$this->reflector->reflect(__CLASS__, __FUNCTION__);
		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * @PublicPage
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testBeforeControllerWithPublicNotationAndInvalidToken() {
		$this->reflector->reflect(__CLASS__, __FUNCTION__);

		$token = 'aaaabbbbccccdddd';
		$this->mockGetTokenParam($token);

		$this->mockShareManagerGetShareByTokenThrowsException($token);

		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * @PublicPage
	 */
	public function testBeforeControllerWithPublicNotationAndToken() {
		$this->reflector->reflect(__CLASS__, __FUNCTION__);

		$token = 'aaaabbbbccccdddd';
		$this->mockGetTokenAndPasswordParams($token, null);

		$share = $this->mockShare(
			'folder', 'tester', 'shared1', Share::SHARE_TYPE_LINK, null,
			Constants::PERMISSION_READ, null
		);

		$this->mockShareManagerGetShareByToken($token, $share);

		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * @PublicPage
	 */
	public function testBeforeControllerWithPublicNotationAndTokenAndValidPassword() {
		$this->reflector->reflect(__CLASS__, __FUNCTION__);

		$token = 'aaaabbbbccccdddd';
		$password = 'validpassword';
		$this->mockGetTokenAndPasswordParams($token, $password);

		$share = $this->mockShare(
			'folder', 'tester', 'shared1', Share::SHARE_TYPE_LINK, null,
			Constants::PERMISSION_READ, 'validpassword'
		);

		$this->mockShareManagerGetShareByToken($token, $share);
		$this->mockShareManagerCheckPassword($share, $password, true);

		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * @PublicPage
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testBeforeControllerWithPublicNotationAndTokenAndInvalidPassword() {
		$this->reflector->reflect(__CLASS__, __FUNCTION__);

		$token = 'aaaabbbbccccdddd';
		$password = 'NOTvalidpassword';
		$this->mockGetTokenAndPasswordParams($token, $password);

		$share = $this->mockShare(
			'folder', 'tester', 'shared1', Share::SHARE_TYPE_LINK, null,
			Constants::PERMISSION_READ, 'validpassword'
		);

		$this->mockShareManagerGetShareByToken($token, $share);
		$this->mockShareManagerCheckPassword($share, 'NOTvalidpassword', false);

		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * @PublicPage
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testBeforeControllerWithPublicNotationAndNoToken() {
		$this->reflector->reflect(__CLASS__, __FUNCTION__);

		$token = null;
		$this->mockGetTokenParam($token);
		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * @@Guest
	 */
	public function testBeforeControllerWithGuestNotation() {
		$this->reflector->reflect(__CLASS__, __FUNCTION__);

		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	public function testCheckSessionAfterPasswordEntry() {
		$share = $this->mockShare('file', 'tester', 'image.png');

		$this->mockSessionExists((string)$share->getId());
		$this->mockSessionWithShareId((string)$share->getId());

		parent::invokePrivate($this->middleware, 'checkSession', [$share]);
	}

	/**
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckSessionBeforePasswordEntry() {
		$share = $this->mockShare('file', 'tester', 'image.png');

		$this->mockSessionExists(false);

		parent::invokePrivate($this->middleware, 'checkSession', [$share]);
	}

	/**
	 * Ids of shares do not match
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckSessionWithWrongSession() {
		$share = $this->mockShare('file', 'tester', 'image.png');

		$this->mockSessionExists(true);
		$this->mockSessionWithShareId(99999);

		parent::invokePrivate($this->middleware, 'checkSession', [$share]);
	}

	public function testCheckPasswordAfterValidPasswordEntry() {
		$password = 'Je suis une pipe';
		$share = $this->mockShare(
			'file', 'tester', 'image.png', Share::SHARE_TYPE_USER, 'externaluser',
			Constants::PERMISSION_READ, $password
		);

		$this->mockShareManagerCheckPassword($share, $password, true);

		parent::invokePrivate($this->middleware, 'checkPassword', [$share, $password]);
	}

	/**
	 * Given password and token password don't match
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckPasswordAfterInvalidPasswordEntry() {
		$password = 'Je suis une pipe';
		$wrongPassword = 'Empyrion Galactic Survival';
		$share = $this->mockShare(
			'file', 'tester', 'image.png', Share::SHARE_TYPE_USER, 'externaluser',
			Constants::PERMISSION_READ,
			$wrongPassword
		);

		$this->mockShareManagerCheckPassword($share, $password, false);

		parent::invokePrivate($this->middleware, 'checkPassword', [$share, $password]);
	}

	public function testAuthenticateAfterValidPasswordEntry() {
		$password = 'Je suis une pipe';
		$share = $this->mockShare(
			'file', 'tester', 'image.png', Share::SHARE_TYPE_LINK, null, Constants::PERMISSION_READ,
			$password
		);

		$this->mockShareManagerCheckPassword($share, $password, true);

		$this->assertTrue(
			parent::invokePrivate($this->middleware, 'authenticate', [$share, $password])
		);
	}

	/**
	 * Given password and token password don't match
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testAuthenticateAfterInvalidPasswordEntry() {
		$password = 'Je suis une pipe';
		$wrongPassword = 'Empyrion Galactic Survival';
		$share = $this->mockShare(
			'file', 'tester', 'image.png', Share::SHARE_TYPE_LINK, null, Constants::PERMISSION_READ,
			$wrongPassword
		);

		$this->mockShareManagerCheckPassword($share, $password, false);

		parent::invokePrivate($this->middleware, 'authenticate', [$share, $password]);
	}

	/**
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testAuthenticateWithWrongLinkType() {
		$password = 'Je suis une pipe';
		$share = $this->mockShare(
			'file', 'tester', 'image.png', Share::SHARE_TYPE_LINK, 'tester',
			Constants::PERMISSION_READ, $password
		);

		$this->mockShareManagerCheckPassword($share, $password, false);

		parent::invokePrivate($this->middleware, 'authenticate', [$share, $password]);
	}

	public function testCheckAuthorisationAfterValidPasswordEntry() {
		$password = 'Je suis une pipe';
		$share = $this->mockShare(
			'file', 'tester', 'image.png', Share::SHARE_TYPE_LINK, 'tester',
			Constants::PERMISSION_READ, $password
		);

		$this->mockShareManagerCheckPassword($share, $password, true);

		parent::invokePrivate($this->middleware, 'checkAuthorisation', [$share, $password]);
	}

	/**
	 * Given password and token password don't match
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckAuthorisationAfterInvalidPasswordEntry() {
		$password = 'Je suis une pipe';
		$wrongPassword = 'Empyrion Galactic Survival';
		$share = $this->mockShare(
			'file', 'tester', 'image.png', Share::SHARE_TYPE_LINK, 'tester',
			Constants::PERMISSION_READ, $wrongPassword
		);

		$this->mockShareManagerCheckPassword($share, $password, false);

		parent::invokePrivate($this->middleware, 'checkAuthorisation', [$share, $password]);
	}

	/**
	 * It will use the session, wich is a valid one in this case
	 * Other cases are in the checkSession tests
	 */
	public function testCheckAuthorisationWithNoPassword() {
		$password = null;
		$wrongPassword = 'Empyrion Galactic Survival';
		$share = $this->mockShare(
			'file', 'tester', 'image.png', Share::SHARE_TYPE_LINK, 'tester',
			Constants::PERMISSION_READ, $wrongPassword
		);

		$this->mockSessionExists((string)$share->getId());
		$this->mockSessionWithShareId((string)$share->getId());
		parent::invokePrivate($this->middleware, 'checkAuthorisation', [$share, $password]);
	}

	public function testCheckItemTypeWithItemTypeSet() {
		$share = $this->mockShare('folder', 'tester', 'folder1');

		parent::invokePrivate($this->middleware, 'checkItemType', [$share]);
	}

	/**
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckItemTypeWithItemTypeNotSet() {
		$share = $this->mockShare(null, 'tester', 'folder1');

		parent::invokePrivate($this->middleware, 'checkItemType', [$share]);
	}

	public function testCheckShareIsValidWithValidShare() {
		$share = $this->mockShare('file', 'tester', 'image.png');

		$token = 'aaaabbbbccccdddd';

		parent::invokePrivate($this->middleware, 'checkShareIsValid', [$share, $token]);
	}

	/**
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckShareIsValidWithMissingOwner() {
		$share = $this->mockShare('file', null, 'image.png');

		$token = 'aaaabbbbccccdddd';

		parent::invokePrivate($this->middleware, 'checkShareIsValid', [$share, $token]);
	}

	/**
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckShareIsValidWithMissingSource() {
		$share = $this->mockShare('file', 'tester', null);

		$token = 'aaaabbbbccccdddd';

		parent::invokePrivate($this->middleware, 'checkShareIsValid', [$share, $token]);
	}

	public function testAfterExceptionWithCheckExceptionAndHtmlAcceptAnd401Code() {
		$message = 'fail';
		$code = Http::STATUS_UNAUTHORIZED;
		$exception = new CheckException($message, $code);

		$template = $this->mockHtml401Response();

		$response =
			$this->middleware->afterException($this->controller, 'checkSession', $exception);

		$this->assertEquals($template, $response);
	}

	public function testAfterExceptionWithCheckExceptionAndHtmlAcceptAnd404Code() {
		$message = 'fail';
		$code = Http::STATUS_NOT_FOUND;
		$exception = new CheckException($message, $code);

		$redirectUrl = '/app/error/route';
		$this->mockHtml404Response($redirectUrl, $code);

		$response =
			$this->middleware->afterException($this->controller, 'authenticate', $exception);

		$this->assertEquals($redirectUrl, $response->getRedirectURL());
		$this->assertEquals(Http::STATUS_SEE_OTHER, $response->getStatus());
		$this->assertEquals($message, $response->getCookies()['galleryErrorMessage']['value']);
	}

	public function testAfterExceptionWithCheckExceptionAndJsonAccept() {
		$message = 'fail';
		$code = Http::STATUS_NOT_FOUND;
		$exception = new CheckException($message, $code);

		$template = $this->mockJsonResponse($message, $code);

		$response =
			$this->middleware->afterException(
				$this->controller, 'checkShareIsValid', $exception
			);

		$this->assertEquals($template, $response);
	}

	/**
	 * @expectedException \OCA\Gallery\Environment\EnvironmentException
	 */
	public function testAfterExceptionWithNonCheckException() {
		$message = 'fail';
		$code = Http::STATUS_NOT_FOUND;
		$exception = new EnvironmentException($message, $code);

		$this->middleware->afterException($this->controller, 'checkShareIsValid', $exception);
	}

	/**
	 * Mocks ISession->exists('public_link_authenticated')
	 *
	 * @param int $shareId
	 */
	private function mockSessionExists($shareId) {
		$this->session->expects($this->once())
					  ->method('exists')
					  ->with('public_link_authenticated')
					  ->willReturn($shareId);
	}

	/**
	 * Mocks ISession->get('public_link_authenticated')
	 *
	 * @param int $shareId
	 */
	private function mockSessionWithShareId($shareId) {
		$this->session->expects($this->once())
					  ->method('get')
					  ->with('public_link_authenticated')
					  ->willReturn($shareId);
	}

	private function mockHtml401Response() {
		$this->mockAcceptHeader('html');
		$this->mockGetParams();

		return new TemplateResponse($this->appName, 'authenticate', [], 'guest');
	}

	private function mockHtml404Response($redirectUrl, $code) {
		$this->mockAcceptHeader('html');
		$this->mockUrlToErrorPage($code, $redirectUrl);
	}

	private function mockJsonResponse($message, $code) {
		$this->mockAcceptHeader('json');
		$jsonData = [
			'message' => $message,
			'success' => false
		];

		return new JSONResponse($jsonData, $code);
	}

	/**
	 * Mocks IRequest->getHeader('Accept')
	 *
	 * @param string $type
	 */
	private function mockAcceptHeader($type) {
		$this->request->expects($this->once())
					  ->method('getHeader')
					  ->with('Accept')
					  ->willReturn($type);
	}

	/**
	 * Mocks IRequest->getParams()
	 */
	private function mockGetParams() {
		$this->request->expects($this->once())
					  ->method('getParams')
					  ->willReturn([]);
	}

	/**
	 * Mocks IURLGenerator->linkToRoute()
	 *
	 * @param int $code
	 * @param string $url
	 */
	private function mockUrlToErrorPage($code, $url) {
		$this->urlGenerator->expects($this->once())
						   ->method('linkToRoute')
						   ->with($this->appName . '.page.error_page', ['code' => $code])
						   ->willReturn($url);
	}

	/**
	 * Mocks IRequest->getParam()
	 *
	 * @param string $token
	 * @param string $password
	 */
	private function mockGetTokenAndPasswordParams($token, $password = null) {
		$this->request->expects($this->at(0))
					  ->method('getParam')
					  ->with('token')
					  ->willReturn($token);
		$this->request->expects($this->at(1))
					  ->method('getParam')
					  ->with('password')
					  ->willReturn($password);
	}

	/**
	 * Mocks IRequest->getParam('token')
	 *
	 * @param string $token
	 */
	private function mockGetTokenParam($token) {
		$this->request->expects($this->once())
					  ->method('getParam')
					  ->with('token')
					  ->willReturn($token);
	}

	private function mockShare(
		$nodeType,
		$shareOwner,
		$target,
		$shareType = Share::SHARE_TYPE_USER,
		$sharedWith = 'externaluser',
		$permission = Constants::PERMISSION_READ,
		$password = 'securePassword'
	) {
		$share = $this->getMockBuilder('OCP\Share\IShare')
					  ->disableOriginalConstructor()
					  ->getMock();

		$share->method('getId')
			  ->willReturn(12345);
		$share->method('getNodeType')
			  ->willReturn($nodeType);
		$share->method('getShareOwner')
			  ->willReturn($shareOwner);
		$share->method('getTarget')
			  ->willReturn($target);
		$share->method('getShareType')
			  ->willReturn($shareType);
		$share->method('getSharedWith')
			  ->willReturn($sharedWith);
		$share->method('getPermissions')
			  ->willReturn($permission);
		$share->method('getPassword')
			  ->willReturn($password);

		return $share;
	}

	private function mockShareManagerGetShareByToken($token, $share) {
		$this->shareManager->expects($this->once())
						   ->method('getShareByToken')
						   ->with($token)
						   ->willReturn($share);
	}

	private function mockShareManagerGetShareByTokenThrowsException($token) {
		$this->shareManager->expects($this->once())
						   ->method('getShareByToken')
						   ->with($token)
						   ->willThrowException(
							   new \OCP\Share\Exceptions\ShareNotFound(
								   "Can't find a share using that token"
							   )
						   );
	}

	private function mockShareManagerCheckPassword($share, $password, $result) {
		$this->shareManager->expects($this->once())
						   ->method('checkPassword')
						   ->with($share, $password)
						   ->willReturn($result);
	}

}
