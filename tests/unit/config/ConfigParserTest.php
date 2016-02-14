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

namespace OCA\Gallery\Config;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Exception\ParseException;

/**
 * Class ConfigParserTest
 *
 * @package OCA\Gallery\Config
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
		$parsedFeatureList = [
			'native_svg'
		];
		$features = [
			'features' => $featureList
		];

		return [
			[$emptyConfig, false, []],
			[$noFeatures, false, []],
			[$emptyFeatures, false, []],
			[$emptyFeatures, true, []],
			[$features, false, $parsedFeatureList],
			[$features, true, $parsedFeatureList],
		];
	}

	/**
	 * @dataProvider providesGetFeaturesListData
	 *
	 * @param array $config
	 * @param bool $bom BOM in utf-8 files
	 * @param array $expectedResult
	 */
	public function testGetFeaturesList($config, $bom, $expectedResult) {
		$folder = $this->mockFolderWithConfig($config, $bom);

		$response = $this->configParser->getFeaturesList($folder, $this->configName);

		$this->assertEquals($expectedResult, $response);
	}

	/**
	 * @expectedException \OCA\Gallery\Config\ConfigException
	 */
	public function testGetFeaturesListWithBrokenConfig() {
		$folder = $this->mockFolder('home::user', 121212, []);
		$folder->method('get')
			   ->with($this->configName)
			   ->willThrowException(new \Exception('Computer says no'));

		$this->configParser->getFeaturesList($folder, $this->configName);
	}

	public function providesGetFolderConfigData() {
		// An empty config file
		$emptyConfig = [];

		// The information section of a standard root config
		$description = 'My cute description';
		$copyright = 'Copyright 2004-2016 interfaSys sàrl';
		$infoList = [
			'description_link' => $description,
			'copyright_link'   => $copyright,
			'inherit'          => 'yes'
		];
		$informationConfig = [
			'information' => $infoList
		];

		// The sorting section of a standard root config
		$sortingList = [
			'type'    => 'name',
			'order'   => 'des',
			'inherit' => 'yes'
		];
		$sortingConfig = [
			'sorting' => $sortingList
		];

		// The standard config, including an information and sorting sub-section
		$standardConfig = array_merge($informationConfig, $sortingConfig);

		// The level for the current folder is always 0
		$folderLevel = 0;

		// The level of a parent folder, counted from the current config folder
		$parentLevel = 1;

		// The completion status only changes if the current folder contains a usable config
		$nothingCompleted = ['design' => false, 'information' => false, 'sorting' => false];
		$infoCompleted = ['design' => false, 'information' => true, 'sorting' => false];
		$sortingCompleted = ['design' => false, 'information' => false, 'sorting' => true];
		$designCompleted = ['design' => true, 'information' => false, 'sorting' => false];
		$infoAndSortingCompleted = ['design' => false, 'information' => true, 'sorting' => true];
		$allCompleted = ['design' => true, 'information' => true, 'sorting' => true];

		// Case 0: Standard config in the current folder only
		$currentConfigOnlyResult = $standardConfig;
		$currentConfigOnlyResult['information']['level'] = $folderLevel;
		$currentConfigOnlyResult['sorting']['level'] = $folderLevel;

		// Case 1: Use standard config from parent folder
		$parentConfigOnlyResult = $standardConfig;
		$parentConfigOnlyResult['information']['level'] = $parentLevel;
		$parentConfigOnlyResult['sorting']['level'] = $parentLevel;

		// Case 2: Sorting with missing type = unusable
		$brokenSortingConfig = [
			'sorting' => [
				'order'   => 'des',
				'inherit' => 'no'
			]
		];

		// Case 3: Use sorting config from folder and info config from parent
		$dateSortingConfig = [
			'sorting' => [
				'type'  => 'date',
				'order' => 'des',
			]
		];

		$dateSortingConfigResult = array_merge($standardConfig, $dateSortingConfig);
		$dateSortingConfigResult['information']['level'] = $parentLevel;

		// Case 4: Evil sorting type = unusable
		$evilDateSortingConfig = [
			'sorting' => [
				'type'  => 'date<script>alert(1)</script>',
				'order' => 'des',
			]
		];

		// Case 5: Evil sorting order = unusable
		$evilSortingOrderConfig = [
			'sorting' => [
				'type'  => 'date',
				'order' => 'des<script>alert(1)</script>',
			]
		];

		// Case 6: Setting a background colour
		$designColourConfig = [
			'design' => [
				'background' => '#ff9f00'
			]
		];

		$designConfigResult = array_merge($emptyConfig, $designColourConfig);
		$designConfigResult['design']['level'] = $folderLevel;

		// Case 7: Evil background colour = unusable
		$evilDesignColourConfig = [
			'design' => [
				'background' => '#ff9f00<script>alert(1)</script>'
			]
		];

		$infoConfig = [
			'information' => [
				'description_link' => 'Local conf',
				'copyright_link'   => '2015 me',
			]
		];

		// Full information is inherited from root
		$infoConfigResult = array_merge($standardConfig, $infoConfig);
		$infoConfigResult['sorting']['level'] = $parentLevel;

		/**
		 * @param $currentConfig
		 * @param $completionStatus
		 * @param $newConfig
		 * @param $level
		 * @param $expectedResult
		 */
		return [
			[
				$emptyConfig, $nothingCompleted, $standardConfig, $folderLevel,
				[$currentConfigOnlyResult, $infoAndSortingCompleted]
			],// case 0
			[
				$emptyConfig, $nothingCompleted, $standardConfig, $parentLevel,
				[$parentConfigOnlyResult, $infoAndSortingCompleted]
			],// case 1
			[
				$emptyConfig, $nothingCompleted, $brokenSortingConfig, $folderLevel,
				[$emptyConfig, $nothingCompleted]
			],// case 2
			[
				$dateSortingConfig, $sortingCompleted, $standardConfig, $parentLevel,
				[$dateSortingConfigResult, $infoAndSortingCompleted]
			],// case 3
			[
				$emptyConfig, $nothingCompleted, $evilDateSortingConfig, $folderLevel,
				[$emptyConfig, $nothingCompleted]
			],// case 4
			[
				$emptyConfig, $nothingCompleted, $evilSortingOrderConfig, $folderLevel,
				[$emptyConfig, $nothingCompleted]
			],// case 5
			[
				$emptyConfig, $nothingCompleted, $designColourConfig, $folderLevel,
				[$designConfigResult, $designCompleted]
			],// case 6
			[
				$emptyConfig, $nothingCompleted, $evilDesignColourConfig, $folderLevel,
				[$emptyConfig, $nothingCompleted]
			]// case 7
		];
	}

	/**
	 * @dataProvider providesGetFolderConfigData
	 *
	 * @param array $currentConfig config collected so far
	 * @param array $completionStatus which sub-sections were filled so far
	 * @param array $newConfig config found in the folder we're analysing
	 * @param int $level level at which the folder we're analysing is at (initial folder or parent)
	 * @param array $expectedResult
	 */
	public function testGetFolderConfig(
		$currentConfig, $completionStatus, $newConfig, $level, $expectedResult
	) {
		$folder = $this->mockFolderWithConfig($newConfig);

		$response = $this->configParser->getFolderConfig(
			$folder, $this->configName, $currentConfig, $completionStatus, $level
		);

		$this->assertEquals($expectedResult, $response);
	}

	private function mockFolderWithConfig($config, $bom = false) {
		$file = $this->mockFile(212121);
		$yaml = new Dumper();
		$content = $yaml->dump($config);
		$content = $bom ? chr(239) . chr(187) . chr(191) . $content : $content;
		$file->method('getContent')
			 ->willReturn($content);
		$folder = $this->mockFolder('home::user', 121212, [$file]);
		$folder->method('get')
			   ->with($this->configName)
			   ->willReturn($file);

		return $folder;
	}

}
