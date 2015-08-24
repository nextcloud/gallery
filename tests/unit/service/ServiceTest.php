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

use OCP\ILogger;

use OCA\GalleryPlus\Environment\Environment;

/**
 * Class ServiceTest
 *
 * @package OCA\GalleryPlus\Controller
 */
abstract class ServiceTest extends \Test\TestCase {

	/** @var string */
	protected $appName = 'galleryplus';
	/** @var Environment */
	protected $environment;
	/** @var ILogger */
	protected $logger;

	/**
	 * Test set up
	 */
	protected function setUp() {
		parent::setUp();

		$this->environment = $this->getMockBuilder('\OCA\GalleryPlus\Environment\Environment')
								  ->disableOriginalConstructor()
								  ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
	}

	/**
	 * Mocks Environment->getResourceFromId
	 *
	 * Needs to pass a mock of a File or Folder
	 *
	 * @param int $fileId
	 * @param object|\PHPUnit_Framework_MockObject_MockObject|bool $answer
	 */
	protected function mockGetResourceFromId($fileId, $answer) {
		$this->environment->expects($this->once())
						  ->method('getResourceFromId')
						  ->with($this->equalTo($fileId))
						  ->willReturn($answer);
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
	 *
	 * @return object|\PHPUnit_Framework_MockObject_MockObject
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

	public function mockJpgFile($fileId) {
		$file = $this->mockFile($fileId);
		$this->mockJpgFileMethods($file);

		return $file;
	}

	public function mockSvgFile($fileId) {
		$file = $this->mockFile($fileId);
		$this->mockSvgFileMethods($file);

		return $file;
	}


	private function mockJpgFileMethods($file) {
		$filename = 'testimage.jpg';
		$file->method('getContent')
			 ->willReturn(file_get_contents(\OC::$SERVERROOT . '/tests/data/' . $filename));
		$file->method('getName')
			 ->willReturn($filename);
		$file->method('getMimeType')
			 ->willReturn('image/jpeg');

		return $file;
	}

	private function mockSvgFileMethods($file) {
		$filename = 'testimagelarge.svg';
		$file->method('getContent')
			 ->willReturn(file_get_contents(\OC::$SERVERROOT . '/tests/data/' . $filename));
		$file->method('getName')
			 ->willReturn($filename);
		$file->method('getMimeType')
			 ->willReturn('image/svg+xml');

		return $file;
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
