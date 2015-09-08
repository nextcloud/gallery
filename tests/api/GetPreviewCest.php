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
 * Class GetPreviewCest
 */
class GetPreviewCest {

	private $apiUrl;

	/**
	 * Sets up the environment for this series of tests
	 *
	 * @param ApiTester $I
	 */
	public function _before(ApiTester $I) {
		$this->apiUrl = GalleryApp::$URL . 'api/preview';
	}

	public function _after(ApiTester $I) {
	}

	/**
	 * Connects to the API as an anonymous user
	 *
	 * @param \Step\Api\Anonymous $I
	 */
	public function unauthorizedAccess(\Step\Api\Anonymous $I) {
		$I->connectToTheApi($this->apiUrl . '/9999999/1920/1080', 'the preview API');
	}

	public function getPreviewOfPng(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('get the preview of a PNG file');

		$I->getUserCredentialsAndUseHttpAuthentication();
		$data = $I->getFilesDataForFolder('folder2');
		$file = $data['testimage.png'];
		$url = $this->apiUrl . '/' . $file['id'] . '/64/64';
		$I->sendGET($url);
		$I->downloadAFile($file, 'testimage.png');
		$I->checkImageSize(64, 64);
	}

	/**
	 * That's a different code path because the file is animated
	 *
	 * @todo maybe
	 *
	 * @param \Step\Api\User $I
	 */
	public function getPreviewOfAnimatedGif(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('get the preview of an animated GIF file');

		$I->getUserCredentialsAndUseHttpAuthentication();
		$data = $I->getFilesDataForFolder('');
		// 89x72 gif
		$file = $data['animated.gif'];
		$url = $this->apiUrl . '/' . $file['id'] . '/64/64';
		$I->sendGET($url);
		$I->downloadAFile($file, 'animated.gif');
		$I->checkImageSize(89, 72);
	}

	public function emptyResponse(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('get the preview of a file without a valid fileId');
		$I->amGoingTo("send a fileId which doesn't exist");
		$I->expectTo("receive a 404");
		$I->getUserCredentialsAndUseHttpAuthentication();
		$url = $this->apiUrl . '/9999999/1920/1080';
		$I->sendGET($url);
		$I->seeResponseCodeIs(404);
	}

	public function getPreviewOfBrokenFile(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('get the preview of a broken file');
		$I->expect('an 500 status and an error message');

		$I->getUserCredentialsAndUseHttpAuthentication();
		$data = $I->getFilesDataForFolder('');
		$file = $data['testimage-corrupt.jpg'];
		$url = $this->apiUrl . '/' . $file['id'] . '/1920/1080';
		$I->sendGET($url);
		$I->seeResponseCodeIs(500);
		$I->seeResponseContainsJson(['success' => false]);
	}
}
