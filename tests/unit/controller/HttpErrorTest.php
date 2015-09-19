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

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\Gallery\Environment\NotFoundEnvException;
use OCA\Gallery\Service\NotFoundServiceException;
use OCA\Gallery\Service\ForbiddenServiceException;
use OCA\Gallery\Service\InternalServerErrorServiceException;

/**
 * Class HttpErrorTest
 *
 * @package OCA\Gallery\Controller
 */
class HttpErrorTest extends \Test\TestCase {

	/** @var string */
	private $appName = 'gallery';

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
		$httpError = $this->getMockForTrait('\OCA\Gallery\Controller\HttpError');
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
		$urlGenerator = $this->mockIURLGenerator();
		$redirectUrl = '/index.php/app/error';
		$this->mockUrlToErrorPage($urlGenerator, $status, $redirectUrl);

		$httpError = $this->getMockForTrait('\OCA\Gallery\Controller\HttpError');

		/** @type RedirectResponse $response */
		$response = $httpError->htmlError($urlGenerator, $this->appName, $exception);
		$this->assertEquals($redirectUrl, $response->getRedirectURL());
		$this->assertEquals(Http::STATUS_TEMPORARY_REDIRECT, $response->getStatus());
		$this->assertEquals($message, $response->getCookies()['galleryErrorMessage']['value']);
	}

	private function mockIURLGenerator() {
		return $this->getMockBuilder('\OCP\IURLGenerator')
					->disableOriginalConstructor()
					->getMock();
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
