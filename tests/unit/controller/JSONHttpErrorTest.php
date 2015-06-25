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

/**
 * Class JsonHttpErrorTest
 *
 * @package OCA\Gallery\Controller
 */
class JsonHttpErrorTest extends \Test\TestCase {

	/**
	 * @dataProvider providesExceptionData
	 *
	 * @param \Exception $exception
	 * @param String $message
	 * @param String $status
	 */
	public function testError($exception, $message, $status) {
		$jsonHttpError = $this->getMockForTrait('OCA\Gallery\Controller\JsonHttpError');
		$response = $jsonHttpError->error($exception);

		$this->assertEquals(['message' => $message, 'success' => false], $response->getData());
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

		return [
			[$notFoundEnvException, $notFoundEnvMessage, $notFoundEnvStatus],
			[$notFoundServiceException, $notFoundServiceMessage, $notFoundServiceStatus],
			[$forbiddenServiceException, $forbiddenServiceMessage, $forbiddenServiceStatus],

		];
	}
}
