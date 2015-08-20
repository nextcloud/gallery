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

namespace OCA\Gallery\Controller;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ILogger;

use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\Gallery\AppInfo\Application;
use OCA\Gallery\Http\ImageResponse;
use OCA\Gallery\Service\SearchFolderService;
use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\SearchMediaService;
use OCA\Gallery\Service\DownloadService;

/**
 * Class FilesControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class FilesControllerTest extends \Test\TestCase {

	/** @var IAppContainer */
	protected $container;
	/** @var string */
	protected $appName = 'gallery';
	/** @var IRequest */
	protected $request;
	/** @var ConfigController */
	protected $controller;
	/** @var IURLGenerator */
	protected $urlGenerator;
	/** @var SearchFolderService */
	protected $searchFolderService;
	/** @var ConfigService */
	protected $configService;
	/** @var SearchMediaService */
	protected $searchMediaService;
	/** @var DownloadService */
	protected $downloadService;
	/** @var ILogger */
	protected $logger;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$app = new Application;
		$this->container = $app->getContainer();
		$this->container['UserFolder'] = $this->getMockBuilder('OCP\Files\Folder')
											  ->disableOriginalConstructor()
											  ->getMock();
		$this->request = $this->getMockBuilder('\OCP\IRequest')
							  ->disableOriginalConstructor()
							  ->getMock();
		$this->urlGenerator = $this->getMockBuilder('\OCP\IURLGenerator')
								   ->disableOriginalConstructor()
								   ->getMock();
		$this->searchFolderService =
			$this->getMockBuilder('\OCA\Gallery\Service\SearchFolderService')
				 ->disableOriginalConstructor()
				 ->getMock();
		$this->configService = $this->getMockBuilder('\OCA\Gallery\Service\ConfigService')
									->disableOriginalConstructor()
									->getMock();
		$this->searchMediaService = $this->getMockBuilder('\OCA\Gallery\Service\SearchMediaService')
										 ->disableOriginalConstructor()
										 ->getMock();
		$this->downloadService = $this->getMockBuilder('\OCA\Gallery\Service\DownloadService')
									  ->disableOriginalConstructor()
									  ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->controller = new FilesController(
			$this->appName,
			$this->request,
			$this->urlGenerator,
			$this->searchFolderService,
			$this->configService,
			$this->searchMediaService,
			$this->downloadService,
			$this->logger
		);
	}

	public function testDownload() {
		$fileId = 1234;
		$filename = null;

		$download = $this->mockGetDownload($fileId, $filename);

		/** @type ImageResponse $response */
		$response = $this->controller->download($fileId, $filename);

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
		$this->assertEquals(
			$download['mimetype'] . '; charset=utf-8', $response->getHeaders()['Content-type']
		);
		$this->assertEquals($download['preview'], $response->render());
	}

	public function testDownloadWithWrongId() {
		$fileId = 99999;
		$filename = null;

		$this->mockGetResourceFromId($fileId, false);

		$redirect = new RedirectResponse(
			$this->urlGenerator->linkToRoute($this->appName . '.page.error_page')
		);

		$response = $this->controller->download($fileId, $filename);

		$this->assertEquals($redirect->getRedirectURL(), $response->getRedirectURL());
	}


	/**
	 * Mocks Files->getDownload
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param string|null $filename
	 *
	 * @return array
	 */
	private function mockGetDownload($fileId, $filename) {
		$file = $this->mockFile($fileId);

		$this->mockGetResourceFromId($fileId, $file);

		$download = $this->mockDownloadData($file, $filename);

		$this->mockDownloadFile($file, $download);

		return $download;
	}

	/**
	 * Mocks OCP\Files\File
	 *
	 * Contains a JPG
	 *
	 * @param int $fileId
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

	/**
	 * Mocks DownloadService->getResourceFromId
	 *
	 * @param int $fileId
	 * @param object|\PHPUnit_Framework_MockObject_MockObject|bool $answer
	 */
	private function mockGetResourceFromId($fileId, $answer) {
		$this->downloadService->expects($this->once())
							  ->method('getResourceFromId')
							  ->with($this->equalTo($fileId))
							  ->willReturn($answer);
	}

	/**
	 * @param object|\PHPUnit_Framework_MockObject_MockObject $file
	 * @param $filename
	 *
	 * @return array
	 */
	private function mockDownloadData($file, $filename) {
		$download = [
			'preview'  => $file->getContent(),
			'mimetype' => $file->getMimeType(),
		];

		if ($download) {
			if (is_null($filename)) {
				$filename = $file->getName();
			}
			$download['name'] = $filename;
		}

		return $download;
	}

	/**
	 * Mocks DownloadService->downloadFile
	 *
	 * @param object|\PHPUnit_Framework_MockObject_MockObject $file
	 * @param array $download
	 */
	private function mockDownloadFile($file, $download) {
		$this->downloadService->expects($this->once())
							  ->method('downloadFile')
							  ->with($this->equalTo($file))
							  ->willReturn($download);
	}
}
