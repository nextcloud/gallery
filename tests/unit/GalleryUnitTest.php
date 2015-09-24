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

namespace Test;

use OCP\ILogger;
use OCP\Files\File;
use OCP\Files\Folder;

use OCA\Gallery\Environment\Environment;
use OCA\Gallery\Service\ServiceException;

/**
 * Class GalleryUnitTest
 *
 * @package OCA\Gallery
 */
abstract class GalleryUnitTest extends \Test\TestCase {

	/** @var string */
	protected $appName = 'gallery';
	/** @var Environment */
	protected $environment;
	/** @var ILogger */
	protected $logger;

	/**
	 * Test set up
	 */
	protected function setUp() {
		parent::setUp();

		$this->environment = $this->getMockBuilder('\OCA\Gallery\Environment\Environment')
								  ->disableOriginalConstructor()
								  ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
	}

	/**
	 * Mocks Object->getResourceFromId
	 *
	 * Needs to pass a mock of a File or Folder
	 *
	 * @param object $mockedObject
	 * @param int $fileId
	 * @param File|Folder $answer
	 */
	protected function mockGetResourceFromId($mockedObject, $fileId, $answer) {
		$mockedObject->expects($this->once())
					 ->method('getResourceFromId')
					 ->with($this->equalTo($fileId))
					 ->willReturn($answer);
	}

	/**
	 * Mocks Object->getResourceFromId with a bad Id
	 *
	 * Needs to pass a mock of a File or Folder
	 *
	 * @param \PHPUnit_Framework_MockObject_MockObject $mockedObject
	 * @param int $fileId
	 * @param \Exception $exception
	 */
	protected function mockGetResourceFromIdWithBadFile($mockedObject, $fileId, $exception) {
		$mockedObject->expects($this->once())
					 ->method('getResourceFromId')
					 ->with($this->equalTo($fileId))
					 ->willThrowException($exception);
	}

	/**
	 * Mocks OCP\Files\File
	 *
	 * Duplicate of PreviewControllerTest->mockFile
	 *
	 * Contains a JPG
	 *
	 * @param int $fileId
	 * @param string $storageId
	 * @param bool $isReadable
	 * @param string $path
	 *
	 * @return \PHPUnit_Framework_MockObject_MockObject
	 */
	protected function mockFile($fileId, $storageId = 'home::user', $isReadable = true, $path = ''
	) {
		$storage = $this->mockGetStorage($storageId);
		$file = $this->getMockBuilder('OCP\Files\File')
					 ->disableOriginalConstructor()
					 ->getMock();
		$file->method('getId')
			 ->willReturn($fileId);
		$file->method('getType')
			 ->willReturn('file');
		$file->method('getStorage')
			 ->willReturn($storage);
		$file->method('isReadable')
			 ->willReturn($isReadable);
		$file->method('getPath')
			 ->willReturn($path);

		return $file;
	}

	protected function mockJpgFile($fileId) {
		$file = $this->mockFile($fileId);
		$this->mockJpgFileMethods($file);

		return $file;
	}

	protected function mockSvgFile($fileId) {
		$file = $this->mockFile($fileId);
		$this->mockSvgFileMethods($file);

		return $file;
	}

	protected function mockAnimatedGifFile($fileId) {
		$file = $this->mockFile($fileId);
		$this->mockAnimatedGifFileMethods($file);

		return $file;
	}

	protected function mockNoMediaFile($fileId) {
		$file = $this->mockFile($fileId);
		$this->mockNoMediaFileMethods($file);

		return $file;
	}

	private function mockJpgFileMethods($file) {
		$filename = 'testimage.jpg';
		$file->method('getContent')
			 ->willReturn(file_get_contents(__DIR__ . '/../_data/' . $filename));
		$file->method('getName')
			 ->willReturn($filename);
		$file->method('getMimeType')
			 ->willReturn('image/jpeg');
	}

	private function mockSvgFileMethods($file) {
		$filename = 'testimagelarge.svg';
		$file->method('getContent')
			 ->willReturn(file_get_contents(__DIR__ . '/../_data/' . $filename));
		$file->method('getName')
			 ->willReturn($filename);
		$file->method('getMimeType')
			 ->willReturn('image/svg+xml');
	}

	private function mockAnimatedGifFileMethods($file) {
		$filename = 'animated.gif';
		$file->method('getContent')
			 ->willReturn(file_get_contents(__DIR__ . '/../_data/' . $filename));
		$file->method('getName')
			 ->willReturn($filename);
		$file->method('getMimeType')
			 ->willReturn('image/gif');
		$file->method('fopen')
			 ->with('rb')
			 ->willReturn(fopen(__DIR__ . '/../_data/' . $filename, 'rb'));;
	}

	private function mockNoMediaFileMethods($file) {
		$filename = '.nomedia';
		$file->method('getContent')
			 ->willReturn(file_get_contents(__DIR__ . '/../_data/' . $filename));
		$file->method('getName')
			 ->willReturn($filename);
		$file->method('getMimeType')
			 ->willReturn('image/jpeg');
	}

	protected function mockBadFile() {
		$exception = new ServiceException("Can't read file");
		$file = $this->getMockBuilder('OCP\Files\File')
					 ->disableOriginalConstructor()
					 ->getMock();
		$file->method('getId')
			 ->willThrowException($exception);
		$file->method('getType')
			 ->willThrowException($exception);
		$file->method('getPath')
			 ->willThrowException($exception);
		$file->method('getContent')
			 ->willThrowException($exception);

		return $file;
	}

	protected function mockFolder(
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

	protected function mockGetFileNodeFromVirtualRoot($location, $file) {
		$this->environment->expects($this->any())
						  ->method('getNodeFromVirtualRoot')
						  ->with(
							  $location
						  )
						  ->willReturn($file);
	}

	protected function mockGetPathFromVirtualRoot($node, $path) {
		$this->environment->expects($this->any())
						  ->method('getPathFromVirtualRoot')
						  ->with(
							  $node
						  )
						  ->willReturn($path);
	}

}
