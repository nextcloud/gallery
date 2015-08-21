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

use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

use OCA\Gallery\AppInfo\Application;
use OCA\Gallery\Http\ImageResponse;
use OCA\Gallery\Service\ThumbnailService;
use OCA\Gallery\Service\PreviewService;
use OCA\Gallery\Service\DownloadService;
use OCA\Gallery\Service\ServiceException;
use OCA\Gallery\Utility\EventSource;

/**
 * Class PreviewControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class PreviewControllerTest extends \Test\TestCase {

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
		$returnedArray = [
			$width,
			$height,
			$aspect,
			$animatedPreview,
			$base64Encode
		];
		$this->mockGetThumbnailSpecs($square, $scale, $returnedArray);

		list($file, $mockedPreview) =
			$this->mockGetData(
				$thumbnailId, $width, $height, $aspect, $animatedPreview, $base64Encode
			);

		$this->mockPreviewValidator($square, $base64Encode, $mockedPreview['preview']);

		list($preview, $status) = self::invokePrivate(
			$this->controller, 'getThumbnail', [$thumbnailId, $square, $scale]
		);

		$this->assertEquals(Http::STATUS_OK, $status);
		$this->assertEquals($mockedPreview, $preview);
	}

	public function testGetPreview() {
		$fileId = 1234;
		$width = 1024;
		$height = 768;

		list($file, $preview) = $this->mockGetData($fileId, $width, $height);
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

		$this->mockGetResourceFromId($fileId, false);

		$errorResponse = new JSONResponse(
			[
				'message' => "I'm truly sorry, but we were unable to generate a preview for this file",
				'success' => false
			], Http::STATUS_INTERNAL_SERVER_ERROR
		);

		$response = $this->controller->getPreview($fileId, $width, $height);

		$this->assertEquals($errorResponse->getStatus(), $response->getStatus());
		$this->assertEquals($errorResponse->getData()['success'], $response->getData()['success']);
	}

	/**
	 * Mocks Preview->getData
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param int $width
	 * @param int $height
	 * @param bool $keepAspect
	 * @param bool $animatedPreview
	 * @param bool $base64Encode
	 *
	 * @return array
	 */
	private function mockGetData(
		$fileId, $width, $height, $keepAspect = true, $animatedPreview = true, $base64Encode = false
	) {

		$file = $this->mockFile($fileId);

		$this->mockGetResourceFromId($fileId, $file);

		$this->mockIsPreviewRequired($file, $animatedPreview, true);

		$preview = $this->mockPreviewData($file);

		$this->mockCreatePreview($file, $width, $height, $keepAspect, $base64Encode, $preview);

		return [$file, $preview];
	}

	/**
	 * Mocks OCP\Files\File
	 *
	 * Contains a JPG
	 *
	 * @param $fileId
	 *
	 * @return object|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockFile($fileId) {
		$file = $this->getMockBuilder('OCP\Files\File')
					 ->disableOriginalConstructor()
					 ->getMock();
		$file->method('getId')
			 ->willReturn($fileId);
		$file->method('getContent')
			 ->willReturn(file_get_contents(\OC::$SERVERROOT . '/tests/data/testimage.jpg'));
		$file->method('getName')
			 ->willReturn('testimage.jpg');
		$file->method('getMimeType')
			 ->willReturn('image/jpeg');

		return $file;
	}

	/**
	 * Mocks PreviewService->getResourceFromId
	 * return $file;
	 *
	 * @param int $fileId
	 * @param object|\PHPUnit_Framework_MockObject_MockObject|bool $answer
	 */
	private function mockGetResourceFromId($fileId, $answer) {
		$this->previewService->expects($this->once())
							 ->method('getResourceFromId')
							 ->with($this->equalTo($fileId))
							 ->willReturn($answer);
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
	 *
	 * @return array<string,mixed>
	 */
	private function mockPreviewData($file) {
		$preview = [
			'preview'  => $file->getContent(), // Not a real preview, but it's not important
			'mimetype' => 'image/png', //Most previews are PNGs
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
	 * @param $file
	 * @param $width
	 * @param $height
	 * @param $keepAspect
	 * @param $base64Encode
	 * @param $preview
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

	private function mockEventSourceSend($event, $data, $message) {
		$this->eventSource->expects($this->once())
						  ->method('send')
						  ->with(
							  $event,
							  $data
						  )
						  ->willReturn($message);

	}

	private function mockGetThumbnailSpecs($square, $scale, $array) {
		$this->thumbnailService->expects($this->once())
							   ->method('getThumbnailSpecs')
							   ->with(
								   $square,
								   $scale
							   )
							   ->willReturn($array);

	}

	private function mockPreviewValidator($square, $base64Encode, $base64EncodedPreview) {
		$this->previewService->expects($this->once())
							 ->method('previewValidator')
							 ->with(
								 $square,
								 $base64Encode
							 )
							 ->willReturn($base64EncodedPreview);

	}

}
