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
 * Class ConnectWithTokenCest
 */
class ConnectWithTokenCest {

	private $folderMetaData;
	private $browserHeader = 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8';

	public function _before(\Step\Api\TokenUser $I) {
		$this->folderMetaData = $I->getSharedFolderInformation();
	}

	public function _after(ApiTester $I) {
	}

	public function connectToConfig(\Step\Api\TokenUser $I) {
		$I->am('a guest with a token');
		$I->wantTo('make sure I can get the config');

		$this->connect($I, GalleryApp::$URL . '/config.public');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(200);
	}

	public function connectToFiles(\Step\Api\TokenUser $I) {
		$I->am('a guest with a token');
		$I->wantTo('make sure I can get the files');

		$this->connect($I, GalleryApp::$URL . '/files.public/list');
		$I->seeResponseIsJson();
		$I->seeResponseCodeIs(200);
	}

	public function connectToThumbnails(\Step\Api\TokenUser $I) {
		$I->am('a guest with a token');
		$I->wantTo('make sure I can get the thumbails');


		$data = $I->getFilesDataForFolder('shared1');
		$id1 = $data['testimage.eps']['id'];
		$id2 = $data['testimage.gif']['id'];
		$params = [
			'ids'    => $id1 . ';' . $id2,
			'square' => true,
			'scale'  => 1.7
		];

		$this->connect($I, GalleryApp::$URL . '/thumbnails.public', $params, 'text/event-stream');
		$I->seeResponseCodeIs(200);
		$I->seeHttpHeader('Content-type', 'text/event-stream;charset=UTF-8');
		$I->seeResponseContains('"status":200');
		$I->seeResponseContains('"fileid":"' . $id1 . '","status":200');
		$I->seeResponseContains('"fileid":"' . $id2 . '","status":200');
	}

	public function connectToPreview(\Step\Api\TokenUser $I) {
		$I->am('a guest with a token');
		$I->wantTo('make sure I can get the preview');

		$data = $I->getFilesDataForFolder('shared1');
		$file = $data['testimage.gif'];
		$url = GalleryApp::$URL . '/preview.public/' . $file['id'];
		$params = [
			'width'  => 800,
			'height' => 600
		];
		$this->connect($I, $url, $params, $this->browserHeader);
		$I->downloadAFile($file, 'testimage.gif');
	}

	private function connect(
		\Step\Api\TokenUser $I, $url, $params = [],
		$acceptHeaders = 'application/json, text/javascript, */*;q=0.01'
	) {
		$I->haveHttpHeader('Accept', $this->browserHeader);
		$I->sendGET('/');

		$html = $I->grabResponse();
		$tidy = tidy_parse_string($html);
		$head = $tidy->head();
		$requestToken = $head->attribute['data-requesttoken'];

		$I->haveHttpHeader('Accept', $acceptHeaders);
		$I->haveHttpHeader('requesttoken', $requestToken);

		$params = array_merge(
			$params, [
					   'token'    => $this->folderMetaData['token'],
					   'password' => $this->folderMetaData['password']
				   ]
		);

		$I->sendGET($url, $params);
	}

}
