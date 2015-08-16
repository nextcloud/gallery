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
use Helper\DataSetup;

/**
 * Class GetFilesCest
 *
 * @todo Match the original structure
 */
class GetFilesCest {

	private $setupData;
	private $userId;
	private $password;
	private $filesApi;
	private $params = [
		'mediatypes' => 'image/png;image/jpeg;image/gif;application/postscript'
	];

	/**
	 * Injects objects we need
	 *
	 * @param DataSetup $setupData
	 */
	protected function _inject(DataSetup $setupData) {
		$this->setupData = $setupData;
	}

	public function _before(ApiTester $I) {
		$this->filesApi = GalleryApp::$URL . 'api/files/list';
		$this->userId = $this->setupData->userId;
		$this->password = $this->setupData->userPassword;
	}

	public function _after(ApiTester $I) {
	}

	public function unauthorizedAccess(ApiTester $I) {
		$I->am('an app');
		$I->wantTo('connect to the Files API without credentials');
		$I->sendGET($this->filesApi, $this->params);
		$I->seeResponseCodeIs(401);
		$I->seeResponseIsJson();
	}

	public function getStandardList(ApiTester $I) {
		$I->am('an app');
		$I->wantTo(
			'get the list of available media files'
		);

		$I->amHttpAuthenticated($this->userId, $this->password);
		$I->sendGET($this->filesApi, $this->params);
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
	 * @after getStandardList
	 *
	 * @param ApiTester $I
	 */
	public function getListWithNativeSvgEnabled(ApiTester $I) {
		$mediaTypes = $this->params['mediatypes'];
		$params = ['mediatypes' => $mediaTypes . ';image/svg+xml'];

		$I->am('an app');
		$I->wantTo('get the list of available media files which should include SVGs');

		$I->amHttpAuthenticated($this->userId, $this->password);
		$I->sendGET($this->filesApi, $params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['path' => 'folder2/testimagelarge.svg']);
	}

}
