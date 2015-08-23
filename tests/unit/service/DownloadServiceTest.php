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
use OCP\Files\File;

use OCA\GalleryPlus\Environment\Environment;

/**
 * Class DownloadServiceTest
 *
 * @package OCA\GalleryPlus\Controller
 */
class DownloadServiceTest extends \Test\TestCase {

	use Base64Encode;

	/** @var DownloadService */
	protected $service;
	/** @var string */
	protected $appName = 'gallery';
	/** @var Environment */
	private $environment;
	/** @var ILogger */
	protected $logger;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$this->environment = $this->getMockBuilder('\OCA\GalleryPlus\Environment\Environment')
								  ->disableOriginalConstructor()
								  ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->service = new DownloadService (
			$this->appName,
			$this->environment,
			$this->logger
		);
	}

	public function testDownloadRawFile() {
		/** @type File $file */
		$file = $this->mockFile(12345);

		$download = [
			'preview'  => $file->getContent(),
			'mimetype' => $file->getMimeType()
		];

		$downloadResponse = $this->service->downloadFile($file);

		$this->assertSame($download['mimetype'], $downloadResponse['mimetype']);
		$this->assertSame($download['preview'], $downloadResponse['preview']);
	}

	public function testDownloadBase64EncodedFile() {
		/** @type File $file */
		$file = $this->mockFile(12345);

		$download = [
			'preview'  => $this->encode($file->getContent()),
			'mimetype' => $file->getMimeType()
		];

		$downloadResponse = $this->service->downloadFile($file, true);

		$this->assertSame($download['mimetype'], $downloadResponse['mimetype']);
		$this->assertSame($download['preview'], $downloadResponse['preview']);
	}

	public function testDownloadNonExistentFile() {
		$file = $this->mockBadFile(12345);

		$downloadResponse = $this->service->downloadFile($file);

		$this->assertFalse($downloadResponse);
	}

	/**
	 * Mocks OCP\Files\File
	 *
	 * Duplicate of PreviewControllerTest->mockFile
	 *
	 * Contains a JPG
	 *
	 * @param $fileId
	 *
	 * @return object|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockFile($fileId) {
		$file = $this->getMockBuilder('OCP\Files\File')
					 ->disableOriginalConstructor()
					 ->getMock();
		$file->method('getId')
			 ->willReturn($fileId);
		$file->method('getContent')
			 ->willReturn(file_get_contents(\OC::$SERVERROOT . '/tests/data/testimage.jpg'));
		$file->method('getName')
			 ->willReturn('testimage.jpg');
		$file->method('getMimeType')
			 ->willReturn('image/jpeg');

		return $file;
	}

	private function mockBadFile() {
		$file = $this->getMockBuilder('OCP\Files\File')
					 ->disableOriginalConstructor()
					 ->getMock();
		$file->method('getContent')
			 ->willThrowException(new ServiceException("Can't read file"));

		return $file;
	}

}
