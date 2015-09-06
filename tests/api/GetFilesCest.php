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
		$I->wantTo('get the list of available media files');

		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->sendGET($this->apiUrl, $this->params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();

		$I->seeResponseJsonMatchesXpath('//files/path');
		$I->dontSeeResponseContainsJson(['path' => 'folder2/testimagelarge.svg']);
		// Folder 4 contains the .nomedia file
		$I->dontSeeResponseContainsJson(['path' => 'folder4']);

		$I->seeResponseJsonMatchesXpath('//albuminfo/path', '');

		$I->seeResponseContainsJson(['locationhaschanged' => false]);
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

	public function getListOfRootWhenGivenBogusPath(\Step\Api\User $I) {
		$params = $this->params;
		$params['location'] = '/completely/lost in/tests';

		$I->am('an app');
		$I->wantTo(
			'get the list of files of the root folder when typing a deep path which is completely wrong'
		);

		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->sendGET($this->apiUrl, $params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(['path' => 'testimage-corrupt.jpg']);
		$I->seeResponseContainsJson(['locationhaschanged' => true]);
	}

	public function getListOfParentFolderWhenFolderHasTypo(\Step\Api\User $I) {
		$params = $this->params;
		// The correct path is /folder1/shared1/shared1.1, containing testimage.png
		$params['location'] = '/folder1/shared1/shared1.2';

		$I->am('an app');
		$I->wantTo(
			'get the list of files of the parent folder when the last folder contains a typo'
		);

		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->sendGET($this->apiUrl, $params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		// /folder1/shared1 only contains 2 files. Warning, alphabetical order
		$I->seeResponseJsonMatchesXpath('//files[path[1]="folder1/shared1/testimage.eps"]');
		// This is weird and might come from a bug in Codeception
		$I->seeResponseJsonMatchesXpath('//files[path[1][1]="folder1/shared1/testimage.gif"]');
		$I->seeResponseContainsJson(['locationhaschanged' => true]);
	}

	public function getListOfForbiddenPath(\Step\Api\User $I) {
		$params = $this->params;
		// This folder contains a .nomedia file
		$params['location'] = 'folder4';

		$I->am('an app');
		$I->wantTo(
			'get the list of files of a folder which contains the .nomedia file'
		);

		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->sendGET($this->apiUrl, $params);
		$statusCode = 403;
		$I->seeResponseCodeIs($statusCode);
		$I->seeResponseIsJson();
		$I->seeResponseContainsJson(
			['message' => 'Album is private or unavailable (' . $statusCode . ')']
		);
	}

}
