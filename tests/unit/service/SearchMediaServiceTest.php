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

namespace OCA\Gallery\Service;

use OCP\Files\Folder;

/**
 * Class SearchMediaServiceTest
 *
 * @package OCA\Gallery\Controller
 */
class SearchMediaServiceTest extends \Test\GalleryUnitTest {

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

	public function providesTopFolderData() {
		$isReadable = true;
		$mounted = false;
		$mount = null;
		$query = '.nomedia';
		$queryResult = false;

		$folder1 = $this->mockFolder(
			'home::user', 545454,
			[
				$this->mockJpgFile(11111),
				$this->mockJpgFile(22222),
				$this->mockJpgFile(33333)
			],
			$isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder2 = $this->mockFolder(
			'home::user', 767676,
			[
				$this->mockJpgFile(44444),
				$this->mockJpgFile(55555),
				$this->mockJpgFile(66666)
			],
			$isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder33 = $this->mockFolder(
			'home::user', 545454,
			[
				$this->mockJpgFile(11111),
				$this->mockJpgFile(22222),
				$this->mockJpgFile(33333)
			],
			$isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder3 = $this->mockFolder(
			'home::user', 101010, [$folder33], $isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder33b = $this->mockFolder(
			'home::user', 545454,
			[
				$this->mockJpgFile(11111),
				$this->mockJpgFile(22222),
				$this->mockJpgFile(33333)
			],
			$isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder3b = $this->mockFolder(
			'home::user', 101010, [$folder33b], $isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder44 = $this->mockFolder(
			'home::user', 545454,
			[
				$this->mockJpgFile(11111),
				$this->mockJpgFile(22222),
				$this->mockJpgFile(33333)
			],
			$isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder45 = $this->mockFolder(
			'home::user', 767676,
			[
				$this->mockJpgFile(44444),
				$this->mockJpgFile(55555),
				$this->mockJpgFile(66666)
			],
			$isReadable, $mounted, $mount, $query, $queryResult
		);
		$folder4 = $this->mockFolder(
			'home::user', 101010,
			[
				$folder44,
				$folder45
			],
			$isReadable, $mounted, $mount, $query,
			$queryResult
		);
		$folder5 = $this->mockFolder(
			'home::user', 987234,
			[
				$this->mockJpgFile(998877),
				$this->mockJpgFile(998876),
				$this->mockNoMediaFile(998875)
			],
			$isReadable, $mounted, $mount, '.nomedia', true
		);
		$folder6 = $this->mockFolder(
			'webdav::user@domain.com/dav', 545454, [$this->mockJpgFile(11111)], $isReadable, true,
			$mount, $query, $queryResult
		);
		$folder7 = $this->mockFolder(
			'home::user', 545454,
			[
				$this->mockJpgFile(1),
				$this->mockJpgFile(2),
				$this->mockJpgFile(3),
				$this->mockJpgFile(4),
				$this->mockJpgFile(5),
			],
			$isReadable, $mounted, $mount, $query, $queryResult
		);

		// 2 folders and 3 files, everything is reachable
		$config1 = [
			$folder1,
			$folder2,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)

		];
		$folder1Path = 'holidays';
		$folder2Path = 'athletics';
		$topFolder1 = $this->mockFolder(
			'home::user', 909090, $config1, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$map1 = [
			[$topFolder1, ''],
			[$folder1, $folder1Path],
			[$folder2, $folder2Path],
		];
		// 2 deepfolder and 3 files. Should return all the files
		$config2 = [
			$folder3,
			$folder3b,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)

		];
		$folder3Path = 'ninja';
		$folder3bPath = 'racing';
		$folder33Path = 'ninja/mma';
		$folder33bPath = 'racing/f1';
		$topFolder2 = $this->mockFolder(
			'home::user', 909090, $config2, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$map2 = [
			[$topFolder2, ''],
			[$folder3, $folder3Path],
			[$folder3b, $folder3bPath],
			[$folder33, $folder33Path],
			[$folder33b, $folder33bPath],
		];
		// 1 deepfolder (with 2 sub-folders) and 3 files. Should return the files and the content of
		// 1 folder because we stop looking after we've found at least 1 picture in a sub-sub-folder
		$config3 = [
			$folder4,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)
		];
		$folder4Path = 'trips';
		$folder44Path = 'trips/NSA';
		$folder45Path = 'trips/GCHQ';
		$topFolder3 = $this->mockFolder(
			'home::user', 909090, $config3, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$map3 = [
			[$topFolder3, ''],
			[$folder4, $folder4Path],
			[$folder44, $folder44Path],
			[$folder45, $folder45Path],
		];
		// 1 blacklisted folder and 3 files
		$config4 = [
			$folder5,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)

		];
		$folder5Path = 'food';
		$topFolder4 = $this->mockFolder(
			'home::user', 909090, $config4, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$map4 = [
			[$topFolder4, ''],
			[$folder5, $folder5Path],
		];
		// 1 standard folder, 1 external share (ignored) and 3 files
		$config5 = [
			$folder1,
			$folder6,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)

		];
		$folder6Path = 'pets';
		$topFolder5 = $this->mockFolder(
			'home::user', 909090, $config5, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$map5 = [
			[$topFolder5, ''],
			[$folder1, $folder1Path],
			[$folder6, $folder6Path],
		];
		// 1 standard folder (3), 1 deep folder and 3 files
		$config6 = [
			$folder1,
			$folder7,
			$this->mockJpgFile(77777),
			$this->mockJpgFile(88888),
			$this->mockJpgFile(99999)
		];
		$folder7Path = 'missions';
		$topFolder6 = $this->mockFolder(
			'home::user', 909090, $config6, $isReadable, $mounted, $mount, $query, $queryResult
		);
		$map6 = [
			[$topFolder6, ''],
			[$folder1, $folder1Path],
			[$folder7, $folder7Path],
		];

		return [
			[$topFolder1, $map1, 9, 3],
			[$topFolder2, $map2, 9, 5],
			[$topFolder3, $map3, 6, 3],
			[$topFolder4, $map4, 3, 1],
			[$topFolder5, $map5, 6, 2],
			[$topFolder6, $map6, 10, 3]
		];
	}

	/**
	 * @dataProvider providesTopFolderData
	 *
	 * @param Folder $topFolder
	 * @param array $map
	 * @param int $images
	 * @param int $albums
	 *
	 * @internal param int $result
	 */
	public function testGetMediaFiles($topFolder, $map, $images, $albums) {
		$supportedMediaTypes = [
			'image/png',
			'image/jpeg',
			'image/gif'
		];
		$features = [];
		$this->mockNodePath($map);
		$response = $this->service->getMediaFiles($topFolder, $supportedMediaTypes, $features);

		$this->assertSame($images, sizeof($response[0]));
		$this->assertSame($albums, sizeof($response[1]));
	}

	public function providesFolderWithFilesData() {
		$isReadable = true;
		$mounted = false;
		$mount = null;
		$query = '.nomedia';
		$queryResult = false;

		// The order doesn't matter, but it's easier to compare
		// if that matches the order in FileService::getNodeData
		$topFolder1Data = [
			'path'           => '',
			'nodeid'         => 12121,
			'mtime'          => null,
			'etag'           => "5d739f2c156c38b8db8c48603c11cd6c",
			'size'           => 88888,
			'sharedwithuser' => false,
			'owner'          => [],
			'permissions'    => 31,
			'freespace'      => 7777777,
		];
		$topFolder2Data = $topFolder1Data;
		$file1Data = [
			'path'           => 'rootfile.jpg',
			'nodeid'         => 11111,
			'mtime'          => null,
			'etag'           => "8603c11cd6c5d739f2c156c38b8db8c4",
			'size'           => 1024,
			'sharedwithuser' => false,
			'owner'          => [],
			'permissions'    => 31,
			'mimetype'       => 'image/jpeg',
		];
		$file1 = $this->mockJpgFile(
			$file1Data['nodeid'], 'home::user', $isReadable, $file1Data['path'],
			$file1Data['etag'], $file1Data['size'], $file1Data['sharedwithuser'], null,
			$file1Data['permissions']
		);

		$ownerUid = 909090;
		$ownerName = 'San Akinamoura';
		$owner = $this->mockOwner($ownerUid, $ownerName);
		$file2Data = [
			'path'           => 'holidays/everest.jpg',
			'nodeid'         => 22222,
			'mtime'          => null,
			'etag'           => "739f2c156c38b88603c11cd6c5ddb8c4",
			'size'           => 102410241024,
			'sharedwithuser' => true,
			'owner'          => [
				'uid'         => $ownerUid,
				'displayname' => $ownerName
			],
			'permissions'    => 31,
			'mimetype'       => 'image/jpeg',
		];
		$file2 = $this->mockJpgFile(
			$file2Data['nodeid'], 'webdav::user@domain.com/dav', $isReadable, $file2Data['path'],
			$file2Data['etag'], $file2Data['size'], $file2Data['sharedwithuser'], $owner,
			$file2Data['permissions']
		);

		$album1Data = [
			'path'           => 'holidays',
			'nodeid'         => 454545,
			'mtime'          => null,
			'etag'           => "56c38b8db8c486035d739f2c1c11cd6c",
			'size'           => 33333,
			'sharedwithuser' => false,
			'owner'          => [],
			'permissions'    => 11,
			'freespace'      => 576576576576,
		];
		$album1 = $this->mockFolder(
			'home::user',
			$album1Data['nodeid'], [
				$file2
			],
			$isReadable, $mounted, $mount, $query, $queryResult, $album1Data['sharedwithuser'],
			$album1Data['etag'], $album1Data['size'], $album1Data['path'], null,
			$album1Data['permissions'], $album1Data['freespace']
		);

		$topFolder1 = $this->mockFolder(
			'home::user',
			$topFolder1Data['nodeid'],
			[
				$file1,
				$album1
			],
			$isReadable, $mounted, $mount, $query, $queryResult, $topFolder1Data['sharedwithuser'],
			$topFolder1Data['etag'], $topFolder1Data['size'], $topFolder1Data['path'], null,
			$topFolder1Data['permissions'], $topFolder1Data['freespace']
		);
		$albumIgnored = $this->mockFolder(
			'home::user',
			$album1Data['nodeid'], [
				$file2
			],
			$isReadable, $mounted, $mount, '.nomedia', true, $album1Data['sharedwithuser'],
			$album1Data['etag'], $album1Data['size'], $album1Data['path'], null,
			$album1Data['permissions'], $album1Data['freespace']
		);
		$topFolder2 = $this->mockFolder(
			'home::user',
			$topFolder1Data['nodeid'],
			[
				$file1,
				$albumIgnored
			],
			$isReadable, $mounted, $mount, $query, $queryResult, $topFolder1Data['sharedwithuser'],
			$topFolder1Data['etag'], $topFolder1Data['size'], $topFolder1Data['path'], null,
			$topFolder1Data['permissions'], $topFolder1Data['freespace']
		);

		$map1 = [
			[$topFolder1, $topFolder1Data['path']],
			[$file1, $file1Data['path']],
			[$file2, $file2Data['path']],
			[$album1, $album1Data['path']],
		];
		$map2 = [
			[$file1, $file1Data['path']],
			[$topFolder2, $topFolder2Data['path']],
		];

		return [
			[
				$topFolder1,
				$map1, [
					[
						$file1Data,
						$file2Data
					],
					[
						$topFolder1Data['path'] => $topFolder1Data,
						$album1Data['path']     => $album1Data,
					]
				]
			],
			[
				$topFolder2,
				$map2, [
					[$file1Data],
					[$topFolder2Data['path'] => $topFolder2Data,]
				]
			]
		];
	}

	/**
	 * @dataProvider providesFolderWithFilesData
	 *
	 * @param Folder $topFolder
	 * @param array $map
	 * @param array $result
	 */
	public function testPropertiesOfGetMediaFiles($topFolder, $map, $result) {
		$supportedMediaTypes = [
			'image/png',
			'image/jpeg',
			'image/gif'
		];
		$features = [];

		$this->mockNodePath($map);
		$response = $this->service->getMediaFiles($topFolder, $supportedMediaTypes, $features);

		$this->assertSame($result, $response);
	}

	/**
	 * @expectedException \OCA\Gallery\Service\NotFoundServiceException
	 */
	public function testGetResourceFromIdWithUnreadableFile() {
		$fileId = 99999;
		$storageId = 'home::user';
		$isReadable = false;
		$file = $this->mockFile($fileId, $storageId, $isReadable);
		$this->mockGetResourceFromId($this->environment, $fileId, $file);

		$this->service->getResourceFromId($fileId);
	}

	/**
	 * @param int $uid
	 * @param string $displayName
	 *
	 * @return mixed|object|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockOwner($uid, $displayName) {
		$owner = $this->getMockBuilder('OCP\IUser')
					  ->disableOriginalConstructor()
					  ->getMock();
		$owner->method('getUID')
			  ->willReturn($uid);
		$owner->method('getDisplayName')
			  ->willReturn($displayName);

		return $owner;
	}

	/**
	 * Mocks Environment->getPathFromVirtualRoot
	 *
	 * This is needed for files and albums to find the path to the root and is required to build
	 * the hierarchy of folders
	 *
	 * @param $map
	 */
	private function mockNodePath($map) {
		$this->environment->method('getPathFromVirtualRoot')
						  ->will(
							  $this->returnValueMap($map)
						  );
	}

}
