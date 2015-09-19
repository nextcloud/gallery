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
namespace OCA\GalleryPlus\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\GalleryPlus\Environment\NotFoundEnvException;
use OCA\GalleryPlus\Service\NotFoundServiceException;
use OCA\GalleryPlus\Service\ForbiddenServiceException;
use OCA\GalleryPlus\Service\InternalServerErrorServiceException;

/**
 * Class HttpErrorTest
 *
 * @package OCA\GalleryPlus\Controller
 */
class HttpErrorTest extends \Test\TestCase {

	/** @var string */
	private $appName = 'galleryplus';

	/**
	 * @return array
	 */
	public function providesExceptionData() {
		$notFoundEnvMessage = 'Not found in env';
		$notFoundEnvException = new NotFoundEnvException($notFoundEnvMessage);
		$notFoundEnvStatus = Http::STATUS_NOT_FOUND;

		$notFoundServiceMessage = 'Not found in service';
		$notFoundServiceException = new NotFoundServiceException($notFoundServiceMessage);
		$notFoundServiceStatus = Http::STATUS_NOT_FOUND;

		$forbiddenServiceMessage = 'Forbidden in service';
		$forbiddenServiceException = new ForbiddenServiceException($forbiddenServiceMessage);
		$forbiddenServiceStatus = Http::STATUS_FORBIDDEN;

		$errorServiceMessage = 'Broken service';
		$errorServiceException = new InternalServerErrorServiceException($errorServiceMessage);
		$errorServiceStatus = Http::STATUS_INTERNAL_SERVER_ERROR;

		$coreServiceMessage = 'Broken core';
		$coreServiceException = new \Exception($coreServiceMessage);
		$coreServiceStatus = Http::STATUS_INTERNAL_SERVER_ERROR;

		return [
			[$notFoundEnvException, $notFoundEnvMessage, $notFoundEnvStatus],
			[$notFoundServiceException, $notFoundServiceMessage, $notFoundServiceStatus],
			[$forbiddenServiceException, $forbiddenServiceMessage, $forbiddenServiceStatus],
			[$errorServiceException, $errorServiceMessage, $errorServiceStatus],
			[$coreServiceException, $coreServiceMessage, $coreServiceStatus]
		];
	}

	/**
	 * @dataProvider providesExceptionData
	 *
	 * @param \Exception $exception
	 * @param String $message
	 * @param String $status
	 */
	public function testJsonError($exception, $message, $status) {
		$httpError = $this->getMockForTrait('\OCA\GalleryPlus\Controller\HttpError');
		/** @type JSONResponse $response */
		$response = $httpError->jsonError($exception);

		$this->assertEquals(
			['message' => $message . ' (' . $status . ')', 'success' => false], $response->getData()
		);
		$this->assertEquals($status, $response->getStatus());
	}

	/**
	 * @dataProvider providesExceptionData
	 *
	 * @param \Exception $exception
	 * @param String $message
	 * @param String $status
	 */
	public function testHtmlError($exception, $message, $status) {
		$session = $this->mockISession();
		$urlGenerator = $this->mockIURLGenerator();
		$this->mockSession($session, 'galleryErrorMessage', $message);
		$redirectUrl = '/index.php/app/error';
		$this->mockUrlToErrorPage($urlGenerator, $status, $redirectUrl);

		$httpError = $this->getMockForTrait('\OCA\GalleryPlus\Controller\HttpError');

		/** @type RedirectResponse $response */
		$response = $httpError->htmlError($session, $urlGenerator, $this->appName, $exception);
		$this->assertEquals($redirectUrl, $response->getRedirectURL());
		$this->assertEquals(Http::STATUS_TEMPORARY_REDIRECT, $response->getStatus());
		$this->assertEquals($message, $session->get('galleryErrorMessage'));
	}

	private function mockISession() {
		return $this->getMockBuilder('\OCP\ISession')
					->disableOriginalConstructor()
					->getMock();
	}

	private function mockIURLGenerator() {
		return $this->getMockBuilder('\OCP\IURLGenerator')
					->disableOriginalConstructor()
					->getMock();
	}

	/**
	 * Needs to be called at least once by testDownloadWithWrongId() or the tests will fail
	 *
	 * @param $session
	 * @param $key
	 * @param $value
	 */
	private function mockSession($session, $key, $value) {
		$session->expects($this->once())
				->method('set')
				->with($key)
				->willReturn($value);

		$session->expects($this->once())
				->method('get')
				->with($key)
				->willReturn($value);
	}

	/**
	 * Mocks IURLGenerator->linkToRoute()
	 *
	 * @param int $code
	 * @param string $url
	 */
	private function mockUrlToErrorPage($urlGenerator, $code, $url) {
		$urlGenerator->expects($this->once())
					 ->method('linkToRoute')
					 ->with($this->appName . '.page.error_page', ['code' => $code])
					 ->willReturn($url);
	}

}
