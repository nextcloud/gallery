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

	/**
	 * @dataProvider providesExceptionData
	 *
	 * @param \Exception $exception
	 * @param String $message
	 * @param String $status
	 */
	public function testError($exception, $message, $status) {
		$httpError = $this->getMockForTrait('\OCA\GalleryPlus\Controller\HttpError');
		$response = $httpError->jsonError($exception);

		$this->assertEquals(
			['message' => $message . ' (' . $status . ')', 'success' => false], $response->getData()
		);
		$this->assertEquals($status, $response->getStatus());
	}

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
}
