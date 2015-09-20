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

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ILogger;
use OCP\Files\File;

use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

use OCA\Gallery\AppInfo\Application;
use OCA\Gallery\Http\ImageResponse;
use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\ThumbnailService;
use OCA\Gallery\Service\PreviewService;
use OCA\Gallery\Service\DownloadService;
use OCA\Gallery\Utility\EventSource;
use OCA\Gallery\Service\NotFoundServiceException;
use OCA\Gallery\Service\InternalServerErrorServiceException;

/**
 * Class PreviewControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class PreviewControllerTest extends \Test\GalleryUnitTest {

	/** @var IAppContainer */
	protected $container;
	/** @var string */
	protected $appName = 'gallery';
	/** @var IRequest */
	protected $request;
	/** @var PreviewController */
	protected $controller;
	/** @var IURLGenerator */
	protected $urlGenerator;
	/** @var ConfigService */
	protected $configService;
	/** @var ThumbnailService */
	protected $thumbnailService;
	/** @var PreviewService */
	protected $previewService;
	/** @var DownloadService */
	protected $downloadService;
	/** @var EventSource */
	protected $eventSource;
	/** @var ILogger */
	protected $logger;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$app = new Application;
		$this->container = $app->getContainer();
		$this->container['UserFolder'] = $this->getMockBuilder('OCP\Files\Folder')
											  ->disableOriginalConstructor()
											  ->getMock();
		$this->request = $this->getMockBuilder('\OCP\IRequest')
							  ->disableOriginalConstructor()
							  ->getMock();
		$this->urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
								   ->disableOriginalConstructor()
								   ->getMock();
		$this->configService = $this->getMockBuilder('\OCA\Gallery\Service\ConfigService')
									->disableOriginalConstructor()
									->getMock();
		$this->thumbnailService = $this->getMockBuilder('\OCA\Gallery\Service\ThumbnailService')
									   ->disableOriginalConstructor()
									   ->getMock();
		$this->previewService = $this->getMockBuilder('\OCA\Gallery\Service\PreviewService')
									 ->disableOriginalConstructor()
									 ->getMock();
		$this->downloadService = $this->getMockBuilder('\OCA\Gallery\Service\DownloadService')
									  ->disableOriginalConstructor()
									  ->getMock();
		$this->eventSource = $this->getMockBuilder('\OCA\Gallery\Utility\EventSource')
								  ->disableOriginalConstructor()
								  ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->controller = new PreviewController(
			$this->appName,
			$this->request,
			$this->urlGenerator,
			$this->configService,
			$this->thumbnailService,
			$this->previewService,
			$this->downloadService,
			$this->eventSource,
			$this->logger
		);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testGetThumbnailsWillDie() {
		$square = true;
		$scale = 2.5;
		$thumbnailId = 1234;

		$file = $this->mockJpgFile($thumbnailId);
		$this->mockGetResourceFromId($this->previewService, $thumbnailId, $file);

		$this->controller->getThumbnails($thumbnailId, $square, $scale);
	}

	public function testEventSourceSend() {
		$thumbnailId = 1234;
		$file = $this->mockFile($thumbnailId);
		$preview = $this->mockBase64PreviewData($file);

		$event = 'preview';
		$message = 'event: ' . $event . PHP_EOL .
				   'data: ' . json_encode($preview) . PHP_EOL .
				   PHP_EOL;
		$this->mockEventSourceSend($event, $preview, $message);

		$this->assertSame($message, $this->eventSource->send('preview', $preview));
	}

	/**
	 * @todo Really base64 encode the preview instead of just passing around the binary content
	 */
	public function testGetThumbnail() {
		$square = true;
		$scale = 2.5;
		$width = 400;
		$height = 400;
		$aspect = !$square;
		$animatedPreview = false;
		$base64Encode = true;
		$thumbnailId = 1234;
		$thumbnailSpecs = [
			$width,
			$height,
			$aspect,
			$animatedPreview,
			$base64Encode
		];
		$this->mockGetThumbnailSpecs($square, $scale, $thumbnailSpecs);
		/** @type File $file */
		$file = $this->mockJpgFile($thumbnailId);
		$mockedPreview =
			$this->mockGetData(
				$thumbnailId, $file, $width, $height, $aspect, $animatedPreview, $base64Encode
			);

		$this->mockPreviewValidator($square, $base64Encode, $mockedPreview['preview']);

		list($preview, $status) = self::invokePrivate(
			$this->controller, 'getThumbnail', [$thumbnailId, $square, $scale]
		);

		$this->assertEquals(Http::STATUS_OK, $status);
		$this->assertEquals($mockedPreview, $preview);
	}

	public function testGetBrokenThumbnail() {
		$square = true;
		$scale = 2.5;
		$width = 400;
		$height = 400;
		$aspect = !$square;
		$animatedPreview = false;
		$base64Encode = true;
		$thumbnailId = 1234;
		$thumbnailSpecs = [
			$width,
			$height,
			$aspect,
			$animatedPreview,
			$base64Encode
		];
		$this->mockGetThumbnailSpecs($square, $scale, $thumbnailSpecs);

		/** @type File $file */
		$file = $this->mockJpgFile($thumbnailId);
		$this->mockGetDataWithBrokenPreview(
			$thumbnailId, $file, $width, $height, $aspect, $animatedPreview, $base64Encode
		);

		list($preview, $status) = self::invokePrivate(
			$this->controller, 'getThumbnail', [$thumbnailId, $square, $scale]
		);

		$this->assertEquals(Http::STATUS_INTERNAL_SERVER_ERROR, $status);
		$this->assertNull($preview['preview']);
		$this->assertEquals($file->getMimeType(), $preview['mimetype']);
	}

	public function testGetThumbnailWithBrokenSetup() {
		$square = true;
		$scale = 2.5;
		$thumbnailId = 1234;
		$animatedPreview = false;

		/** @type File $file */
		$file = $this->mockGetDataWithBrokenSetup($thumbnailId, $animatedPreview);
		$this->mockIsPreviewRequiredThrowsException($file, $animatedPreview);

		list($preview, $status) = self::invokePrivate(
			$this->controller, 'getThumbnail', [$thumbnailId, $square, $scale]
		);

		$this->assertEquals(Http::STATUS_INTERNAL_SERVER_ERROR, $status);
		$this->assertNull($preview['preview']);
		$this->assertEquals($file->getMimeType(), $preview['mimetype']);
	}

	public function testGetPreview() {
		$fileId = 1234;
		$width = 1024;
		$height = 768;

		/** @type File $file */
		$file = $this->mockJpgFile($fileId);
		$preview = $this->mockGetData($fileId, $file, $width, $height);
		$preview['name'] = $file->getName();

		/** @type ImageResponse $response */
		$response = $this->controller->getPreview($fileId, $width, $height);

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());

		// Media type of a JPG preview is always PNG
		$this->assertEquals(
			'image/png; charset=utf-8', $response->getHeaders()['Content-type']
		);
	}

	public function testGetPreviewWithWrongId() {
		$fileId = 99999;
		$width = 1024;
		$height = 768;

		$exception = new NotFoundServiceException('Not found');
		$this->mockGetResourceFromIdWithBadFile($this->previewService, $fileId, $exception);

		$errorResponse = $this->jsonErrorMessage(Http::STATUS_NOT_FOUND);

		$response = $this->controller->getPreview($fileId, $width, $height);

		$this->assertEquals($errorResponse->getStatus(), $response->getStatus());
		$this->assertEquals($errorResponse->getData()['success'], $response->getData()['success']);
	}

	public function testGetPreviewWithBrokenGif() {
		$fileId = 1234;
		$width = 1024;
		$height = 768;

		/** @type File $file */
		$file = $this->mockAnimatedGifFile($fileId);
		$this->mockGetDataWithEmptyPreview($fileId, $file, $width, $height);

		$errorResponse = $this->jsonErrorMessage(Http::STATUS_INTERNAL_SERVER_ERROR);

		$response = $this->controller->getPreview($fileId, $width, $height);

		$this->assertEquals($errorResponse->getStatus(), $response->getStatus());
		$this->assertEquals($errorResponse->getData()['success'], $response->getData()['success']);
	}

	/**
	 * Mocks Preview->getData
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param File $file
	 * @param int $width
	 * @param int $height
	 * @param bool $keepAspect
	 * @param bool $animatedPreview
	 * @param bool $base64Encode
	 * @param bool $previewRequired
	 *
	 * @return array
	 */
	protected function mockGetData(
		$fileId, $file, $width, $height, $keepAspect = true, $animatedPreview = true,
		$base64Encode = false, $previewRequired = true
	) {
		$this->mockGetResourceFromId($this->previewService, $fileId, $file);

		$this->mockIsPreviewRequired($file, $animatedPreview, $previewRequired);
		$previewData = $this->mockPreviewData($file, $previewRequired);

		if ($previewRequired) {
			$this->mockCreatePreview(
				$file, $width, $height, $keepAspect, $base64Encode, $previewData
			);
		} else {
			$this->mockDownloadFile($file, $base64Encode, $previewData);
		}

		return $previewData;
	}

	/**
	 * Mocks Preview->getData
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param File $file
	 * @param int $width
	 * @param int $height
	 * @param bool $keepAspect
	 * @param bool $animatedPreview
	 * @param bool $base64Encode
	 *
	 * @return array
	 */
	private function mockGetDataWithBrokenPreview(
		$fileId, $file, $width, $height, $keepAspect = true, $animatedPreview = true,
		$base64Encode = false
	) {
		$this->mockGetResourceFromId($this->previewService, $fileId, $file);

		$this->mockIsPreviewRequired($file, $animatedPreview, true);

		$this->mockCreatePreviewThrowsException($file, $width, $height, $keepAspect, $base64Encode);
	}

	/**
	 * Mocks Preview->getData
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param File $file
	 * @param int $width
	 * @param int $height
	 * @param bool $keepAspect
	 * @param bool $animatedPreview
	 * @param bool $base64Encode
	 *
	 * @return array
	 */
	private function mockGetDataWithEmptyPreview(
		$fileId, $file, $width, $height, $keepAspect = true, $animatedPreview = true,
		$base64Encode = false
	) {
		$this->mockGetResourceFromId($this->previewService, $fileId, $file);

		$this->mockIsPreviewRequired($file, $animatedPreview, true);

		$this->mockCreatePreview($file, $width, $height, $keepAspect, $base64Encode, null);
	}

	/**
	 * @param int $fileId
	 * @param bool $animatedPreview
	 *
	 * @return object|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockGetDataWithBrokenSetup($fileId, $animatedPreview) {
		$file = $this->mockJpgFile($fileId);
		$this->mockGetResourceFromId($this->previewService, $fileId, $file);

		$this->mockIsPreviewRequiredThrowsException($file, $animatedPreview);

		return $file;
	}


	/**
	 * @param object|\PHPUnit_Framework_MockObject_MockObject $file
	 * @param bool $animatedPreview
	 * @param bool $response
	 */
	private function mockIsPreviewRequired($file, $animatedPreview, $response) {
		$this->previewService->expects($this->once())
							 ->method('isPreviewRequired')
							 ->with(
								 $this->equalTo($file),
								 $this->equalTo($animatedPreview)
							 )
							 ->willReturn($response);
	}

	/**
	 * @param object|\PHPUnit_Framework_MockObject_MockObject $file
	 * @param bool $animatedPreview
	 */
	private function mockIsPreviewRequiredThrowsException($file, $animatedPreview) {
		$exception = new InternalServerErrorServiceException('Broken');
		$this->previewService->expects($this->once())
							 ->method('isPreviewRequired')
							 ->with(
								 $this->equalTo($file),
								 $this->equalTo($animatedPreview)
							 )
							 ->willThrowException($exception);
	}

	/**
	 * @param File $file
	 * @param bool $previewRequired
	 *
	 * @return array <string,mixed>
	 */
	private function mockPreviewData($file, $previewRequired) {
		$mimeType = $previewRequired ? 'image/png' : $file->getMimeType();

		$preview = [
			'preview'  => $file->getContent(), // Not a real preview, but it's not important
			'mimetype' => $mimeType,
		];

		return $preview;
	}

	/**
	 * @param object|\PHPUnit_Framework_MockObject_MockObject $file
	 *
	 * @return array<string,mixed>
	 */
	private function mockBase64PreviewData($file) {
		$preview = [
			'preview'  => base64_encode($file->getContent()),
			// Not a real preview, but it's not important
			'mimetype' => 'image/png', //Most previews are PNGs
		];

		return $preview;
	}

	/**
	 * @param File $file
	 * @param int $width
	 * @param int $height
	 * @param bool $keepAspect
	 * @param bool $base64Encode
	 * @param array $preview
	 */
	private function mockCreatePreview($file, $width, $height, $keepAspect, $base64Encode, $preview
	) {
		$this->previewService->expects($this->once())
							 ->method('createPreview')
							 ->with(
								 $this->equalTo($file),
								 $this->equalTo($width),
								 $this->equalTo($height),
								 $this->equalTo($keepAspect),
								 $this->equalTo($base64Encode)
							 )
							 ->willReturn($preview);
	}

	/**
	 * @param File $file
	 * @param int $width
	 * @param int $height
	 * @param bool $keepAspect
	 * @param bool $base64Encode
	 */
	private function mockCreatePreviewThrowsException(
		$file, $width, $height, $keepAspect, $base64Encode
	) {
		$exception = new InternalServerErrorServiceException('Encryption ate your file');
		$this->previewService->expects($this->once())
							 ->method('createPreview')
							 ->with(
								 $this->equalTo($file),
								 $this->equalTo($width),
								 $this->equalTo($height),
								 $this->equalTo($keepAspect),
								 $this->equalTo($base64Encode)
							 )
							 ->willthrowException($exception);
	}

	/**
	 * @param $file
	 * @param $base64Encode
	 * @param $preview
	 */
	private function mockDownloadFile($file, $base64Encode, $preview) {
		$this->downloadService->expects($this->once())
							  ->method('downloadFile')
							  ->with(
								  $this->equalTo($file),
								  $this->equalTo($base64Encode)
							  )
							  ->willReturn($preview);
	}

	/**
	 * @param $event
	 * @param $data
	 * @param $message
	 */
	private function mockEventSourceSend($event, $data, $message) {
		$this->eventSource->expects($this->once())
						  ->method('send')
						  ->with(
							  $event,
							  $data
						  )
						  ->willReturn($message);

	}

	/**
	 * @param $square
	 * @param $scale
	 * @param $array
	 */
	private function mockGetThumbnailSpecs($square, $scale, $array) {
		$this->thumbnailService->expects($this->once())
							   ->method('getThumbnailSpecs')
							   ->with(
								   $square,
								   $scale
							   )
							   ->willReturn($array);

	}

	/**
	 * @param $square
	 * @param $base64Encode
	 * @param $base64EncodedPreview
	 */
	private function mockPreviewValidator($square, $base64Encode, $base64EncodedPreview) {
		$this->previewService->expects($this->once())
							 ->method('previewValidator')
							 ->with(
								 $square,
								 $base64Encode
							 )
							 ->willReturn($base64EncodedPreview);

	}

	private function jsonErrorMessage($code) {
		return new JSONResponse(
			[
				'message' => "I'm truly sorry, but we were unable to generate a preview for this file",
				'success' => false
			], $code
		);
	}

}
