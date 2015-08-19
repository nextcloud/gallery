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

use Helper\CoreTestCase;

use OCP\Share;
use OCP\Files\Node;
use OCP\Files\Folder;
use OCP\Files\File;
use OCP\IServerContainer;

use OCP\AppFramework\IAppContainer;

use OCA\Gallery\AppInfo\Application as Gallery;
use OCA\Gallery\Environment\Environment;


/**
 * Class GalleryIntegrationTest
 *
 * @package OCA\Gallery\Tests\Integration
 */
abstract class GalleryIntegrationTest extends \Codeception\TestCase\Test {

	/** @var CoreTestCase */
	private $coreTestCase;
	/** @var IAppContainer */
	protected $container;
	/** @var IServerContainer */
	protected $server;
	/** @var Folder|null */
	protected $userFolder;
	/** @var string */
	protected $userId;
	/** @var string */
	protected $sharerUserId;
	/** @var string */
	public $sharedFolderToken;
	/** @var string */
	public $sharedFileToken;
	/** @var string */
	public $passwordForFolderShare;
	/** @var Folder */
	protected $sharedFolder;
	/** @var string */
	protected $sharedFolderName;
	/** @var File */
	protected $sharedFile;
	/** @var string */
	protected $sharedFileName;
	/** @var Environment */
	protected $environment;

	/**
	 * Injects objects we need
	 *
	 * @param CoreTestCase $coreTestCase
	 */
	protected function _inject(CoreTestCase $coreTestCase) {
		$this->coreTestCase = $coreTestCase;
	}

	/**
	 * Runs before each test (public method)
	 *
	 * It's important to recreate the app for every test, as if the user had just logged in
	 *
	 * @fixme Or just create the app once for each type of env and run all tests. For that to work,
	 *     I think I would need to switch to Cepts
	 */
	protected function _before() {
		$this->coreTestCase->setUp();

		$app = new Gallery();
		$this->container = $app->getContainer();
		$this->server = $this->container->getServer();

		$setupData = $this->getModule('\Helper\DataSetup');
		$this->userId = $setupData->userId;
		$this->sharerUserId = $setupData->sharerUserId;
		$this->sharedFolder = $setupData->sharedFolder;
		$this->sharedFolderName = $this->sharedFolder->getName();
		$this->sharedFile = $setupData->sharedFile;
		$this->sharedFileName = $this->sharedFile->getName();
		$this->sharedFolderToken = $setupData->sharedFolderToken;
		$this->sharedFileToken = $setupData->sharedFileToken;
		$this->passwordForFolderShare = $setupData->passwordForFolderShare;
	}

	protected function _after() {
		$this->coreTestCase->logoutUser();
		$this->coreTestCase->tearDown();
	}

	/**
	 * Creates an environment for a logged in user
	 *
	 * @return Environment
	 */
	protected function setUserBasedEnv() {
		$this->coreTestCase->loginAsUser($this->userId);
		$this->userFolder = $this->server->getUserFolder($this->userId);
		$environment = $this->instantiateEnvironment();
		$environment->setStandardEnv();

		return $environment;
	}

	/**
	 * Creates an environment based on a token
	 *
	 * @param string $token
	 *
	 * @return Environment
	 */
	protected function setTokenBasedEnv($token) {
		$linkItem = Share::getShareByToken($token, false);
		$environment = $this->instantiateEnvironment();
		$environment->setTokenBasedEnv($linkItem);

		return $environment;
	}

	/**
	 * Instantiates the environment
	 *
	 * @return Environment
	 */
	private function instantiateEnvironment() {
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

}
