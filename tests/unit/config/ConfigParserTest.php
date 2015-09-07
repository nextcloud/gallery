<?php
/**
 * owncloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\GalleryPlus\Config;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class ConfigParserTest
 *
 * @package OCA\GalleryPlus\Config
 */
class ConfigParserTest extends \Test\GalleryUnitTest {

	/** @var string */
	protected $configName = 'gallery.cnf';
	/** @var ConfigParser */
	protected $configParser;

	/**
	 * Test set up
	 */
	protected function setUp() {
		parent::setUp();

		$this->configParser = new ConfigParser();
	}

	public function providesGetFeaturesListData() {
		$emptyConfig = [];
		$noFeatures = [
			'information' => [
				'description_link' => 'readme.md'
			]
		];
		$emptyFeatures = [
			'features' => []
		];
		$featureList = [
			'external_shares' => "no",
			'native_svg'      => "yes",
		];
		$features = [
			'features' => $featureList
		];

		return [
			[$emptyConfig, []],
			[$noFeatures, []],
			[$emptyFeatures, []],
			[$features, $featureList],
		];
	}

	/**
	 * @dataProvider providesGetFeaturesListData
	 *
	 * @param $config
	 * @param $expectedResult
	 */
	public function testGetFeaturesList($config, $expectedResult) {
		$folder = $this->mockFolderWithConfig($config);

		$response = $this->configParser->getFeaturesList($folder, $this->configName);

		$this->assertEquals($expectedResult, $response);
	}

	/**
	 * @expectedException \OCA\GalleryPlus\Config\ConfigException
	 */
	public function testGetFeaturesListWithBrokenConfig() {
		$folder = $this->mockFolder('home::user', 121212, []);
		$folder->method('get')
			   ->with($this->configName)
			   ->willThrowException(new \Exception('Computer says no'));

		$this->configParser->getFeaturesList($folder, $this->configName);
	}

	public function providesGetFolderConfigData() {
		$emptyConfig = [];
		$description = 'My cute description';
		$copyright = 'Copyright 2004-2016 interfaSys sàrl';
		$infoList = [
			'description_link' => $description,
			'copyright_link'   => $copyright,
			'inherit'          => 'yes'
		];
		$information = [
			'information' => $infoList
		];

		$sortingList = [
			'type'    => 'name',
			'order'   => 'des',
			'inherit' => 'yes'
		];
		$sorting = [
			'sorting' => $sortingList
		];
		$standardRootConfig = array_merge($information, $sorting);
		$standardLevel = 0;
		$rootLevel = 1;
		$nothingCompleted = ['information' => false, 'sorting' => false];
		$sortingCompleted = ['information' => false, 'sorting' => true];
		$infoCompleted = ['information' => true, 'sorting' => false];
		$allCompleted = ['information' => true, 'sorting' => true];

		// One config in the current folder only
		$currentConfigOnlyResult = $standardRootConfig;
		$currentConfigOnlyResult['information']['level'] = $standardLevel;
		$currentConfigOnlyResult['sorting']['level'] = $standardLevel;

		// Sorting with missing type
		$brokenSortingConfig = [
			'sorting' => [
				'order'   => 'des',
				'inherit' => 'no'
			]
		];

		// Sorting with different type
		$dateSortingConfig = [
			'sorting' => [
				'type'  => 'date',
				'order' => 'des',
			]
		];

		$dateSortingConfigResult = array_merge($standardRootConfig, $dateSortingConfig);
		$dateSortingConfigResult['information']['level'] = $rootLevel;

		$infoConfig = [
			'information' => [
				'description_link' => 'Local conf',
				'copyright_link'   => '2015 me',
			]
		];

		// Full information is inherited from root
		$infoConfigResult = array_merge($standardRootConfig, $infoConfig);
		$infoConfigResult['sorting']['level'] = $rootLevel;

		return [
			[
				$emptyConfig, $nothingCompleted, $standardRootConfig, $standardLevel,
				[$currentConfigOnlyResult, $allCompleted]
			],
			[
				$emptyConfig, $nothingCompleted, $brokenSortingConfig,
				$standardLevel, [$emptyConfig, $nothingCompleted]
			],
			[
				$dateSortingConfig, $sortingCompleted, $standardRootConfig, $rootLevel,
				[$dateSortingConfigResult, $allCompleted]
			],
			[
				$infoConfig, $infoCompleted, $standardRootConfig, $rootLevel,
				[$infoConfigResult, $allCompleted]
			],

		];
	}

	/**
	 * @dataProvider providesGetFolderConfigData
	 *
	 * @param $currentConfig
	 * @param $configItems
	 * @param $newConfig
	 * @param $level
	 * @param $expectedResult
	 */
	public function testGetFolderConfig(
		$currentConfig, $configItems, $newConfig, $level, $expectedResult
	) {
		$folder = $this->mockFolderWithConfig($newConfig);

		$response = $this->configParser->getFolderConfig(
			$folder, $this->configName, $currentConfig, $configItems, $level
		);

		$this->assertEquals($expectedResult, $response);
	}

	private function mockFolderWithConfig($config) {
		$file = $this->mockFile(212121);
		$yaml = new Dumper();
		$file->method('getContent')
			 ->willReturn($yaml->dump($config));
		$folder = $this->mockFolder('home::user', 121212, [$file]);
		$folder->method('get')
			   ->with($this->configName)
			   ->willReturn($file);

		return $folder;
	}

}
