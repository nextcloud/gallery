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
 * Class DownloadCest
 */
class DownloadCest {

	private $apiUrl;

	/**
	 * Sets up the environment for this series of tests
	 *
	 * @param ApiTester $I
	 */
	public function _before(ApiTester $I) {
		$this->apiUrl = GalleryApp::$URL . 'api/files/download';
	}

	public function _after(ApiTester $I) {
	}

	/**
	 * Connects to the API as an anonymous user
	 *
	 * @param \Step\Api\Anonymous $I
	 */
	public function unauthorizedAccess(\Step\Api\Anonymous $I) {
		$I->connectToTheApi($this->apiUrl . '/9999999', 'the download API');
	}

	public function downloadFile(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('download a file');

		$I->getUserCredentialsAndUseHttpAuthentication();
		$data = $I->getFilesDataForFolder('');
		$file = $data['testimage.jpg'];
		$url = $this->apiUrl . '/' . $file['id'];
		$I->sendGET($url);
		$I->downloadAFile($file, 'testimage.jpg');
	}

	public function fileNotFoundPage(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('download a file without a valid fileId');
		$I->amGoingTo("send a fileId which doesn't exist");
		$I->expectTo("be redirected to an error 404 page");
		$I->getUserCredentialsAndUseHttpAuthentication();
		$url = $this->apiUrl . '/9999999';
		$I->sendGET($url);
		$I->seeResponseCodeIs(404);
		$I->seeHttpHeader('Content-type', 'text/html; charset=UTF-8');
	}
}
