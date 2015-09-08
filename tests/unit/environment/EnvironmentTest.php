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

use OCP\IUserManager;
use OCP\ILogger;
use OCP\Files\IRootFolder;

use OCP\AppFramework\IAppContainer;

use OCA\Gallery\AppInfo\Application;

/**
 * Class Environment
 *
 * @package OCA\Gallery\Environment
 */
class EnvironmentTest extends \Test\TestCase {

	/** @var IAppContainer */
	private $container;
	/** @var string */
	private $appName = 'gallery';
	/** @var IRootFolder */
	private $rootFolder;
	/** @var IUserManager */
	private $userManager;
	/** @var ILogger */
	private $logger;
	/** @var Environment */
	private $environment;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$app = new Application();
		$this->container = $app->getContainer();
		$this->userManager = $this->getMockBuilder('\OCP\IUserManager')
								  ->disableOriginalConstructor()
								  ->getMock();
		$this->rootFolder = $this->getMockBuilder('OCP\Files\IRootFolder')
									  ->disableOriginalConstructor()
									  ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
	}

	/**
	 * @expectedException \OCA\Gallery\Environment\NotFoundEnvException
	 */
	public function testGetNodeFromUserFolderWithNullUser() {
		$userId = 'user';
		$userFolder = null;
		$this->mockSetEnvironment($userId, $userFolder);
		$this->environment->getNodeFromUserFolder('anypath');

	}

	/**
	 * @expectedException \OCA\Gallery\Environment\NotFoundEnvException
	 */
	public function testGetDisplayName() {
		$userId = null;
		$userFolder = null;
		$this->mockSetEnvironment($userId, $userFolder);
		$this->environment->getDisplayName();
	}

	/**
	 * @param $userId
	 * @param $userFolder
	 */
	private function mockSetEnvironment($userId, $userFolder) {
		$this->environment = new Environment(
			$this->appName,
			$userId,
			$userFolder,
			$this->userManager,
			$this->rootFolder,
			$this->logger
		);
	}

}
