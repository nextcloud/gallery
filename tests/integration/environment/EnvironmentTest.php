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

use OCP\Files\Folder;
use OCP\Files\Node;

use OCA\GalleryPlus\Tests\Integration\GalleryIntegrationTest;

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
		list($token) = $this->prepareFolderToken();
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
		list($token) = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$sharedFolder = $this->rootFolder->get($this->sharerUserId . '/files/folder1');
		$sharedFolderId = $sharedFolder->getId();

		$result = $this->environment->getResourceFromId($sharedFolderId);
		$this->assertEquals($sharedFolder->getId(), $result->getId());

		$sharedFolder->delete();
	}

	public function testGetSharedNodeAsATokenUser() {
		list($token) = $this->prepareFolderToken();
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
		list($token) = $this->prepareFileToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$this->environment->getSharedNode();
	}

	public function testGetVirtualRootFolderAsALoggedInUser() {
		$this->environment = $this->setUserBasedEnv();

		$result = $this->environment->getVirtualRootFolder();
		/*$userFolderId = $this->server->getUserFolder($this->userId)
									 ->getId();*/
		$userFolderId = $this->userFolder->getId();

		$this->assertEquals($userFolderId, $result->getId());
	}

	public function testGetVirtualRootFolderAsATokenUser() {
		list($token) = $this->prepareFolderToken();
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
		list($token) = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result = $this->environment->getUserId();
		$this->assertEquals($this->sharerUserId, $result);
	}

	public function testGetSharedFolderNameAsATokenUser() {
		list($token, $sharedFolder) = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result = $this->environment->getSharedFolderName();
		$this->assertEquals($sharedFolder->getName(), $result);
	}

	public function testGetSharePasswordAsATokenUser() {
		/** @type Node $sharedFolder */
		list($token) = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result = $this->environment->getSharePassword();

		// The password is defined in the bootstrap
		$this->assertTrue(
			$this->server->getHasher()
						 ->verify('sh@red p@ssw0rd', $result)
		);
	}

	public function testGetPathFromVirtualRootAsALoggedInUser() {
		$this->environment = $this->setUserBasedEnv();
		$result =
			$this->environment->getPathFromVirtualRoot(
				$this->userFolder->get('folder1/folder1.1/file1.1')
			);

		$this->assertEquals('folder1/folder1.1/file1.1', $result);
	}

	public function testGetPathFromVirtualRootAsATokenUser() {
		/** @type Folder $sharedFolder */
		list($token, $sharedFolder) = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result =
			$this->environment->getPathFromVirtualRoot($sharedFolder->get('folder1.1/file1.1'));

		$this->assertEquals('folder1.1/file1.1', $result);
	}

	public function testGetPathFromUserFolderAsALoggedInUser() {
		$this->environment = $this->setUserBasedEnv();

		$result =
			$this->environment->getPathFromUserFolder(
				$this->userFolder->get('folder1/folder1.1/file1.1')
			);

		$this->assertEquals('folder1/folder1.1/file1.1', $result);
	}

	public function testGetPathFromUserFolderAsATokenUser() {
		/** @type Folder $sharedFolder */
		list($token, $sharedFolder) = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result =
			$this->environment->getPathFromUserFolder($sharedFolder->get('folder1.1/file1.1'));

		$this->assertEquals('folder1/folder1.1/file1.1', $result);
	}

	public function testGetDisplayNameAsATokenUser() {
		list($token) = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result = $this->environment->getDisplayName();

		$this->assertEquals('UberTester (' . $this->sharerUserId . ')', $result);
	}

	public function testGetNodeFromVirtualRootAsALoggedInUser() {
		$this->environment = $this->setUserBasedEnv();
		$result = $this->environment->getNodeFromVirtualRoot('folder1/folder1.1/file1.1');

		$file = $this->userFolder->get('folder1/folder1.1/file1.1');

		$this->assertEquals($file, $result);
	}

	public function testGetNodeFromVirtualRootAsATokenUser() {
		/** @type Folder $sharedFolder */
		list($token, $sharedFolder) = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result = $this->environment->getNodeFromVirtualRoot('folder1.1/file1.1');

		$file = $sharedFolder->get('folder1.1/file1.1');

		$this->assertEquals($file, $result);
	}

	public function testGetNodeFromUserFolderAsALoggedInUser() {
		$this->environment = $this->setUserBasedEnv();
		$result = $this->environment->getNodeFromUserFolder('folder1/folder1.1/file1.1');

		$file = $this->userFolder->get('folder1/folder1.1/file1.1');

		$this->assertEquals($file, $result);
	}

	public function testGetNodeFromUserFolderAsATokenUser() {
		/** @type Folder $sharedFolder */
		list($token, $sharedFolder) = $this->prepareFolderToken();
		$this->environment = $this->setTokenBasedEnv($token);

		$result = $this->environment->getNodeFromUserFolder('folder1/folder1.1/file1.1');

		$file = $sharedFolder->get('folder1.1/file1.1');

		$this->assertEquals($file, $result);
	}
}
