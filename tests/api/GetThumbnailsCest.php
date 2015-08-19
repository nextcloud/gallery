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
 * Class GetThumbnailsCest
 */
class GetThumbnailsCest {

	private $apiUrl;
	private $params = [
		'square' => false,
		'scale'  => 2.5
	];

	/**
	 * Sets up the environment for this series of tests
	 *
	 * @param ApiTester $I
	 */
	public function _before(ApiTester $I) {
		$this->apiUrl = GalleryApp::$URL . 'api/thumbnails';
	}

	public function _after(ApiTester $I) {
	}

	/**
	 * Connects to the API as an anonymous user
	 *
	 * @param \Step\Api\Anonymous $I
	 */
	public function unauthorizedAccess(\Step\Api\Anonymous $I) {
		$I->connectToTheApi($this->apiUrl, 'the thumbnails API');
	}

	public function getFilesThumbnails(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('get the thumbnails for the files in this folder');

		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->haveHttpHeader('Accept', 'text/event-stream');
		$data = $I->getFilesDataForFolder('');
		$id1 = $data['testimage.jpg']['id'];
		$id2 = $data['animated.gif']['id'];
		$this->params['ids'] = $id1.';'.$id2;

		$I->sendGET($this->apiUrl, $this->params);
		$I->seeResponseCodeIs(200);
		$I->seeHttpHeader('Content-type', 'text/event-stream;charset=UTF-8');
		$I->seeResponseContains('"status":200');
		$I->seeResponseContains('"fileid":"' . $id1 . '","status":200');
		$I->seeResponseContains('"fileid":"' . $id2 . '","status":200');

	}

	public function getFileNotFoundCode(\Step\Api\User $I) {
		$I->am('an app');
		$I->wantTo('receive 404 events when I send the wrong IDs');
		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->haveHttpHeader('Accept', 'text/event-stream');
		$this->params['ids'] = '99998;99999';
		$I->sendGET($this->apiUrl, $this->params);
		$I->seeResponseCodeIs(200);
		$I->seeHttpHeader('Content-type', 'text/event-stream;charset=UTF-8');
		$I->seeResponseContains('"fileid":"99998","status":404');
		$I->seeResponseContains('"fileid":"99999","status":404');
	}

}
