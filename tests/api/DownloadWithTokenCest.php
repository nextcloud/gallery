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
 * Class DownloadWithTokenCest
 */
class DownloadWithTokenCest {

	private $apiUrl;
	private $browserHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

	/**
	 * Sets up the environment for this series of tests
	 *
	 * @param ApiTester $I
	 */
	public function _before(\Step\Api\TokenUser $I) {
		$this->apiUrl = GalleryApp::$URL . 's/';
		$I->haveHttpHeader('Accept','text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8');
	}

	public function _after(ApiTester $I) {
	}

	public function downloadFile(\Step\Api\TokenUser $I) {
		$I->am('a file owner');
		$I->wantTo('insert a file in a forum');

		$fileMetaData = $I->getSharedFileInformation();
		$I->haveHttpHeader('Accept',$this->browserHeader);
		$I->sendGET($this->apiUrl . $fileMetaData['token']);
		$I->downloadAFile($fileMetaData);
	}

	public function downloadFileWithCustomFilename(\Step\Api\TokenUser $I) {
		$I->am('a file owner');
		$I->wantTo('insert a file in a forum');

		$fileMetaData = $I->getSharedFileInformation();
		// Note: The share file is a PNG
		$filename = 'jackinabox.png';
		$url = $this->apiUrl . $fileMetaData['token'] . '/' . $filename;
		$I->haveHttpHeader('Accept',$this->browserHeader);
		$I->sendGET($url);
		$I->downloadAFile($fileMetaData, $filename);
	}

	/**
	 * When a token is not valid we get an error 400, NOT 404
	 *
	 * @param \Step\Api\TokenUser $I
	 */
	public function fileNotFoundPage(\Step\Api\TokenUser $I) {
		$I->am('a file owner');
		$I->wantTo('insert a file in a forum');
		$I->amGoingTo("send a bogus token");
		$I->expectTo("be redirected to an error 400 page");
		$I->haveHttpHeader('Accept',$this->browserHeader);
		$I->sendGET($this->apiUrl . '1AmaW1cK3d70k3N');
		$I->seeResponseCodeIs(400);
		$I->seeHttpHeader('Content-type', 'text/html; charset=UTF-8');
	}
}
