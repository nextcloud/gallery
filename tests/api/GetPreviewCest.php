<?php
/**
 * ownCloud - galleryplus
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
		$I->connectToTheApi($this->apiUrl. '/12345/1920/1080', 'the preview API');
	}

	public function getPreview(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('get the preview of a file');

		$I->getUserCredentialsAndUseHttpAuthentication();
		$data = $I->getFilesDataForFolder('');
		$file = $data[0];
		$url = $this->apiUrl . '/' . $file['id'] . '/1920/1080';
		$filename = urlencode($file['name']);
		$I->sendGET($url);
		$I->seeResponseCodeIs(200);
		$I->seeHttpHeader('Content-type', $file['mediatype'] . '; charset=utf-8');
		$I->seeHttpHeader(
			'Content-Disposition', 'attachment; filename*=UTF-8\'\'' . $filename . '; filename="'
								   . $filename . '"'
		);

	}

	public function emptyResponse(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('get the preview of a file without a valid fileId');
		$I->amGoingTo("send a fileId which doesn't exist");
		$I->expectTo("receive a 404");
		$I->getUserCredentialsAndUseHttpAuthentication();
		$url = $this->apiUrl . '/0/1920/1080';
		$I->sendGET($url);
		$I->seeResponseCodeIs(404);
	}
}
