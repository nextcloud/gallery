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
include_once 'ServiceTest.php';

use OCP\Files\File;

use OCA\Gallery\Preview\Preview;

/**
 * Class PreviewServiceTest
 *
 * @package OCA\Gallery\Controller
 */
class PreviewServiceTest extends ServiceTest {

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
	 * @param $isMimeSupported
	 */
	public function testIsPreviewRequiredWithSvg($isMimeSupported) {
		/** @type File $file */
		$file = $this->mockSvgFile(12345);
		$animatedPreview = true; // Has no effect

		$this->mockIsMimeSupported($isMimeSupported);

		$response = $this->service->isPreviewRequired($file, $animatedPreview);

		$this->assertSame($isMimeSupported, $response);

	}

	public function testIsMimeSupportedWithBrokenSystem() {
		$mimeType = 'secret/mime';
		$this->mockIsMimeSupportedWithBrokenSystem($mimeType);

		$response = self::invokePrivate($this->service, 'isMimeSupported', [$mimeType]);

		$this->assertFalse($response);
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
				'image/svg+xml' => '/core/img/filetypes/image-vector.png',
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


	private function mockIsMimeSupported($svgSupport) {
		$map = [
			['image/jpeg', true],
			['application/font-sfnt', true],
			['application/x-font', true],
			['image/svg+xml', $svgSupport]
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

}
