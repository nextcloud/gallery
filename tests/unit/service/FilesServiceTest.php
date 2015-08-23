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
include_once 'ServiceTest.php';

/**
 * Class FilesServiceTest
 *
 * @package OCA\GalleryPlus\Controller
 */
abstract class FilesServiceTest extends ServiceTest {

	/**
	 * Mocks OCP\Files\File
	 *
	 * Duplicate of PreviewControllerTest->mockFile
	 *
	 * Contains a JPG
	 *
	 * @param int $fileId
	 * @param string $storageId
	 *
	 * @return object|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockFile($fileId, $storageId = 'home::user', $isReadable = true) {
		$storage = $this->mockGetStorage($storageId);
		$file = $this->getMockBuilder('OCP\Files\File')
					 ->disableOriginalConstructor()
					 ->getMock();
		$file->method('getId')
			 ->willReturn($fileId);
		$file->method('getType')
			 ->willReturn('file');
		$file->method('getContent')
			 ->willReturn(file_get_contents(\OC::$SERVERROOT . '/tests/data/testimage.jpg'));
		$file->method('getName')
			 ->willReturn('testimage.jpg');
		$file->method('getMimeType')
			 ->willReturn('image/jpeg');
		$file->method('getStorage')
			 ->willReturn($storage);
		$file->method('isReadable')
			 ->willReturn($isReadable);

		return $file;
	}

	protected function mockBadFile() {
		$file = $this->getMockBuilder('OCP\Files\File')
					 ->disableOriginalConstructor()
					 ->getMock();
		$file->method('getContent')
			 ->willThrowException(new ServiceException("Can't read file"));

		return $file;
	}

	protected function mockGetFolder(
		$storageId,
		$nodeId,
		$files,
		$isReadable = true,
		$mounted = false,
		$mount = null,
		$query = '',
		$queryResult = false
	) {
		$storage = $this->mockGetStorage($storageId);
		$folder = $this->getMockBuilder('OCP\Files\Folder')
					   ->disableOriginalConstructor()
					   ->getMock();
		$folder->method('getType')
			   ->willReturn('dir');
		$folder->method('getId')
			   ->willReturn($nodeId);
		$folder->method('getDirectoryListing')
			   ->willReturn($files);
		$folder->method('getStorage')
			   ->willReturn($storage);
		$folder->method('isReadable')
			   ->willReturn($isReadable);
		$folder->method('isMounted')
			   ->willReturn($mounted);
		$folder->method('getMountPoint')
			   ->willReturn($mount);
		$folder->method('nodeExists')
			   ->with($query)
			   ->willReturn($queryResult);

		return $folder;
	}

	protected function mockGetStorage($storageId) {
		$storage = $this->getMockBuilder('OCP\Files\Storage')
						->disableOriginalConstructor()
						->getMock();
		$storage->method('getId')
				->willReturn($storageId);

		return $storage;
	}

}
