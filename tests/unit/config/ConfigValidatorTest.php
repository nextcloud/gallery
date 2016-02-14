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

/**
 * Class ConfigValidatorTest
 *
 * @package OCA\Gallery\Config
 */
class ConfigValidatorTest extends \Test\GalleryUnitTest {

	/** @var ConfigValidator */
	protected $configValidator;

	/**
	 * Test set up
	 */
	protected function setUp() {
		parent::setUp();

		$this->configValidator = new ConfigValidator();
	}

	public function providesIsConfigSafeData() {
		// An empty config file
		$emptyConfig = [];

		// Info Config
		$infoConfig = [
			'description_link' => 'Local conf',
			'copyright_link'   => '2015 me',
		];

		// The sorting section of a standard root config
		$sortingConfig = [
			'type'    => 'name',
			'order'   => 'des',
			'inherit' => 'yes'
		];

		// Evil sorting type = unusable
		$evilDateSortingConfig = [
			'type'  => 'date<script>alert(1)</script>',
			'order' => 'des',
		];

		// Evil sorting order = unusable
		$evilSortingOrderConfig = [
			'type'  => 'date',
			'order' => 'des<script>alert(1)</script>',
		];

		// Setting a background colour
		$designColourConfig = [
			'background' => '#ff9f00'
		];

		// Evil background colour = unusable
		$evilDesignColourConfig = [
			'background' => '#ff9f00<script>alert(1)</script>'
		];

		/**
		 * @param $key
		 * @param $parsedConfigItem
		 * @param $expectedResult
		 */
		return [
			[
				'information', $emptyConfig, true
			],
			[
				'sorting', $emptyConfig, true
			],
			[
				'design', $emptyConfig, true
			],
			[
				'information', $infoConfig, true
			],
			[
				'sorting', $sortingConfig, true
			],
			[
				'sorting', $evilDateSortingConfig, false
			],
			[
				'sorting', $evilSortingOrderConfig, false
			],
			[
				'design', $designColourConfig, true
			],
			[
				'design', $evilDesignColourConfig, false
			]
		];
	}

	/**
	 * @dataProvider providesIsConfigSafeData
	 *
	 * @param string $key the configuration sub-section identifier
	 * @param array $parsedConfigItem the configuration for a sub-section
	 * @param array $expectedResult
	 */
	public function testIsConfigSafe($key, $parsedConfigItem, $expectedResult) {

		$response = $this->configValidator->isConfigSafe($key, $parsedConfigItem);

		$this->assertEquals($expectedResult, $response);
	}

}
