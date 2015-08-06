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

namespace OCA\Gallery\Tests\Integration;

require_once __DIR__ . '/../../../../lib/base.php';

use Test\TestCase;

use OCP\Share;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;
use OCP\Files\File;
use OCP\IServerContainer;

use OCP\AppFramework\IAppContainer;

use OCA\Gallery\AppInfo\Application;
use OCA\Gallery\Environment\Environment;


/**
 * Class GalleryIntegrationTest
 *
 * @package OCA\Gallery\Tests\Integration
 */
class GalleryIntegrationTest extends TestCase {

	/** @var string */
	protected $appName = 'gallery';
	/** @var IAppContainer */
	protected $container;
	/** @var IServerContainer */
	protected $server;
	/** @var string */
	protected $userId = 'test';
	/** @var string */
	protected $userPassword = '1234';
	/** @var string */
	protected $sharerUserId = 'sharer';
	/** @var string */
	protected $sharerPassword = '5678';
	/** @var IRootFolder */
	protected $rootFolder;
	/** @var Folder|null */
	protected $userFolder;
	/** @var Environment */
	protected $environment;


	protected function setUp() {
		parent::setUp();

		$app = new Application($this->appName);
		$this->container = $app->getContainer();
		$this->server = $this->container->getServer();
		$this->rootFolder = $this->server->getRootFolder();

		/**
		 * Logging hooks are missing at the moment, so we need to disable encryption
		 *
		 * @link https://github.com/owncloud/core/issues/18085#issuecomment-128093797
		 */
		$this->server->getConfig()
					 ->setAppValue('core', 'encryption_enabled', 'no');

		// This is because the filesystem is not properly cleaned up sometimes
		$this->server->getAppManager()
					 ->disableApp('files_trashbin');

	}

	public function tearDown() {
		$this->logout();

		parent::tearDown();
	}

	/**
	 * Sets up a logged in user
	 *
	 * We're using \OC::$server so that we can create a user BEFORE launching the app if need be
	 *
	 * @param $user
	 * @param $password
	 *
	 * @throws \Exception
	 * @throws \OC\User\LoginException
	 */
	protected function setupUser($user, $password) {
		$userManager = \OC::$server->getUserManager();

		if ($userManager->userExists($user)) {
			$userManager->get($user)
						->delete();
		}

		$userManager->createUser($user, $password);

		$this->loginAsUser($user);
	}

	/**
	 * @param $userId
	 *
	 * @return Folder
	 */
	protected function setUserFolder($userId) {
		$userFolder = $this->rootFolder->newFolder('/' . $userId);
		$userFolder->newFolder('/files');

		return $userFolder;
	}

	/**
	 * @return mixed
	 */
	protected function setUserBasedEnv() {
		$this->setupUser($this->userId, $this->userPassword);
		$this->createEnv($this->userId, $this->userPassword);
		$environment = $this->instantiateEnvironment($this->userId);

		$environment->setStandardEnv();

		return $environment;
	}

	/**
	 * @param string $token
	 *
	 * @return mixed
	 */
	protected function setTokenBasedEnv($token) {
		$environment = $this->instantiateEnvironment(null);
		$linkItem = Share::getShareByToken($token, false);

		$environment->setTokenBasedEnv($linkItem);

		return $environment;
	}

	/**
	 * Creates a token for a file
	 *
	 * @return array<bool|string|File>
	 */
	protected function prepareFileToken() {
		$sharedFolder = $this->createEnv($this->sharerUserId, $this->sharerPassword);

		/** @type File $sharedFile */
		$sharedFile = $sharedFolder->get('file1');
		$sharedFile->putContent('foobar');

		$fileInfo = $sharedFile->getFileInfo();

		$token = $this->getToken('file', $fileInfo['fileid']);

		$this->logout();

		return [$token, $sharedFile];
	}

	/**
	 * Creates a token for a folder
	 *
	 * @return array<bool|string|Folder>
	 */
	protected function prepareFolderToken() {
		$sharedFolder = $this->createEnv($this->sharerUserId, $this->sharerPassword);
		$fileInfo = $sharedFolder->getFileInfo();

		$token = $this->getToken('folder', $fileInfo['fileid']);

		$this->logout();

		return [$token, $sharedFolder];
	}

	/**
	 * @param $userId
	 *
	 * @return mixed
	 */
	private function instantiateEnvironment($userId) {
		$this->userFolder = $this->server->getUserFolder($userId);

		$this->container->registerService(
			'UserId', function ($c) {
			return $this->userId;
		}
		);

		$this->container->registerService(
			'userFolder', function ($c) {
			return $this->userFolder;
		}
		);

		return $this->container->query(
			'OCA\Gallery\Environment\Environment'
		);
	}

	/**
	 * Creates a small folder/file hierarchy and returns the top folder
	 *
	 * @param string $userId
	 * @param string $userPassword
	 *
	 * @return Folder
	 */
	private function createEnv($userId, $userPassword) {
		$this->setupUser($userId, $userPassword);
		$userFolder = $this->server->getUserFolder($userId);
		$user = $this->server->getUserManager()
							 ->get($userId);
		$user->setDisplayName('UberTester (' . $userId . ')');

		$folder1 = $userFolder->newFolder('folder1');
		$folder1->newFile('file1');
		$subFolder = $folder1->newFolder('folder1.1');
		$subFolder->newFile('file1.1');

		return $folder1;
	}

	/**
	 * @param $nodeType
	 * @param $nodeId
	 *
	 * @return bool|string
	 */
	private function getToken($nodeType, $nodeId) {
		// We need to make sure sharing via link is enabled
		$this->server->getConfig()
					 ->setAppValue('core', 'shareapi_allow_links', 'yes');

		return Share::shareItem(
			$nodeType, $nodeId, \OCP\Share::SHARE_TYPE_LINK, 'sh@red p@ssw0rd',
			\OCP\Constants::PERMISSION_ALL
		);
	}

}
