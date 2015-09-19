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

namespace OCA\GalleryPlus\Service;

use OCA\GalleryPlus\Config\ConfigParser;
use OCA\GalleryPlus\Config\ConfigException;

/**
 * Class ConfigServiceTest
 *
 * @package OCA\GalleryPlus\Controller
 */
class ConfigServiceTest extends \Test\GalleryUnitTest {

	/** @var ConfigService */
	protected $service;
	/** @var ConfigParser */
	protected $configParser;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$this->configParser = $this->getMockBuilder('\OCA\GalleryPlus\Config\ConfigParser')
								   ->disableOriginalConstructor()
								   ->getMock();
		$this->service = new ConfigService (
			$this->appName,
			$this->environment,
			$this->configParser,
			$this->logger
		);
	}

	public function testBuildFolderConfigWithBrokenSetup() {
		$nodeId = 65965;
		$files = [];
		$folder = $this->mockFolder('home::user', $nodeId, $files);
		$configName = 'gallery.cnf';
		$config = [];
		$configItems = ['information' => false, 'sorting' => false]; // Default in the class
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
