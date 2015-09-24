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

namespace OCA\Gallery\Service;

use OCP\Files\File;

use OCA\Gallery\Preview\Preview;

/**
 * Class PreviewServiceTest
 *
 * @package OCA\Gallery\Controller
 */
class PreviewServiceTest extends \Test\GalleryUnitTest {

	use Base64Encode;

	/** @var PreviewService */
	protected $service;
	/** @var Preview */
	protected $previewManager;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$this->previewManager = $this->getMockBuilder('\OCA\Gallery\Preview\Preview')
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
		$this->mockGetUserIdFails();

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
