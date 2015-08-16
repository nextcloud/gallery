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
 * Class EnvironmentTest
 *
 * @package OCA\Gallery\Tests\Integration
 */
class EnvironmentTest extends GalleryIntegrationTest {

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

		$this->sharedFolderId = $this->sharedFolder->getId();
		$result = $environment->getResourceFromId($this->sharedFolderId);

		$this->assertEquals($this->sharedFolder->getId(), $result->getId());
	}

	public function testGetResourceFromIdAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$this->sharedFolderId = $this->sharedFolder->getId();
		$result = $environment->getResourceFromId($this->sharedFolderId);

		$this->assertEquals($this->sharedFolder->getId(), $result->getId());
	}

	public function testGetSharedNodeAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$this->sharedFolderId = $this->sharedFolder->getId();
		$result = $environment->getSharedNode();

		$this->assertEquals($this->sharedFolderId, $result->getId());
	}

	/**
	 * We can't get the folder if we're given a file token
	 *
	 * @expects EnvironmentException
	 */
	public function testGetSharedNodeAsATokenUserWhenGivenFileToken() {
		$environment = $this->setTokenBasedEnv($this->sharedFileToken);

		$environment->getSharedNode();
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
						 ->verify($this->passwordForShares, $result)
		);
	}

	/**
	 * You can pick any folder from $dataSetup->fileHierarchy
	 */
	public function testGetPathFromVirtualRootAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();
		$result =
			$environment->getPathFromVirtualRoot(
				$this->userFolder->get($this->sharedFolderName . '/folder1.1/testimage.gif')
			);

		$this->assertEquals($this->sharedFolderName . '/folder1.1/testimage.gif', $result);
	}

	public function testGetPathFromVirtualRootAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result =
			$environment->getPathFromVirtualRoot(
				$this->sharedFolder->get('folder1.1/testimage.gif')
			);

		$this->assertEquals('folder1.1/testimage.gif', $result);
	}

	public function testGetPathFromUserFolderAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();

		$result =
			$environment->getPathFromUserFolder(
				$this->userFolder->get($this->sharedFolderName . '/folder1.1/testimage.gif')
			);

		$this->assertEquals($this->sharedFolderName . '/folder1.1/testimage.gif', $result);
	}

	public function testGetPathFromUserFolderAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result =
			$environment->getPathFromUserFolder(
				$this->sharedFolder->get('folder1.1/testimage.gif')
			);

		$this->assertEquals($this->sharedFolderName . '/folder1.1/testimage.gif', $result);
	}

	public function testGetDisplayNameAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result = $environment->getDisplayName();

		$this->assertEquals('Gallery Tester (' . $this->sharerUserId . ')', $result);
	}

	public function testGetNodeFromVirtualRootAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();
		$result =
			$environment->getNodeFromVirtualRoot(
				$this->sharedFolderName . '/folder1.1/testimage.gif'
			);

		$file = $this->userFolder->get($this->sharedFolderName . '/folder1.1/testimage.gif');

		$this->assertEquals($file, $result);
	}

	public function testGetNodeFromVirtualRootAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result = $environment->getNodeFromVirtualRoot('folder1.1/testimage.gif');

		$file = $this->sharedFolder->get('folder1.1/testimage.gif');

		$this->assertEquals($file, $result);
	}

	public function testGetNodeFromUserFolderAsALoggedInUser() {
		$environment = $this->setUserBasedEnv();
		$result =
			$environment->getNodeFromUserFolder(
				$this->sharedFolderName . '/folder1.1/testimage.gif'
			);

		$file = $this->userFolder->get($this->sharedFolderName . '/folder1.1/testimage.gif');

		$this->assertEquals($file, $result);
	}

	public function testGetNodeFromUserFolderAsATokenUser() {
		$environment = $this->setTokenBasedEnv($this->sharedFolderToken);

		$result =
			$environment->getNodeFromUserFolder(
				$this->sharedFolderName . '/folder1.1/testimage.gif'
			);

		$file = $this->sharedFolder->get('folder1.1/testimage.gif');

		$this->assertEquals($file, $result);
	}
}
