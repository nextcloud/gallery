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

use Page\Gallery as GalleryApp;

/**
 * Class GetConfigCest
 *
 * @todo Inject config items and compare the result
 */
class GetConfigCest {

	private $apiUrl;
	private $params = [
		'extramediatypes' => false
	];
	/**
	 * Has to match what is in the dataSetup configuration
	 *
	 * @var array
	 */
	private $parsedFeatures = [
		'features' => ['external_shares']
	];
	private $mediaTypes = [
		"mediatypes" => [
			"image/png",
			"image/jpeg",
			"image/gif",
			"application/postscript"
		]
	];

	public function _before(ApiTester $I) {
		$this->apiUrl = GalleryApp::$URL . 'api/config';
	}

	public function _after(ApiTester $I) {
	}

	/**
	 * Connects to the API as an anonymous user
	 *
	 * @param \Step\Api\Anonymous $I
	 */
	public function unauthorizedAccess(\Step\Api\Anonymous $I) {
		$I->connectToTheApi($this->apiUrl, 'the config API');
	}

	/**
	 * Retrieves the configuration
	 *
	 * @todo figure out why seeResponseJsonMatchesXpath returns
	 *        [DOMException] Invalid Character Error
	 *
	 * @param ApiTester $I
	 */
	public function getConfig(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('get the current Gallery configuration');

		$this->tryToGetAValidConfig($I);
		/**
		 * Warning: Needs to match what is in the test config
		 * If we automate the detection, we're dependant on the results created by a 3rd party lib
		 */
		$I->seeResponseContainsJson($this->parsedFeatures);
		/**
		 * TODO Replace with JSONPath once the library is fixed
		 */
		$I->seeResponseContainsJson($this->mediaTypes);
	}

	/**
	 * @depends getConfig
	 *
	 * @param ApiTester $I
	 * @param $scenario
	 */
	public function getConfigWithExtraMediaTypes(\Step\Api\User $I, \Codeception\Scenario $scenario
	) {
		$I->am('an app');
		$I->wantTo('get the current Gallery configuration which should include extra media types');

		$params = ['extramediatypes' => true];
		$this->tryToGetAValidConfig($I, $params);
		/**
		 * TODO Replace with JSONPath once the library is fixed
		 */
		$mediaTypes = $this->mediaTypes;
		$mediaTypes['mediatypes'] = ["application/font-sfnt"];
		$mediaTypes['mediatypes'] = ["application/x-font"];
		$I->seeResponseContainsJson($mediaTypes);
	}

	/**
	 * @depends getConfig
	 *
	 * @param \Step\Api\User $I
	 */
	public function getBadConfig(\Step\Api\User $I) {
		$I->breakMyConfigFile();

		$I->am('an app');
		$I->wantTo('get the current Gallery configuration');
		$I->expectTo('receive an error message');

		$this->tryToGetAValidConfig($I);
		/**
		 * Might be worth bringing the error object one level up
		 */
		$I->seeResponseContainsJson(
			[
				'features' => [
					[
						'error' => [
							'message' => 'Problem while reading or parsing the configuration file. Config location: /'
						]

					]
				]
			]
		);

		$I->fixMyConfigFile();
	}

	/**
	 * @depends getConfig
	 *
	 * @param \Step\Api\User $I
	 */
	public function getConfigWithBom(\Step\Api\User $I) {
		$I->createMyConfigFileWithABom();

		$I->am('an app');
		$I->wantTo('get the current Gallery configuration');
		$I->expectTo('see the same config as in getConfig()');

		$this->tryToGetAValidConfig($I);
		$I->seeResponseContainsJson($this->parsedFeatures);
		$I->seeResponseContainsJson($this->mediaTypes);

		$I->fixMyConfigFile();
	}

	/**
	 * @depends getConfig
	 *
	 * @param \Step\Api\User $I
	 */
	public function getEmptyConfig(\Step\Api\User $I) {
		$I->emptyMyConfigFile();

		$I->am('an app');
		$I->wantTo('get the current Gallery configuration');
		$I->expectTo('see empty features');

		$this->tryToGetAValidConfig($I);
		$I->seeResponseContainsJson(['features' => []]);
		$I->seeResponseContainsJson($this->mediaTypes);

		$I->fixMyConfigFile();
	}

	/**
	 * @param \Step\Api\User $I
	 */
	private function tryToGetAValidConfig($I, $params = null) {
		if (!$params) {
			$params = $this->params;
		}
		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->sendGET($this->apiUrl, $params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
	}
}
