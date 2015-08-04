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

namespace OCA\GalleryPlus\Environment;

use \OCA\GalleryPlus\Tests\Integration\GalleryIntegrationTest;

/**
 * Class EnvironmentTest
 *
 * @package OCA\GalleryPlus\Tests\Integration
 */
class EnvironmentTest extends GalleryIntegrationTest {

	/*public function setUp() {
		// Create user first
		//$this->setupUser($this->userId, $this->userPassword);

		parent::setUp();
	}*/

	/**
	 * Tests is setting up the environment using a normal user works
	 */
	public function testSetStandardEnv() {
		$this->environment = $this->setUserBasedEnv();
	}

	/**
	 * Tests is setting up the environment using a token works
	 */
	public function testSetTokenBasedEnv() {
		$token = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);
	}

	public function testGetResourceFromIdAsALoggedInUser() {
		$this->environment = $this->setUserBasedEnv();

		$testFolder = $this->userFolder->newFolder('deleteMe');
		$testFolderId = $testFolder->getId();

		$result = $this->environment->getResourceFromId($testFolderId);
		$this->assertEquals($testFolder->getId(), $result->getId());

		$testFolder->delete();
	}

	public function testGetResourceFromIdAsATokenUser() {
		$token = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$sharedFolder = $this->rootFolder->get($this->sharerUserId . '/files/folder1');
		$sharedFolderId = $sharedFolder->getId();

		$result = $this->environment->getResourceFromId($sharedFolderId);
		$this->assertEquals($sharedFolder->getId(), $result->getId());

		$sharedFolder->delete();
	}

	public function testGetSharedNodeAsATokenUser() {
		$token = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$sharedFolder = $this->rootFolder->get($this->sharerUserId . '/files/folder1');
		$sharedFolderId = $sharedFolder->getId();

		$result = $this->environment->getSharedNode();

		$this->assertEquals($sharedFolderId, $result->getId());
	}

	/**
	 * We can't get the folder if we're given a file token
	 *
	 * @expects EnvironmentException
	 */
	public function testGetSharedNodeAsATokenUserWhenGivenFileToken() {
		$token = $this->prepareFileToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$this->environment->getSharedNode();
	}

	public function testGetVirtualRootFolderAsALoggedInUser() {
		$this->environment = $this->setUserBasedEnv();

		$result = $this->environment->getVirtualRootFolder();
		$userFolderId = $this->server->getUserFolder($this->userId)
									 ->getId();

		$this->assertEquals($userFolderId, $result->getId());
	}

	public function testGetVirtualRootFolderAsATokenUser() {
		$token = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result = $this->environment->getVirtualRootFolder();
		$userFolderId = $this->server->getUserFolder($this->sharerUserId);
		$sharedFolder = $userFolderId->get('folder1')
									 ->getId();

		$this->assertEquals($sharedFolder, $result->getId());
	}

	public function testGetUserIdAsALoggedInUser() {
		$this->environment = $this->setUserBasedEnv();

		$result = $this->environment->getUserId();

		$this->assertEquals($this->userId, $result);
	}

	public function testGetUserIdAsATokenUser() {
		$token = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result = $this->environment->getUserId();
		$this->assertEquals($this->sharerUserId, $result);
	}
}
