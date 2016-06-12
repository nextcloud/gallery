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

namespace Helper;

use Codeception\TestCase;

use OCP\Share;
use OCP\Files\IRootFolder;
use OCP\Files\Folder;
use OCP\Files\File;
use OCP\IServerContainer;
use OCP\IUserManager;

use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;

/**
 * Class DataSetup
 *
 * @package OCA\Gallery\Tests\Helper
 */
class DataSetup extends \Codeception\Module {

	/** @var CoreTestCase */
	public $coreTestCase;
	/** @var array<string> */
	public $mediaTypes;
	/** @var array<string> */
	public $extraMediaTypes;
	/** @var string */
	public $userId = 'tester';
	/** @var string */
	public $userPassword = 'RyLqt22pVjKT&Vk0L#mv*gzzB@ZlejHy';
	/** @var string */
	public $sharerUserId = 'sharer';
	/** @var string */
	public $sharerPassword = '3SEg$h4neVI0BiPMxDC*s&I1yNMkEFNv';
	/**
	 * The file structure for the tests
	 *
	 * shared1 is the shared folder
	 * testimage-wide.png is the shared file
	 *
	 * @todo Don't depend on ImageMagick
	 *
	 * @var array
	 */
	public $filesHierarchy = [
		'testimage.jpg',
		'animated.gif',
		'testimage-corrupt.jpg',
		'font.ttf',
		'testimagelarge.svg',
		'testimage.eps',
		'testimage.gif',
		'folder1' => [
			'testimage.jpg',
			'testimage-wide.png',
			'shared1' => [
				'testimage.eps',
				'testimagelarge.svg',
				'testimage.gif',
				'shared1.1' => [
					'testimage.png',
				]
			]
		],
		'folder2' => [
			'testimage.jpg',
			'testimage.png',
			'testimagelarge.svg',
		],
		'folder3' => [],
		'folder4' => [ // Folder will be hidden in Gallery
					   'testimage.jpg',
					   'testimage-wide.png',
					   '.nomedia',
		]
	];
	/** @var Folder */
	public $sharedFolder;
	/** @var string */
	public $sharedFolderName = 'shared1';
	/** @var string */
	public $sharedFolderToken;
	/** @var string */
	public $passwordForFolderShare = 'p@ssw0rd4sh@re5';
	/** @var File */
	public $sharedFile;
	/** @var string */
	public $sharedFileName = 'testimage-wide.png';
	/** @var string */
	public $sharedFileToken;
	/** @var File */
	public $privateFile;
	/** @var string */
	public $privateFileName = 'font.ttf';

	/** @var IAppContainer */
	private $container;
	/** @var IServerContainer */
	private $server;
	/** @var IUserManager */
	private $userManager;
	/** @var IRootFolder */
	private $rootFolder;
	/** @var array */
	private $oldPreviewProviders;

	/**
	 * Called before executing all tests in a suite
	 *
	 * If the module is injected, you need to use _initialize()
	 *
	 * @param array $settings
	 */
	public function _beforeSuite($settings = []) {
		$this->coreTestCase = new CoreTestCase();

		$app = new App('gallery-test-setup');
		$this->container = $app->getContainer();
		$this->server = $this->container->getServer();
		$this->rootFolder = $this->server->getRootFolder();
		$this->userManager = $this->server->getUserManager();

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

		$this->setPreviewProviders();

		$this->createTestSetup($this->userId, $this->userPassword);
		$this->createTestSetup($this->sharerUserId, $this->sharerPassword);
		$this->createShares();
	}

	/**
	 * Called after all tests in a suite are completed
	 *
	 * We clean up
	 */
	public function _afterSuite() {
		$this->deleteTestUsers();

		$this->server->getConfig()
					 ->setSystemValue('enabledPreviewProviders', $this->oldPreviewProviders);
	}

	/**
	 * Called when a test fails
	 *
	 * This is called after every test, not after a suite run
	 *
	 * @param TestCase $test
	 * @param $fail
	 */
	public function _failed(\Codeception\TestCase $test, $fail) {

	}

	/**
	 * Returns a list of ids available in the given folder
	 *
	 * @param string $folderPath
	 *
	 * @return array<string,int|string>
	 */
	public function getFilesDataForFolder($folderPath) {
		$userFolder = $this->server->getUserFolder($this->userId);
		/** @type Folder $folder */
		$folder = $userFolder->get($folderPath);
		$content = $folder->getDirectoryListing();
		$data = [];

		foreach ($content as $node) {
			$nodeType = $node->getType();
			$mimeType = $node->getMimetype();
			if ($nodeType === 'file' && in_array($mimeType, $this->mediaTypes)) {
				$name = $node->getName();
				$data[$name] = [
					'id'        => $node->getId(),
					'mediatype' => $mimeType,
					'etag'      => $node->getEtag(),
				];
			}
		}

		return $data;
	}

	public function createBrokenConfig() {
		$userFolder = $this->server->getUserFolder($this->userId);
		$this->addFile($userFolder, 'broken-gallery.cnf', 'gallery.cnf');
	}

	public function createConfigWithBom() {
		$userFolder = $this->server->getUserFolder($this->userId);
		$this->addFile($userFolder, 'bom-gallery.cnf', 'gallery.cnf');
	}


	public function emptyConfig() {
		$userFolder = $this->server->getUserFolder($this->userId);
		$this->addFile($userFolder, 'empty-gallery.cnf', 'gallery.cnf');
	}

	public function restoreValidConfig() {
		$userFolder = $this->server->getUserFolder($this->userId);
		$this->addFile($userFolder, $this->userId . '-gallery.cnf', 'gallery.cnf');
	}

	/**
	 * Creates a test environment for a given user
	 *
	 * @param string $userId
	 * @param string $userPassword
	 */
	private function createTestSetup($userId, $userPassword) {
		// Create user
		$this->deleteUser($userId);
		$this->createUser($userId, $userPassword);

		$this->coreTestCase->loginAsUser($userId);

		// Create folders and files
		$this->createSampleData($userId, $this->filesHierarchy);

		$this->coreTestCase->logoutUser();
	}

	/**
	 * Adds the previews providers we need for the test, to the config
	 */
	private function setPreviewProviders() {
		$this->oldPreviewProviders = $this->server->getConfig()
												  ->getSystemValue('enabledPreviewProviders');

		// We need to enable the providers we're going to use in the tests
		$providers = [
			'OC\\Preview\\JPEG',
			'OC\\Preview\\PNG',
			'OC\\Preview\\GIF',
			'OC\\Preview\\Postscript',
			'OC\\Preview\\Font'
		];
		$this->server->getConfig()
					 ->setSystemValue('enabledPreviewProviders', $providers);

		$this->mediaTypes = [
			'image/jpeg',
			'image/png',
			'image/gif',
			'application/postscript'
		];

		$this->extraMediaTypes = [
			'application/font-sfnt',
			'application/x-font',
		];
	}

	/**
	 * Deletes the 2 users created for the tests
	 */
	private function deleteTestUsers() {
		$this->deleteUser($this->userId);
		$this->deleteUser($this->sharerUserId);
	}

	/**
	 * Creates a local user
	 *
	 * @param $userId
	 * @param $password
	 */
	private function createUser($userId, $password) {
		$user = $this->userManager->createUser($userId, $password);
		$user->setDisplayName('Gallery Tester (' . $userId . ')');
	}

	/**
	 * Removes a user from the instance
	 *
	 * @param string $user
	 */
	private function deleteUser($user) {
		if ($this->userManager->userExists($user)) {
			$this->userManager->get($user)
							  ->delete();
		}
	}

	/**
	 * Creates a small folder/file hierarchy
	 *
	 * @param string $userId
	 * @param array $structure
	 */
	private function createSampleData($userId, $structure) {
		$userFolder = $this->server->getUserFolder($userId);

		$this->createStructure($userFolder, $structure);

		// Add configuration. This will break if the config filename or the userId is changed
		$this->addFile($userFolder, $userId . '-gallery.cnf', 'gallery.cnf');
		$this->addFile($userFolder->get('folder2'), 'sorting-gallery.cnf', 'gallery.cnf');
	}

	/**
	 * Creates the folder structure and adds test images
	 *
	 * This could be refined by adding tests to make sure we have access to the files
	 *
	 * @param Folder $baseFolder
	 * @param array <string|int,string|array> $structure
	 */
	private function createStructure($baseFolder, $structure) {
		foreach ($structure as $key => $value) {
			if (is_array($value)) {
				$subFolder = $baseFolder->newFolder($key);

				if ($key === $this->sharedFolderName) {
					$this->sharedFolder = $subFolder;
				}

				if (!empty($value)) {
					$this->createStructure($subFolder, $value);
				}
			} else {
				$file = $this->addFile($baseFolder, $value, $value);

				if ($value === $this->sharedFileName) {
					$this->sharedFile = $file;
				}
				if ($value === $this->privateFileName) {
					$this->privateFile = $file;
				}
			}
		}
	}

	/**
	 * Copies the content of one file to another
	 *
	 * @param Folder $folder
	 * @param string $sourceName
	 * @param string $destinationName
	 *
	 * @return File
	 */
	private function addFile($folder, $sourceName, $destinationName) {
		$file = $folder->newFile($destinationName);
		$fileData = file_get_contents(__DIR__ . '/../../_data/' . $sourceName);
		$file->putContent($fileData);

		return $file;
	}

	/**
	 * Shares the file and folder, both publicly and with the tester
	 *
	 * Warning - File operations don't work properly in the test environment provided by core as
	 * the cache is not updated and the scanner does not work. Do not attempt to rename or move
	 * files
	 */
	private function createShares() {
		$this->coreTestCase->loginAsUser($this->sharerUserId);

		// Warning - File first or an error will be triggered because the file belongs to the folder
		$this->sharedFileToken = $this->createShare('file');
		$this->sharedFolderToken = $this->createShare('folder');
		$this->createShare('file', $this->userId);
		$this->createShare('folder', $this->userId);

		$this->coreTestCase->logoutUser();
	}

	/**
	 * Creates a share of either a file or a folder, either publicly or with the tester
	 *
	 * @param string $nodeType
	 * @param string|null $shareWith
	 *
	 * @return bool|string
	 */
	protected function createShare($nodeType, $shareWith = null) {
		/**
		 * Pick the file or the folder
		 */
		if ($nodeType === 'file') {
			$sharedNode = $this->sharedFile;
		} else {
			$sharedNode = $this->sharedFolder;
		}
		$fileInfo = $sharedNode->getFileInfo();

		/**
		 * Decide which type of share it is
		 */
		$shareType = \OCP\Share::SHARE_TYPE_USER;
		if ($shareWith === null) {
			// We need to make sure sharing via link is enabled
			$this->server->getConfig()
						 ->setAppValue('core', 'shareapi_allow_links', 'yes');

			// Only password protect the folders
			if ($nodeType === 'folder') {
				$shareWith = $this->passwordForFolderShare;
			}
			$shareType = \OCP\Share::SHARE_TYPE_LINK;
		}

		/**
		 * Share and generate the token if it's a public share
		 */

		return Share::shareItem(
			$nodeType, $fileInfo['fileid'], $shareType, $shareWith,
			\OCP\Constants::PERMISSION_ALL
		);
	}

}
