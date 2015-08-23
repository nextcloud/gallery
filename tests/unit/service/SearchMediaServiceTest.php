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
namespace OCA\GalleryPlus\Service;
include_once 'FilesServiceTest.php';

/**
 * Class SearchMediaServiceTest
 *
 * @package OCA\GalleryPlus\Controller
 */
class SearchMediaServiceTest extends FilesServiceTest {

	/** @var SearchMediaService */
	protected $service;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$this->service = new SearchMediaService (
			$this->appName,
			$this->environment,
			$this->logger
		);
	}

	public function testIsPreviewAvailable() {
		$file = $this->mockBadFile();

		$result = self::invokePrivate($this->service, 'isPreviewAvailable', [$file]);

		$this->assertFalse($result);
	}

	public function testGetMediaFilesWithUnavailableFolder() {
		$isReadable = false;
		$files = [];
		$topFolder = $this->mockGetFolder(
			'home::user', 545454, $files, $isReadable
		);
		$supportedMediaTypes = [];
		$features = [];
		$response = $this->service->getMediaFiles($topFolder, $supportedMediaTypes, $features);

		$this->assertSame([], $response);
	}

	public function providesTopFolderData() {
		$isReadable = true;
		$mounted = false;
		$mount = null;
		$query = '.nomedia';
		$queryResult = false;

		$folder1 = $this->mockGetFolder(
			'home::user', 545454, [
			$this->mockFile(11111),
			$this->mockFile(22222),
			$this->mockFile(33333)
		], $isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder2 = $this->mockGetFolder(
			'home::user', 767676, [
			$this->mockFile(44444),
			$this->mockFile(55555),
			$this->mockFile(66666)
		], $isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder3 = $this->mockGetFolder(
			'home::user', 10101, [$folder1], $isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder4 = $this->mockGetFolder(
			'home::user', 10101, [$folder1, $folder2], $isReadable, $mounted, $mount, $query,
			$queryResult
		);

		// 2 folders and 3 files, everything is reachable
		$config1 = [
			$folder1,
			$folder2,
			$this->mockFile(77777),
			$this->mockFile(88888),
			$this->mockFile(99999)

		];
		// 2 deepfolder and 3 files. Should return the all the files
		$config2 = [
			$folder3,
			$folder3,
			$this->mockFile(77777),
			$this->mockFile(88888),
			$this->mockFile(99999)

		];
		// 1 deepfolder (with 2 sub-folders) and 3 files. Should return the files and the content of 1 folder
		$config3 = [
			$folder4,
			$this->mockFile(77777),
			$this->mockFile(88888),
			$this->mockFile(99999)
		];
		$topFolder1 = $this->mockGetFolder(
			'home::user', 909090, $config1, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$topFolder2 = $this->mockGetFolder(
			'home::user', 909090, $config2, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$topFolder3 = $this->mockGetFolder(
			'home::user', 909090, $config3, $isReadable, $mounted, $mount, $query, $queryResult
		);

		return [
			[$topFolder1, 9],
			[$topFolder2, 9],
			[$topFolder3, 6]
		];
	}

	/**
	 * @dataProvider providesTopFolderData
	 *
	 * @param array $topFolder
	 * @param int $result
	 */
	public function testGetMediaFiles($topFolder, $result) {
		$supportedMediaTypes = [
			'image/png',
			'image/jpeg',
			'image/gif'
		];
		$features = [];

		$response = $this->service->getMediaFiles($topFolder, $supportedMediaTypes, $features);

		$this->assertSame($result, sizeof($response));
	}

}
