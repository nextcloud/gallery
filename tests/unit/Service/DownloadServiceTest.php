<?php
/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Tests\Service;

use OCP\Files\File;

use OCA\Gallery\Service\Base64Encode;
use OCA\Gallery\Service\DownloadService;


/**
 * Class DownloadServiceTest
 *
 * @package OCA\Gallery\Tests\Service
 */
class DownloadServiceTest extends \OCA\Gallery\Tests\GalleryUnitTest {

	use Base64Encode;

	/** @var DownloadService */
	protected $service;

	/**
	 * Test set up
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->service = new DownloadService (
			$this->appName,
			$this->environment,
			$this->logger
		);
	}

	public function testDownloadRawFile() {
		/** @type File $file */
		$file = $this->mockJpgFile(12345);

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
		$file = $this->mockJpgFile(12345);

		$download = [
			'preview'  => $this->encode($file->getContent()),
			'mimetype' => $file->getMimeType()
		];

		$downloadResponse = $this->service->downloadFile($file, true);

		$this->assertSame($download['mimetype'], $downloadResponse['mimetype']);
		$this->assertSame($download['preview'], $downloadResponse['preview']);
	}

	/**
	 * @expectedException \OCA\Gallery\Service\NotFoundServiceException
	 */
	public function testDownloadNonExistentFile() {
		$file = $this->mockBadFile();

		$this->service->downloadFile($file);
	}

}
