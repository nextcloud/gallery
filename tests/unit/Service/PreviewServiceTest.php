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

namespace OCA\Gallery\Tests\Service;

use OCP\Files\File;
use OCP\IPreview;

use OCA\Gallery\Service\Base64Encode;
use OCA\Gallery\Service\PreviewService;

/**
 * Class PreviewServiceTest
 *
 * @package OCA\Gallery\Tests\Service
 */
class PreviewServiceTest extends \OCA\Gallery\Tests\GalleryUnitTest {

	use Base64Encode;

	/** @var PreviewService */
	protected $service;
	/** @var IPreview|\PHPUnit_Framework_MockObject_MockObject */
	protected $previewManager;

	/**
	 * Test set up
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->previewManager = $this->getMockBuilder('\OCP\IPreview')
									 ->disableOriginalConstructor()
									 ->getMock();

		$this->service = new PreviewService (
			$this->appName,
			$this->environment,
			$this->previewManager,
			$this->logger
		);
	}

	public function providesIsPreviewRequiredData() {
		return [
			[true],
			[false],
		];
	}

	/**
	 * @dataProvider providesIsPreviewRequiredData
	 *
	 * @param bool $isMimeSupported
	 */
	public function testIsPreviewRequiredWithSvg($isMimeSupported) {
		/** @type File $file */
		$file = $this->mockSvgFile(12345);
		$animatedPreview = true; // Has no effect

		$this->mockIsMimeSupported($isMimeSupported);

		$response = $this->service->isPreviewRequired($file, $animatedPreview);

		$this->assertSame($isMimeSupported, $response);

	}

	public function providesIsPreviewRequiredWithAnimatedGifData() {
		return [
			[true, true, false],
			[true, false, true],
			[false, true, false],
			[false, false, false]
		];
	}

	/**
	 * @dataProvider providesIsPreviewRequiredWithAnimatedGifData
	 *
	 * @param bool $isMimeSupported
	 * @param bool $animatedPreview
	 * @param bool $expected
	 */
	public function testIsPreviewRequiredWithAnimatedGif(
		$isMimeSupported, $animatedPreview, $expected
	) {
		/** @type File $file */
		$file = $this->mockAnimatedGifFile(12345);

		$this->mockIsMimeSupported($isMimeSupported);

		$response = $this->service->isPreviewRequired($file, $animatedPreview);

		$this->assertSame($expected, $response);
	}

	public function testIsPreviewRequiredWithBrokenSystem() {
		/** @type File $file */
		$file = $this->mockAnimatedGifFile(12345);
		$animatedPreview = false; // Should require a preview

		$this->mockIsMimeSupportedWithBrokenSystem('image/gif');

		$response = $this->service->isPreviewRequired($file, $animatedPreview);

		$this->assertFalse($response);
	}

	/**
	 * @expectedException \OCA\Gallery\Service\InternalServerErrorServiceException
	 */
	public function testIsPreviewRequiredWithBrokenGif() {
		/** @type File $file */
		$file = $this->mockAnimatedGifFile(12345);
		$file = $this->mockBrokenAnimatedGifFileMethods($file);
		$animatedPreview = false; // Should require a preview
		$this->mockIsMimeSupported(true);

		$this->service->isPreviewRequired($file, $animatedPreview);
	}

	/**
	 * @expectedException \OCA\Gallery\Service\InternalServerErrorServiceException
	 */
	public function testCreatePreviewWithBrokenSystem() {
		/** @type File $file */
		$file = $this->mockJpgFile(12345);

		$this->previewManager->method('getPreview')
			->willThrowException(new \Exception('BOOM'));

		$this->service->createPreview(
			$file, $maxX = 0, $maxY = 0, $keepAspect = true, $base64Encode = false
		);
	}

	private function mockIsMimeSupported($mimeSupported) {
		$map = [
			['image/jpeg', true],
			['application/font-sfnt', true],
			['application/x-font', true],
			['image/svg+xml', $mimeSupported],
			['image/gif', $mimeSupported]
		];
		$this->previewManager->method('isMimeSupported')
							 ->will(
								 $this->returnValueMap($map)
							 );
	}

	private function mockIsMimeSupportedWithBrokenSystem($mimeType) {
		$this->previewManager->expects($this->once())
							 ->method('isMimeSupported')
							 ->with($mimeType)
							 ->willThrowException(new \Exception('Boom'));
	}

	private function mockBrokenAnimatedGifFileMethods($file) {
		$file->method('getMimeType')
			 ->willReturn('image/gif');
		$file->method('fopen')
			 ->with('rb')
			 ->willThrowException(new \Exception('Boom'));

		return $file;
	}

	private function mockGetUserIdFails() {
		$this->environment->expects($this->once())
						  ->method('getUserId')
						  ->willThrowException(new \Exception('Boom'));
	}
}
