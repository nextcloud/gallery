<?php
/**
 * Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2016
 */

namespace OCA\Gallery\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\Gallery\Environment\NotFoundEnvException;
use OCA\Gallery\Service\NotFoundServiceException;
use OCA\Gallery\Service\ForbiddenServiceException;
use OCA\Gallery\Service\InternalServerErrorServiceException;
use OCP\ILogger;
use OCP\IRequest;

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
		$notFoundEnvMessage = 'An error occurred. Request ID: 1234';
		$notFoundEnvException = new NotFoundEnvException($notFoundEnvMessage);
		$notFoundEnvStatus = Http::STATUS_NOT_FOUND;

		$notFoundServiceMessage = 'An error occurred. Request ID: 1234';
		$notFoundServiceException = new NotFoundServiceException($notFoundServiceMessage);
		$notFoundServiceStatus = Http::STATUS_NOT_FOUND;

		$forbiddenServiceMessage = 'Forbidden in service';
		$forbiddenServiceException = new ForbiddenServiceException($forbiddenServiceMessage);
		$forbiddenServiceStatus = Http::STATUS_FORBIDDEN;

		$errorServiceMessage = 'An error occurred. Request ID: 1234';
		$errorServiceException = new InternalServerErrorServiceException($errorServiceMessage);
		$errorServiceStatus = Http::STATUS_INTERNAL_SERVER_ERROR;

		$coreServiceMessage = 'An error occurred. Request ID: 1234';
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
		$request = $this->createMock(IRequest::class);
		$logger = $this->createMock(ILogger::class);

		if($exception instanceof ForbiddenServiceException) {
			$amount = 0;
			$message = $message . ' (' . $status . ')';
		} else {
			$amount = 1;
		}

		$logger
			->expects($this->exactly($amount))
			->method('logException')
			->with($exception, ['app' => 'gallery']);
		$request
			->expects($this->exactly($amount))
			->method('getId')
			->willReturn('1234');

		/** @var HttpError $httpError */
		$httpError = $this->getMockForTrait(HttpError::class);
		/** @type JSONResponse $response */
		$response = $httpError->jsonError($exception, $request, $logger);

		$this->assertSame(
			['message' => $message, 'success' => false], $response->getData()
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
		$this->assertEquals(Http::STATUS_SEE_OTHER, $response->getStatus());
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
