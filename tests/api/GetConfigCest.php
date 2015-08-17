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

		$I->getUserCredentialsAndUseHttpAuthentication();
		$params = ['extramediatypes' => false];
		$I->sendGET($this->apiUrl, $params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();


		$I->seeResponseContainsJson(['features' => []]);

		/**
		 * TODO Replace with JSONPath once the library is fixed
		 */
		$I->seeResponseContainsJson(
			[
				"mediatypes" => [
					"image/png"              => "/core/img/filetypes/image.png",
					"image/jpeg"             => "/core/img/filetypes/image.png",
					"image/gif"              => "/core/img/filetypes/image.png",
					"application/postscript" => "/core/img/filetypes/image-vector.png"
				]
			]
		);
	}

	/**
	 * @depends getConfig
	 *
	 * @param ApiTester $I
	 * @param $scenario
	 */
	public function getConfigWithExtraMediaTypes(\Step\Api\User $I, \Codeception\Scenario $scenario) {
		$I->am('an app');
		$I->wantTo('get the current Gallery configuration which should include extra media types');

		$I->getUserCredentialsAndUseHttpAuthentication();
		$params = ['extramediatypes' => true];
		$I->sendGET($this->apiUrl, $params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		/**
		 * TODO Replace with JSONPath once the library is fixed
		 */
		$I->seeResponseContainsJson(
			[
				"mediatypes" => [
					"image/png"              => "/core/img/filetypes/image.png",
					"image/jpeg"             => "/core/img/filetypes/image.png",
					"image/gif"              => "/core/img/filetypes/image.png",
					"application/postscript" => "/core/img/filetypes/image-vector.png",
					"application/font-sfnt"  => "/core/img/filetypes/font.png",
					"application/x-font"     => "/core/img/filetypes/font.png"
				]
			]
		);
	}

}
