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

namespace OCA\Gallery\Controller;

use OCP\IConfig;
use OCP\IRequest;
use OCP\IURLGenerator;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\Gallery\Environment\Environment;

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

	/**
	 * Test set up
	 */
	protected function setUp() {
		parent::setUp();

		$this->request = $this->getMockBuilder('\OCP\IRequest')
							  ->disableOriginalConstructor()
							  ->getMock();
		$this->environment = $this->getMockBuilder('\OCA\Gallery\Environment\Environment')
								  ->disableOriginalConstructor()
								  ->getMock();
		$this->urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
								   ->disableOriginalConstructor()
								   ->getMock();
		$this->appConfig = $this->getMockBuilder('\OCP\IConfig')
								->disableOriginalConstructor()
								->getMock();
		$this->controller = new PageController(
			$this->appName,
			$this->request,
			$this->environment,
			$this->urlGenerator,
			$this->appConfig
		);
	}


	public function testIndex() {
		$params = ['appName' => $this->appName];
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

		$template = new TemplateResponse($this->appName, 'public', $params, 'public');

		$response = $this->controller->publicIndex($token, null);

		$this->assertEquals($params, $response->getParams());
		$this->assertEquals('public', $response->getTemplateName());
		$this->assertTrue($response instanceof TemplateResponse);
		$this->assertEquals($template->getStatus(), $response->getStatus());
	}

	public function testPublicIndexWithFileToken() {
		$token = 'aaaabbbbccccdddd';
		$filename = 'happy.jpg';
		$fileId = 12345;

		$this->mockGetSharedNode('file', $fileId);
		$redirectUrl = 'http://owncloud/download/' . $filename;
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

}
