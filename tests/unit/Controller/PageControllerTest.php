<?php
/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Tests\Controller;

use OCP\AppFramework\Http\Template\ExternalShareMenuAction;
use OCP\AppFramework\Http\Template\LinkMenuAction;
use OCP\AppFramework\Http\Template\SimpleMenuAction;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IRequest;
use OCP\IURLGenerator;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\Gallery\Controller\PageController;
use OCA\Gallery\Environment\Environment;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @package OCA\Gallery\Controller
 */
class PageControllerTest extends \Test\TestCase {

	/** @var string */
	private $appName = 'gallery';
	/** @var IRequest */
	private $request;
	/** @var Environment */
	private $environment;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IConfig */
	private $appConfig;
	/** @var PageController */
	protected $controller;
	/** @var EventDispatcherInterface */
	protected $dispatcher;
	/** @var IL10N */
	private $l10n;

	/**
	 * Test set up
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->environment = $this->createMock(Environment::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->appConfig = $this->createMock(IConfig::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->dispatcher = $this->createMock(EventDispatcherInterface::class);
		$this->controller = new PageController(
			$this->appName,
			$this->request,
			$this->environment,
			$this->urlGenerator,
			$this->appConfig,
			$this->dispatcher,
			$this->l10n
		);
	}


	public function testIndex() {
		$url = 'http://server.cloud/ajax/upload.php';
		$this->mockUrlToUploadEndpoint($url);
		$publicUploadEnabled = 'yes';
		$mailNotificationEnabled = 'no';
		$mailPublicNotificationEnabled = 'yes';
		$params = [
			'appName' => $this->appName,
			'uploadUrl' => $url,
			'publicUploadEnabled' => $publicUploadEnabled,
			'mailNotificationEnabled' => $mailNotificationEnabled,
			'mailPublicNotificationEnabled' => $mailPublicNotificationEnabled
		];
		$this->mockGetTestAppValue(
			$publicUploadEnabled, $mailNotificationEnabled, $mailPublicNotificationEnabled
		);

		$this->dispatcher->expects($this->once())
			->method('dispatch')
			->with('OCP\Share::loadSocial');

		$template = new TemplateResponse($this->appName, 'index', $params);

		$response = $this->controller->index();

		$this->assertEquals($params, $response->getParams());
		$this->assertEquals('index', $response->getTemplateName());
		$this->assertTrue($response instanceof TemplateResponse);
		$this->assertEquals($template->getStatus(), $response->getStatus());
	}

	public function testCspForImgContainsData() {
		$response = $this->controller->index();

		$this->assertContains(
			"img-src 'self' data:", $response->getHeaders()['Content-Security-Policy']
		);
	}

	public function testCspForFontsContainsData() {
		$response = $this->controller->index();

		$this->assertContains(
			"font-src 'self' data:", $response->getHeaders()['Content-Security-Policy']
		);
	}

	public function testSlideshow() {
		$template = new TemplateResponse($this->appName, 'slideshow', [], 'blank');

		$response = $this->controller->slideshow();

		$this->assertEquals('slideshow', $response->getTemplateName());
		$this->assertTrue($response instanceof TemplateResponse);
		$this->assertEquals($template->render(), $response->render());
	}

	public function testPublicIndexWithFolderToken() {
		$token = 'aaaabbbbccccdddd';
		$password = 'I am a password';
		$displayName = 'User X';
		$albumName = 'My Shared Folder';
		$protected = 'true';
		$server2ServerSharing = true;
		$server2ServerSharingEnabled = 'yes';
		$params = [
			'appName'              => $this->appName,
			'token'                => $token,
			'displayName'          => $displayName,
			'albumName'            => $albumName,
			'server2ServerSharing' => $server2ServerSharing,
			'protected'            => $protected,
			'filename'             => $albumName
		];
		$this->mockGetSharedNode('dir', 12345);
		$this->mockGetSharedFolderName($albumName);
		$this->mockGetDisplayName($displayName);
		$this->mockGetSharePassword($password);
		$this->mockGetAppValue($server2ServerSharingEnabled);
		$this->mockGetUserId('user1');
		$this->mockL10N();

		$template = new Http\Template\PublicTemplateResponse($this->appName, 'public', $params);

		$response = $this->controller->publicIndex($token, 'filename.txt');

		$this->assertEquals($params, $response->getParams());
		$this->assertEquals('public', $response->getTemplateName());
		$this->assertTrue($response instanceof Http\Template\PublicTemplateResponse);
		$this->assertEquals($template->getStatus(), $response->getStatus());
		$this->assertEquals($albumName, $response->getHeaderTitle());
		$this->assertEquals('shared by ' . $displayName, $response->getHeaderDetails());

		$actions = [
			new SimpleMenuAction('download', $this->l10n->t('Download'), 'icon-download-white', '', 0),
			new SimpleMenuAction('download', $this->l10n->t('Download'), 'icon-download', '', 10),
			new LinkMenuAction($this->l10n->t('Direct link'), 'icon-public', ''),
			new ExternalShareMenuAction($this->l10n->t('Add to your Nextcloud'), 'icon-external', 'user1', $displayName, $albumName)
		];
		$this->assertEquals($actions[0], $response->getPrimaryAction());
		$this->assertEquals(array_slice($actions, 1), $response->getOtherActions());
	}

	public function testPublicIndexWithFileToken() {
		$token = 'aaaabbbbccccdddd';
		$filename = 'happy.jpg';
		$fileId = 12345;

		$this->mockGetSharedNode('file', $fileId);
		$redirectUrl = 'http://server.cloud/download/' . $filename;
		$this->mockUrlToDownloadPage($token, $fileId, $filename, $redirectUrl);
		$template = new RedirectResponse($redirectUrl);

		$response = $this->controller->publicIndex($token, $filename);

		$this->assertTrue($response instanceof RedirectResponse);
		$this->assertEquals($template->getRedirectURL(), $response->getRedirectURL());
	}

	public function testErrorPage() {
		$message = 'Not found!';
		$code = Http::STATUS_NOT_FOUND;

		$this->mockCookieGet('galleryErrorMessage', $message);

		$response = $this->controller->errorPage($code);

		$this->assertEquals('index', $response->getTemplateName());
		$this->assertTrue($response instanceof TemplateResponse);
		$this->assertEquals($code, $response->getStatus());
		$this->assertContains($message, $response->getParams()['message']);
	}

	private function mockGetSharedNode($nodeType, $nodeId) {
		$folder = $this->getMockBuilder('OCP\Files\Folder')
					   ->disableOriginalConstructor()
					   ->getMock();
		$folder->method('getType')
			   ->willReturn($nodeType);
		$folder->method('getId')
			   ->willReturn($nodeId);

		$this->environment->expects($this->once())
						  ->method('getSharedNode')
						  ->willReturn($folder);
	}

	private function mockGetSharedFolderName($name) {
		$this->environment->expects($this->once())
						  ->method('getSharedFolderName')
						  ->willReturn($name);
	}

	private function mockGetDisplayName($name) {
		$this->environment->expects($this->once())
						  ->method('getDisplayName')
						  ->willReturn($name);
	}

	private function mockGetSharePassword($password) {
		$this->environment->expects($this->once())
						  ->method('getSharePassword')
						  ->willReturn($password);
	}

	private function mockGetAppValue($status) {
		$this->appConfig->expects($this->once())
						->method('getAppValue')
						->with(
							'files_sharing',
							'outgoing_server2server_share_enabled',
							'yes'
						)
						->willReturn($status);
	}

	private function mockGetUserId($userId) {
		$this->environment->expects($this->once())
			->method('getUserId')
			->willReturn($userId);
	}

	private function mockUrlToDownloadPage($token, $fileId, $filename, $url) {
		$this->urlGenerator->expects($this->once())
						   ->method('linkToRoute')
						   ->with(
							   $this->appName . '.files_public.download',
							   [
								   'token'    => $token,
								   'fileId'   => $fileId,
								   'filename' => $filename
							   ]
						   )
						   ->willReturn($url);
	}

	private function mockGetTestAppValue(
		$publicUploadEnabled, $mailNotificationEnabled, $mailPublicNotificationEnabled
	) {
		$map = [
			['core', 'shareapi_allow_public_upload', 'yes', $publicUploadEnabled],
			['core', 'shareapi_allow_mail_notification', 'no', $mailNotificationEnabled],
			['core', 'shareapi_allow_public_notification', 'no', $mailPublicNotificationEnabled]
		];
		$this->appConfig
			->method('getAppValue')
			->will(
				$this->returnValueMap($map)
			);
	}

	/**
	 * Needs to be called at least once by testDownloadWithWrongId() or the tests will fail
	 *
	 * @param $key
	 * @param $value
	 */
	private function mockCookieGet($key, $value) {
		$this->request->expects($this->once())
					  ->method('getCookie')
					  ->with($key)
					  ->willReturn($value);
	}

	private function mockUrlToUploadEndpoint($url) {
		$this->urlGenerator->expects($this->once())
						   ->method('linkTo')
						   ->with('files', 'ajax/upload.php')
						   ->willReturn($url);
	}

	private function mockL10N() {
		$this->l10n->expects($this->any())
			->method('t')
			->will($this->returnCallback(function($text, $params) {
				return vsprintf($text, $params);
			}));
	}
}
