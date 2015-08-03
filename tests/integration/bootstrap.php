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

namespace OCA\GalleryPlus\Tests\Integration;

require_once __DIR__ . '/../../../../lib/base.php';

use Test\TestCase;

use OCP\Files\IRootFolder;
use OCP\Files\Folder;

use OCP\AppFramework\IAppContainer;

use \OCA\GalleryPlus\AppInfo\Application;


/**
 * Class GalleryIntegrationTest
 *
 * @package OCA\GalleryPlus\Tests\Integration
 */
class GalleryIntegrationTest extends TestCase {

	/** @var string */
	protected $appName = 'galleryplus';
	/** @var IAppContainer */
	protected $container;
	/** @var string */
	protected $userId = 'test';
	/** @var string */
	protected $userPassword = 'test';
	/** @var IRootFolder */
	protected $rootFolder;
	/** @var Folder|null */
	protected $userFolder;


	protected function setUp() {
		parent::setUp();

		$app = new Application($this->appName);
		$this->container = $app->getContainer();

		$this->rootFolder = $this->container->getServer()
											->getRootFolder();

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

	protected function setUserFolder() {
		$this->userFolder = $this->rootFolder->newFolder('/' . $this->userId);
		$this->userFolder->newFolder('/files');
	}

}