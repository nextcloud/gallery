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

	public function providesGetSupportedMediaTypesData() {
		$baseMimeTypes = [
			'image/jpeg',
		];

		$slideshowMimes = array_merge(
			$baseMimeTypes,
			[
				'application/font-sfnt',
				'application/x-font',
			]
		);

		$baseMimeTypesWithSvg = array_merge(
			$baseMimeTypes,
			[
				'image/svg+xml',
			]
		);

		$slideshowMimesWithSvg = array_merge(
			$slideshowMimes,
			[
				'image/svg+xml',
			]
		);

		return [
			[$baseMimeTypes, false, false, $baseMimeTypes],
			[$baseMimeTypes, false, true, $baseMimeTypesWithSvg],
			[$baseMimeTypes, true, true, $slideshowMimesWithSvg],
			[$baseMimeTypes, true, false, $slideshowMimes],
		];
	}

	/**
	 * @dataProvider providesGetSupportedMediaTypesData
	 *
	 * @param $baseMimeTypes
	 * @param $extraMediaTypes
	 * @param $nativeSvgSupport
	 * @param $expectedResult
	 */
	public function testGetSupportedMediaTypes(
		$baseMimeTypes, $extraMediaTypes, $nativeSvgSupport, $expectedResult
	) {

		$this->assertSame(
			$baseMimeTypes, self::invokePrivate($this->service, 'baseMimeTypes', [$baseMimeTypes])
		);

		$this->mockIsMimeSupported($nativeSvgSupport);

		$response = $this->service->getSupportedMediaTypes($extraMediaTypes, $nativeSvgSupport);

		$this->assertSame($expectedResult, array_keys($response));
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

	public function providesPreviewValidatorData() {
		return [
			[true, true],
			[true, false],
			[false, true],
			[false, false]
		];
	}

	/**
	 * @dataProvider providesPreviewValidatorData
	 *
	 * @param bool $square
	 * @param bool $base64Encode
	 */
	public function testPreviewValidator($square, $base64Encode) {
		$file = $this->mockJpgFile(1234);
		$this->mockPreviewValidator($file);
		$preview = $file->getContent();

		if ($base64Encode) {
			$preview = base64_encode($preview);
		}

		$response = $this->service->previewValidator($square, $base64Encode);

		$this->assertSame($preview, $response);
	}

	/**
	 * @expectedException \OCA\Gallery\Service\InternalServerErrorServiceException
	 */
	public function testPreviewValidatorWithBrokenSetup() {
		$square = true;
		$base64Encode = true;
		$this->mockPreviewValidatorWithBrokenSystem();

		$this->service->previewValidator($square, $base64Encode);
	}


	public function providesAddSvgSupportData() {
		$supportedMimes = [
			'image/png',
			'image/jpeg',
			'image/gif'
		];

		$supportedMimesWithSvg = array_merge(
			$supportedMimes,
			[
				// The method returns the path, but only checks for the key
				'image/svg+xml' => '/core/img/filetypes/image.png',
			]
		);

		return [
			[$supportedMimes, true, $supportedMimesWithSvg],
			[$supportedMimes, false, $supportedMimes],
			[$supportedMimesWithSvg, true, $supportedMimesWithSvg],
			[$supportedMimesWithSvg, false, $supportedMimesWithSvg],
		];
	}

	/**
	 * @dataProvider providesAddSvgSupportData
	 *
	 * @param array $supportedMimes
	 * @param bool $nativeSvgSupport
	 * @param array $expectedResult
	 */
	public function testAddSvgSupport($supportedMimes, $nativeSvgSupport, $expectedResult) {
		$response = self::invokePrivate(
			$this->service, 'addSvgSupport', [$supportedMimes, $nativeSvgSupport]
		);

		$this->assertSame($expectedResult, $response);
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

	private function mockPreviewValidator($preview) {
		$this->previewManager->expects($this->once())
							 ->method('previewValidator')
							 ->with($this->anything())
							 ->willReturn($preview->getContent());
	}

	private function mockPreviewValidatorWithBrokenSystem() {
		$this->previewManager->expects($this->once())
							 ->method('previewValidator')
							 ->with($this->anything())
							 ->willThrowException(new \Exception('Boom'));
	}

	private function mockGetUserIdFails() {
		$this->environment->expects($this->once())
						  ->method('getUserId')
						  ->willThrowException(new \Exception('Boom'));
	}
}
