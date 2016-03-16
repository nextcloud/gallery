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

		$I->seeResponseJsonMatchesJsonPath('$.files[*]..path]');
		$I->seeResponseJsonMatchesJsonPath('$.albums[*]..path]');
		$I->dontSeeResponseContainsJson(['path' => 'folder2/testimagelarge.svg']);
		// Folder 4 contains the .nomedia file
		$I->dontSeeResponseContainsJson(['path' => 'folder4']);
		$I->seeResponseContainsJson(
			[
				'design'      => [
					'background' => '#ff9f00',
					'inherit'    => 'yes',
					'level'      => 0,
				],
				'information' =>
					[
						// You have to use double-quotes here in order to be able to insert the line return
						'description' => "# This is the official **Gallery** sample folder\xA" .
										 "Contribute to this project [on Github](https://github.com/owncloud/gallery)\xA",
						'copyright'   => 'Copyright 2014-2015 [Acme](http://www.ubersecrettester.ninja)',
						'inherit'     => 'yes',
						'level'       => 0,
					],
				'sorting'     =>
					[
						'type'    => 'date',
						'order'   => 'des',
						'inherit' => 'yes',
						'level'   => 0,
					],
			]
		);
		$I->seeResponseContainsJson(['albumpath' => '']);
		$I->seeResponseContainsJson(['updated' => true]);
	}

	/**
	 * @depends getStandardList
	 *
	 * @param \Step\Api\User $I
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
		$I->seeResponseContainsJson(['albumpath' => '']);
		$I->seeResponseContainsJson(['updated' => true]);
	}

	public function getListOfParentFolderWhenFolderHasTypo(\Step\Api\User $I) {
		$params = $this->params;
		// This doesn't match any path in the filesystem, the correct path is
		// /folder1/shared1/shared1.1, containing testimage.png
		$params['location'] = 'folder1/shared1/shared1.2';
		// This is the path which will be used instead
		$parentPath = 'folder1/shared1';

		$I->am('an app');
		$I->wantTo(
			'get the list of files of the parent folder when the last folder contains a typo'
		);

		$I->getUserCredentialsAndUseHttpAuthentication();
		$I->sendGET($this->apiUrl, $params);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsJson();
		// /folder1/shared1 only contains 2 files. Warning, alphabetical order
		$I->seeResponseContainsJson(
			[
				'path'           => $parentPath . '/testimage.eps',
				'sharedwithuser' => false,
				'owner'          => [
					'uid'         => 'tester',
					'displayname' => 'Gallery Tester (tester)'
				],
				'permissions'    => 27,
				'mimetype'       => 'application/postscript'
			]
		);
		$I->seeResponseContainsJson(
			[
				'path'           => $parentPath . '/testimage.gif',
				'sharedwithuser' => false,
				'owner'          => [
					'uid'         => 'tester',
					'displayname' => 'Gallery Tester (tester)'
				],
				'permissions'    => 27,
				'mimetype'       => 'image/gif'
			]
		);
		$I->seeResponseJsonMatchesJsonPath('$.albums[*]..path]');
		$I->seeResponseContainsJson(
			[
				'path'           => $parentPath,
				'sharedwithuser' => false,
				'owner'          => [
					'uid'         => 'tester',
					'displayname' => 'Gallery Tester (tester)'
				],
				'permissions'    => 31
			]
		);
		$I->seeResponseContainsJson(['albumpath' => $parentPath]);
		$I->seeResponseContainsJson(['updated' => true]);
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
			[
				'message' => 'The owner has placed a restriction or the storage location is unavailable ('
							 . $statusCode . ')'
			]
		);
	}

}
