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

namespace OCA\Gallery\Environment;

use OCP\Files\Folder;
use OCP\Files\Node;

use OCA\Gallery\Tests\Integration\GalleryIntegrationTest;

/**
 * Class EnvironmentSetupTest
 *
 * @package OCA\Gallery\Tests\Integration
 */
class EnvironmentSetupTest extends GalleryIntegrationTest {

	/**
	 * Tests is setting up the environment using a normal user works
	 */
	public function testSetStandardEnv() {
		$this->setUserBasedEnv();
	}

	/**
	 * Tests if setting up the environment using a token works
	 */
	public function testSetTokenBasedEnv() {
		$this->setTokenBasedEnv($this->sharedFolderToken);
	}

	public function testGetResourceFromIdAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();

		$sharedFolderId = $this->sharedFolder->getId();
		$result = $environment->getResourceFromId($sharedFolderId);

		$this->assertEquals($this->sharedFolder->getId(), $result->getId());
	}

	public function testGetResourceFromIdAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$sharedFolderId = $this->sharedFolder->getId();
		$result = $environment->getResourceFromId($sharedFolderId);

		$this->assertEquals($this->sharedFolder->getId(), $result->getId());
	}

	public function testGetSharedNodeAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$sharedFolderId = $this->sharedFolder->getId();
		$result = $environment->getSharedNode();

		$this->assertEquals($sharedFolderId, $result->getId());
	}

	public function testGetSharedNodeAsATokenUserWhenGivenFileToken() {
		$environment = $this->setTokenBasedEnv($this->sharedFileToken);

		$sharedFileId = $this->sharedFile->getId();
		$result = $environment->getSharedNode();

		$this->assertEquals($sharedFileId, $result->getId());
	}

	/**
	 * We can't get the virtual root if we're given a file token
	 *
	 * @expectedException \OCA\Gallery\Environment\NotFoundEnvException
	 */
	public function testGetVirtualRootFolderAsATokenUserWhenGivenFileToken() {
		$environment = $this->setTokenBasedEnv($this->sharedFileToken);

		$environment->getVirtualRootFolder();
	}

	public function testGetVirtualRootFolderAsALoggedInUser() {
		/** @type Environment $environment */
		$environment = $this->setUserBasedEnv();

		$userFolderId = $this->userFolder->getId();
		$result = $environment->getVirtualRootFolder();

		$this->assertEquals($userFolderId, $result->getId());
	}

	public function testGetVirtualRootFolderAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result = $environment->getVirtualRootFolder();

		$this->assertEquals($this->sharedFolder->getId(), $result->getId());
	}

	public function testGetUserIdAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();

		$result = $environment->getUserId();

		$this->assertEquals($this->userId, $result);
	}

	public function testGetUserIdAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result = $environment->getUserId();
		$this->assertEquals($this->sharerUserId, $result);
	}

	public function testGetSharedFolderNameAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result = $environment->getSharedFolderName();
		$this->assertEquals($this->sharedFolder->getName(), $result);
	}

	public function testGetSharePasswordAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result = $environment->getSharePassword();

		// The password is defined in the bootstrap
		$this->assertTrue(
			$this->server->getHasher()
						 ->verify($this->passwordForFolderShare, $result)
		);
	}

	/**
	 * You can pick any folder from $dataSetup->fileHierarchy
	 */
	public function testGetPathToFileFromVirtualRootAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();

		$path = 'folder1/' . $this->sharedFolderName . '/testimage.gif';
		$result = $environment->getPathFromVirtualRoot($this->userFolder->get($path));

		$this->assertEquals($path, $result);
	}

	public function testGetPathToFileFromVirtualRootAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$path = 'shared1.1/testimage.png';
		$result = $environment->getPathFromVirtualRoot($this->sharedFolder->get($path));

		$this->assertEquals($path, $result);
	}

	public function testGetPathToFolderFromVirtualRootAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();

		$path = 'folder1/' . $this->sharedFolderName;
		$result = $environment->getPathFromVirtualRoot($this->userFolder->get($path));

		$this->assertEquals($path, $result);
	}

	public function testGetPathFromUserFolderAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();

		$path = 'shared1.1/testimage.png';
		$result = $environment->getPathFromUserFolder(
			$this->userFolder->get($this->sharedFolderName . '/' . $path)
		);

		$this->assertEquals($this->sharedFolderName . '/' . $path, $result);
	}

	public function testGetPathFromUserFolderAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$path = 'shared1.1/testimage.png';
		$result = $environment->getPathFromUserFolder($this->sharedFolder->get($path));

		$this->assertEquals('folder1/' . $this->sharedFolderName . '/' . $path, $result);
	}

	public function testGetDisplayNameAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result = $environment->getDisplayName();

		$this->assertEquals('Gallery Tester (' . $this->sharerUserId . ')', $result);
	}

	public function testGetNodeFromVirtualRootAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();

		$path = 'shared1.1/testimage.png';
		$result = $environment->getNodeFromVirtualRoot($this->sharedFolderName . '/' . $path);

		$file = $this->userFolder->get($this->sharedFolderName . '/' . $path);

		$this->assertEquals($file, $result);
	}

	public function testGetNodeFromVirtualRootAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$path = 'shared1.1/testimage.png';
		$result = $environment->getNodeFromVirtualRoot($path);

		$file = $this->sharedFolder->get($path);

		$this->assertEquals($file, $result);
	}

	public function testGetNodeFromUserFolderAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();

		$path = 'shared1.1/testimage.png';
		$result = $environment->getNodeFromUserFolder($this->sharedFolderName . '/' . $path);

		$file = $this->userFolder->get($this->sharedFolderName . '/' . $path);

		$this->assertEquals($file, $result);
	}

	public function testGetNodeFromUserFolderAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$path = 'shared1.1/testimage.png';
		$result =
			$environment->getNodeFromUserFolder('folder1/' . $this->sharedFolderName . '/' . $path);

		$file = $this->sharedFolder->get($path);

		$this->assertEquals($file, $result);
	}
}
