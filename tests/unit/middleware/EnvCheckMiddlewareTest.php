<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\Gallery\Middleware;

use OC\AppFramework\Utility\ControllerMethodReflector;
use Helper\CoreTestCase;

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

use OCA\Gallery\Environment\Environment;
use OCA\Gallery\Environment\EnvironmentException;

/**
 * @package OCA\Gallery\Middleware\EnvCheckMiddlewareTest
 */
class EnvCheckMiddlewareTest extends \Codeception\TestCase\Test {

	/** @var CoreTestCase */
	private $coreTestCase;

	/** @var string */
	private $appName = 'gallery';
	/** @var IRequest */
	private $request;
	/** @var IHasher */
	private $hasher;
	/** @var ISession */
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

	/** @var string */
	public $sharedFolderToken;
	/** @var string */
	public $passwordForFolderShare;
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

		/**
		 * Injects objects we need to bypass the static methods
		 *
		 * CODECEPTION SPECIFIC
		 */
		$setupData = $this->getModule('\Helper\DataSetup');
		$this->sharedFolderToken = $setupData->sharedFolderToken;
		$this->passwordForFolderShare = $setupData->passwordForFolderShare;
		$this->coreTestCase = $setupData->coreTestCase;
	}

	/**
	 * Invokes private methods
	 *
	 * CODECEPTION SPECIFIC
	 * This is from the core TestCase
	 *
	 * @param $object
	 * @param $methodName
	 * @param array $parameters
	 *
	 * @return mixed
	 */
	public function invokePrivate($object, $methodName, array $parameters = []) {
		return $this->coreTestCase->invokePrivate($object, $methodName, $parameters);
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

		$share = $this->newShare();
		$this->shareManager
			->expects($this->once())
			->method('getShareByToken')
			->with($token)
			->willReturn($share);

		$this->middleware->beforeController(__CLASS__, __FUNCTION__);
	}

	/**
	 * @PublicPage
	 *
	 * Because the method tested is static, we need to load our test environment \Helper\DataSetup
	 */
	public function testBeforeControllerWithPublicNotationAndToken() {
		$this->reflector->reflect(__CLASS__, __FUNCTION__);

		$this->mockGetTokenAndPasswordParams(
			$this->sharedFolderToken, $this->passwordForFolderShare
		);

		$share = $this->newShare();
		$share->setId(12345)
			->setNodeType('folder')
			->setShareOwner('test')
			->setTarget('folder1')
			->setShareType(Share::SHARE_TYPE_LINK)
			->setSharedWith('validpassword');
		$this->shareManager
			->expects($this->once())
			->method('getShareByToken')
			->with($this->sharedFolderToken)
			->willReturn($share);

		$this->shareManager
			->expects($this->once())
			->method('checkPassword')
			->with($share, $this->passwordForFolderShare)
			->willReturn(true);

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
		$share = $this->newShare();
		$share->setId(12345);
		$this->mockSessionExists($share->getId());
		$this->mockSessionWithShareId($share->getId());

		self::invokePrivate($this->middleware, 'checkSession', [$share]);
	}

	/**
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckSessionBeforePasswordEntry() {
		$share = $this->newShare();
		$share->setId(12345);
		$this->mockSessionExists(false);

		self::invokePrivate($this->middleware, 'checkSession', [$share]);
	}

	/**
	 * Ids of shares do not match
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckSessionWithWrongSession() {
		$share = $this->newShare();
		$share->setId(12345);
		$this->mockSessionExists(true);
		$this->mockSessionWithShareId(99999);

		self::invokePrivate($this->middleware, 'checkSession', [$share]);
	}

	public function testCheckPasswordAfterValidPasswordEntry() {
		$password = 'Je suis une pipe';
		$share = $this->newShare();
		$share->setId(12345)
			->setSharedWith($password);
		$this->shareManager
			->expects($this->once())
			->method('checkPassword')
			->with($share, $password)
			->willReturn(true);

		self::invokePrivate($this->middleware, 'checkPassword', [$share, $password]);
	}

	/**
	 * Given password and token password don't match
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckPasswordAfterInvalidPasswordEntry() {
		$password = 'Je suis une pipe';
		$share = $this->newShare();
		$share->setId(12345)
			->setSharedWith('Empyrion Galactic Survival');
		$this->shareManager
			->expects($this->once())
			->method('checkPassword')
			->with($share, $password)
			->willReturn(false);

		self::invokePrivate($this->middleware, 'checkPassword', [$share, $password]);
	}

	public function testAuthenticateAfterValidPasswordEntry() {
		$password = 'Je suis une pipe';
		$share = $this->newShare();
		$share->setId(12345)
			->setSharedWith($password)
			->setShareType(\OCP\Share::SHARE_TYPE_LINK);
		$this->shareManager
			->expects($this->once())
			->method('checkPassword')
			->with($share, $password)
			->willReturn(true);

		$this->assertTrue(
			self::invokePrivate($this->middleware, 'authenticate', [$share, $password])
		);
	}

	/**
	 * Given password and token password don't match
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testAuthenticateAfterInvalidPasswordEntry() {
		$password = 'Je suis une pipe';
		$share = $this->newShare();
		$share->setId(12345)
			->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setSharedWith('Empyrion Galactic Survival');
		$this->shareManager
			->expects($this->once())
			->method('checkPassword')
			->with($share, $password)
			->willReturn(false);

		self::invokePrivate($this->middleware, 'authenticate', [$share, $password]);
	}

	/**
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testAuthenticateWithWrongLinkType() {
		$password = 'Je suis une pipe';
		$share = $this->newShare();
		$share->setId(12345)
			->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setSharedWith('tester');
		$this->shareManager
			->expects($this->once())
			->method('checkPassword')
			->with($share, $password)
			->willReturn(false);

		self::invokePrivate($this->middleware, 'authenticate', [$share, $password]);
	}

	public function testCheckAuthorisationAfterValidPasswordEntry() {
		$password = 'Je suis une pipe';
		$share = $this->newShare();
		$share->setId(12345)
			->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setSharedWith($password);
		$this->shareManager
			->expects($this->once())
			->method('checkPassword')
			->with($share, $password)
			->willReturn(true);

		self::invokePrivate($this->middleware, 'checkAuthorisation', [$share, $password]);
	}

	/**
	 * Given password and token password don't match
	 *
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckAuthorisationAfterInvalidPasswordEntry() {
		$password = 'Je suis une pipe';
		$share = $this->newShare();
		$share->setId(12345)
			->setShareType(\OCP\Share::SHARE_TYPE_LINK)
			->setSharedWith('Empyrion Galactic Survival');
		$this->shareManager
			->expects($this->once())
			->method('checkPassword')
			->with($share, $password)
			->willReturn(false);

		self::invokePrivate($this->middleware, 'checkAuthorisation', [$share, $password]);
	}

	/**
	 * It will use the session, wich is a valid one in this case
	 * Other cases are in the checkSession tests
	 */
	public function testCheckAuthorisationWithNoPassword() {
		$password = null;
		$share = $this->newShare();
		$share->setId(12345)
			->setSharedWith('Empyrion Galactic Survival');
		$this->mockSessionExists($share->getId());
		$this->mockSessionWithShareId($share->getId());
		self::invokePrivate($this->middleware, 'checkAuthorisation', [$share, $password]);
	}

	public function testCheckItemTypeWithItemTypeSet() {
		$share = $this->newShare();
		$share->setId(12345)
			->setNodeType('folder');

		self::invokePrivate($this->middleware, 'checkItemType', [$share]);
	}

	/**
	 * @expectedException \OCP\Files\NotFoundException
	 */
	public function testCheckItemTypeWithItemTypeNotSet() {
		$share = $this->newShare();
		$share->setId(12345);

		self::invokePrivate($this->middleware, 'checkItemType', [$share]);
	}

	public function testCheckShareIsValidWithValidShare() {
		$share = $this->newShare();
		$share->setId(12345)
			->setShareOwner('tester')
			->setTarget('folder1');
		$token = 'aaaabbbbccccdddd';

		self::invokePrivate($this->middleware, 'checkShareIsValid', [$share, $token]);
	}

	/**
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckShareIsValidWithMissingOwner() {
		$share = $this->newShare();
		$share->setId(12345)
			->setTarget('folder1');
		$token = 'aaaabbbbccccdddd';

		self::invokePrivate($this->middleware, 'checkShareIsValid', [$share, $token]);
	}

	/**
	 * @expectedException \OCA\Gallery\Middleware\CheckException
	 */
	public function testCheckShareIsValidWithMissingSource() {
		$share = $this->newShare();
		$share->setId(12345)
			->setShareOwner('tester');
		$token = 'aaaabbbbccccdddd';

		self::invokePrivate($this->middleware, 'checkShareIsValid', [$share, $token]);
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
	 * @param $token
	 * @param $password
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
	 */
	private function mockGetTokenParam($token) {
		$this->request->expects($this->any())
			->method('getParam')
			->with('token')
			->willReturn($token);
	}

	private function newShare(){
		return \OC::$server->getShareManager()->newShare();
	}

}
