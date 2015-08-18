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
 * Class GetFilesCest
 *
 * @todo Match the original structure
 */
class GetFilesCest {

	private $apiUrl;
	private $params;

	/**
	 * Sets up the environment for this series of tests
	 *
	 * We use custom methods defined in _support/Helper/Api
	 * If these are re-usable across suites, they may move to _support/Step
	 *
	 * @param ApiTester $I
	 */
	public function _before(ApiTester $I) {
		$this->apiUrl = GalleryApp::$URL . 'api/files/list';
		list($mediaTypes) = $I->getMediaTypes();
		$this->params = [
			'mediatypes' => implode(';', $mediaTypes)
		];
	}

	public function _after(ApiTester $I) {
	}

	/**
	 * Connects to the API as an anonymous user
	 *
	 * @param \Step\Api\Anonymous $I
	 */
	public function unauthorizedAccess(\Step\Api\Anonymous $I) {
		$I->connectToTheApi($this->apiUrl, 'the files/list API');
	}

	public function getStandardList(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo(
			'get the list of available media files'
		);

		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->sendGET($this->apiUrl, $this->params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->seeResponseJsonMatchesXpath('//files');
		$I->seeResponseJsonMatchesXpath('//albuminfo');
		$I->seeResponseJsonMatchesXpath('//locationhaschanged');


		$I->seeResponseJsonMatchesXpath('//files/path');
		$I->dontSeeResponseContainsJson(['path' => 'folder2/testimagelarge.svg']);

		$I->seeResponseJsonMatchesXpath('//albuminfo/path', '');
	}

	/**
	 * @depends getStandardList
	 *
	 * @param ApiTester $I
	 */
	public function getListWithNativeSvgEnabled(\Step\Api\User $I) {
		$mediaTypes = $this->params['mediatypes'];
		$params = ['mediatypes' => $mediaTypes . ';image/svg+xml'];

		$I->am('an app');
		$I->wantTo('get the list of available media files which should include SVGs');

		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->sendGET($this->apiUrl, $params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['path' => 'folder2/testimagelarge.svg']);
	}

}
