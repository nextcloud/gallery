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

use OCA\Gallery\Config\ConfigParser;
use OCA\Gallery\Config\ConfigException;

use OCA\Gallery\Preview\Preview;

/**
 * Class ConfigServiceTest
 *
 * @package OCA\Gallery\Controller
 */
class ConfigServiceTest extends \Test\GalleryUnitTest {

	/** @var ConfigService */
	protected $service;
	/** @var ConfigParser */
	protected $configParser;
	/** @var Preview */
	protected $previewManager;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$this->configParser = $this->getMockBuilder('\OCA\Gallery\Config\ConfigParser')
								   ->disableOriginalConstructor()
								   ->getMock();
		$this->previewManager = $this->getMockBuilder('\OCA\Gallery\Preview\Preview')
									 ->disableOriginalConstructor()
									 ->getMock();
		$this->service = new ConfigService (
			$this->appName,
			$this->environment,
			$this->configParser,
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

		$this->assertSame($expectedResult, $response);
	}

	public function testGetSupportedMediaTypesWithBrokenPreviewSystem() {
		// We only support 1 media type: GIF
		self::invokePrivate($this->service, 'baseMimeTypes', [['image/gif']]);

		// Unfortunately, the GIF preview is broken
		$this->mockIsMimeSupportedWithBrokenSystem('image/gif');

		$response = $this->service->getSupportedMediaTypes(false, false);

		// 1-1 = 0
		$this->assertEmpty($response);
	}

	public function providesValidateMimeTypeData() {
		return [
			['image/png'],
			['image/jpeg'],
			['image/gif'],
			['application/postscript'],
			['application/x-font']
		];
	}

	/**
	 * @dataProvider providesValidateMimeTypeData
	 *
	 * @param $mimeType
	 *
	 */
	public function testValidateMimeType($mimeType) {
		$supportedMimeTypes = [
			'image/png',
			'image/jpeg',
			'image/gif',
			'application/postscript',
			'application/x-font'
		];

		$this->assertSame(
			$supportedMimeTypes,
			self::invokePrivate($this->service, 'baseMimeTypes', [$supportedMimeTypes])
		);
		$this->mockIsMimeSupported($nativeSvgSupport = true);

		$this->service->validateMimeType($mimeType);
	}

	public function providesValidateMimeTypeWithForbiddenMimeData() {
		return [
			['text/plain'],
			['application/javascript'],
			['application/json'],
			['text/markdown'],
			['application/yaml'],
			['application/xml'],
		];
	}

	/**
	 * @dataProvider providesValidateMimeTypeWithForbiddenMimeData
	 *
	 * @param $mimeType
	 *
	 * @expectedException \OCA\Gallery\Service\ForbiddenServiceException
	 */
	public function testValidateMimeTypeWithForbiddenMime($mimeType) {
		$supportedMimeTypes = [
			'image/png',
			'image/jpeg',
			'image/gif',
			'image/x-xbitmap',
			'image/bmp',
			'application/postscript',
			'application/x-font'
		];

		$this->assertSame(
			$supportedMimeTypes,
			self::invokePrivate($this->service, 'baseMimeTypes', [$supportedMimeTypes])
		);
		$this->mockIsMimeSupported($nativeSvgSupport = true);

		$this->service->validateMimeType($mimeType);
	}

	public function providesAddSvgSupportData() {
		$supportedMimes = [
			'image/png',
			'image/jpeg',
			'image/gif'
		];

		$supportedMimesWithSvg = array_merge($supportedMimes, ['image/svg+xml']);

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

	public function testBuildFolderConfigWithBrokenSetup() {
		$nodeId = 65965;
		$files = [];
		$folder = $this->mockFolder('home::user', $nodeId, $files);
		$configName = 'gallery.cnf';
		$config = [];
		// Default in the class
		$configItems = ['design' => false, 'information' => false, 'sorting' => false];
		$level = 0;
		$configPath = 'Some/folder';
		$exception = new ConfigException('Boom');
		$result =
			[['error' => ['message' => 'Boom' . ". Config location: /$configPath"]]];

		$this->mockGetPathFromVirtualRoot($folder, $configPath);
		$this->mockGetFolderConfigWithBrokenSetup(
			$folder, $configName, $config, $configItems, $level, $exception
		);

		$response = self::invokePrivate(
			$this->service, 'buildFolderConfig', [$folder, $configName, $config, $level]
		);

		$this->assertSame($result, $response);
	}

	public function providesValidatesInfoConfigData() {
		$description = 'My cute description';
		$copyright = 'Copyright 2004-2016 interfaSys sàrl';

		$albumConfig = [
			'information' => [
				'description_link' => $description,
				'copyright_link'   => $copyright,
			]
		];

		$modifiedAlbumConfig = [
			'information' => [
				'description_link' => null,
				'copyright_link'   => null,
			]
		];

		return [
			[0, 0, $albumConfig, $albumConfig],
			[1, 0, $albumConfig, $modifiedAlbumConfig],
			[1, 2, $albumConfig, $albumConfig]
		];
	}

	/**
	 * @dataProvider providesValidatesInfoConfigData
	 *
	 * @param $level
	 * @param $virtualRootLevel
	 * @param $albumConfig
	 * @param $modifiedAlbumConfig
	 */
	public function testValidatesInfoConfig(
		$level, $virtualRootLevel, $albumConfig, $modifiedAlbumConfig
	) {

		self::invokePrivate($this->service, 'virtualRootLevel', [$virtualRootLevel]);
		$albumConfig['information']['level'] = $level;
		$modifiedAlbumConfig['information']['level'] = $level;

		$response = self::invokePrivate($this->service, 'validatesInfoConfig', [$albumConfig]);

		$this->assertSame($modifiedAlbumConfig, $response);
	}

	private function mockIsMimeSupported($mimeSupported) {
		$map = [
			['image/png', true],
			['image/jpeg', true],
			['application/postscript', true],
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

	private function mockGetFolderConfigWithBrokenSetup(
		$folder, $configName, $config, $configItems, $level, $exception
	) {
		$this->configParser->expects($this->any())
						   ->method('getFolderConfig')
						   ->with(
							   $folder, $configName, $config, $configItems, $level
						   )
						   ->willThrowException($exception);
	}


}
