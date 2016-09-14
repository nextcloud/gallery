<?php
/**
 * Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2016
 */

namespace OCA\Gallery\Controller;

use OCA\Gallery\Service\ServiceException;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\Files\File;
use OCP\Files\Folder;
use OCP\ILogger;

use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\JSONResponse;

use OCA\Gallery\AppInfo\Application;
use OCA\Gallery\Http\ImageResponse;
use OCA\Gallery\Service\SearchFolderService;
use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\SearchMediaService;
use OCA\Gallery\Service\DownloadService;
use OCA\Gallery\Service\NotFoundServiceException;

/**
 * Class FilesControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class FilesControllerTest extends \Test\GalleryUnitTest {

	use PathManipulation;

	/** @var IAppContainer */
	protected $container;
	/** @var string */
	protected $appName = 'gallery';
	/** @var IRequest */
	protected $request;
	/** @var FilesController */
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

	/**
	 * @return array
	 */
	public function providesTestDownloadData() {
		return [
			[1234, $this->mockJpgFile(1234), 'image/jpeg'],
			[4567, $this->mockSvgFile(4567), 'text/plain']
		];
	}

	/**
	 * @dataProvider providesTestDownloadData
	 *
	 * @param int $fileId
	 * @param File $file
	 * @param string $expectedMimeType
	 */
	public function testDownload($fileId, $file, $expectedMimeType) {
		$filename = null;
		$download = $this->mockGetDownload($fileId, $file, $filename);

		/** @type ImageResponse $response */
		$response = $this->controller->download($fileId, $filename);

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());
		$this->assertEquals(
			$expectedMimeType . '; charset=utf-8', $response->getHeaders()['Content-type']
		);
		$this->assertEquals($download['preview'], $response->render());
	}

	public function testDownloadWithWrongId() {
		$fileId = 99999;
		$filename = null;
		$status = Http::STATUS_NOT_FOUND;

		$exception = new NotFoundServiceException('Not found');
		$this->mockGetFileWithBadFile($this->downloadService, $fileId, $exception);

		$redirectUrl = '/index.php/app/error';
		$this->mockUrlToErrorPage($status, $redirectUrl);

		/** @type RedirectResponse $response */
		$response = $this->controller->download($fileId, $filename);

		$this->assertEquals($redirectUrl, $response->getRedirectURL());
		$this->assertEquals(Http::STATUS_SEE_OTHER, $response->getStatus());
		$this->assertEquals(
			$exception->getMessage(), $response->getCookies()['galleryErrorMessage']['value']
		);
	}

	public function providesGetFilesWithWorkingSetupData() {
		$location = 'folder';
		$folderPathFromRoot = 'user/files/' . $location;
		$etag = 1111222233334444;


		$folderId = 9876;
		$folderPermissions = 31;
		$folderEtag = 9999888877776666;
		$folderIsShared = false;
		$files = [
			['path' => $folderPathFromRoot . '/deep/path.png'],
			['path' => $folderPathFromRoot . '/testimage.png'],
		];
		$albums = [
			['path' => $folderPathFromRoot . '/deep'],
		];
		$albumConfig = [
			'information' => [],
			'sorting'     => [],
			'design'      => [],
		];

		$folderData = ['home::user', $folderId, $files, true, false, null, '', false, $folderIsShared,
					$folderEtag, 4096, 'some/path', null, $folderPermissions];

		$folder = call_user_func_array([$this,'mockFolder'], $folderData);
		$folder2 = call_user_func_array([$this, 'mockFolder'], $folderData);

		return [
			[
				$location, $folderPathFromRoot, $folder, $albumConfig, $files, $albums, $etag,
				[
					'files'       => $files,
					'albums'      => $albums,
					'albumconfig' => $albumConfig,
					'albumpath'   => $folderPathFromRoot,
					'updated'     => true
				]
			],
			[
				$location, $folderPathFromRoot, $folder2, $albumConfig, $files, $albums, $folderEtag,
				[
					'files'       => [],
					'albums'      => [],
					'albumconfig' => $albumConfig,
					'albumpath'   => $folderPathFromRoot,
					'updated'     => false
				]
			]
		];
	}

	/**
	 * @dataProvider providesGetFilesWithWorkingSetupData
	 *
	 * @param string $location
	 * @param string $folderPathFromRoot
	 * @param Folder $folder
	 * @param array $albumConfig
	 * @param array $files
	 * @param array $albums
	 * @param string $etag
	 * @param array $result
	 *
	 * @internal param $ $
	 */
	public function testGetFilesWithWorkingSetup(
		$location, $folderPathFromRoot, $folder, $albumConfig, $files, $albums, $etag, $result
	) {
		$features = '';
		$mediatypes = 'image/png';

		$this->mockGetCurrentFolder($location, $folderPathFromRoot, [$features], $folder);
		$this->mockGetConfig($folder, [$features], $albumConfig);
		$this->mockGetMediaFiles($folder, [$mediatypes], [$features], [$files, $albums]);

		$response = $this->controller->getList($location, $features, $etag, $mediatypes);

		/*$fakeResponse = new JSONResponse(
			[
				'message' => 'let me see',
				'success' => false
			]
		);*/
		$this->assertEquals($result, $response);
	}

	public function testGetFilesWithBrokenSetup() {
		$location = '';
		$features = '';
		$etag = 1111222233334444;
		$mediatypes = 'image/png';
		$exceptionMessage = 'Aïe!';
		$this->searchFolderService->expects($this->once())
								  ->method('getCurrentFolder')
								  ->with(
									  $location,
									  [$features]
								  )
								  ->willThrowException(new ServiceException($exceptionMessage));
		// Default status code when something breaks
		$status = Http::STATUS_INTERNAL_SERVER_ERROR;
		$this->request
			->expects($this->once())
			->method('getId')
			->willReturn('1234');
		$errorMessage = [
			'message' => 'An error occurred. Request ID: 1234',
			'success' => false
		];
		/** @type JSONResponse $response */
		$response = $this->controller->getList($location, $features, $etag, $mediatypes);

		$this->assertEquals($errorMessage, $response->getData());
	}

	public function providesFilesData() {
		$location = 'folder1';
		$folderPathFromRoot = 'user/files/' . $location;

		return [
			[
				['path' => $folderPathFromRoot . '/deep/folder/to/test/path/reduction.png'],
				$folderPathFromRoot . '/deep/reduction.png',
				$folderPathFromRoot
			],
			[
				['path' => $folderPathFromRoot . '/folder/image.png'],
				$folderPathFromRoot . '/folder/image.png',
				$folderPathFromRoot
			],
			[
				['path' => $folderPathFromRoot . '/testimage.png'],
				$folderPathFromRoot . '/testimage.png',
				$folderPathFromRoot
			]
		];
	}

	/**
	 * @dataProvider providesFilesData
	 *
	 * @param array $file
	 * @param string $fixedPath
	 * @param string $folderPathFromRoot
	 */
	public function testGetReducedPath($file, $fixedPath, $folderPathFromRoot) {
		$response = $this->getReducedPath($file['path'], $folderPathFromRoot);

		$this->assertEquals($fixedPath, $response);
	}

	/**
	 * Mocks IURLGenerator->linkToRoute
	 *
	 * @param int $code
	 * @param string $url
	 */
	protected function mockUrlToErrorPage($code, $url) {
		$this->urlGenerator->expects($this->once())
						   ->method('linkToRoute')
						   ->with($this->appName . '.page.error_page', ['code' => $code])
						   ->willReturn($url);
	}

	/**
	 * Mocks Files->getDownload
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param File $file
	 * @param string|null $filename
	 *
	 * @return array
	 */
	private function mockGetDownload($fileId, $file, $filename) {
		$this->mockGetFile($this->downloadService, $fileId, $file);

		$download = $this->mockDownloadData($file, $filename);

		$this->mockDownloadFile($file, $download);

		return $download;
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

	/**
	 * Mocks SearchFolderService->getCurrentFolder
	 *
	 * @param $location
	 * @param $folderPathFromRoot
	 * @param $features
	 * @param $folder
	 */
	private function mockGetCurrentFolder($location, $folderPathFromRoot, $features, $folder) {
		$answer = [
			$folderPathFromRoot,
			$folder,
		];
		$this->searchFolderService->expects($this->once())
								  ->method('getCurrentFolder')
								  ->with(
									  $location,
									  $features
								  )
								  ->willReturn($answer);
	}

	/**
	 * Mocks ConfigService->getConfig
	 *
	 * @param $folderNode
	 * @param $features
	 * @param $answer
	 */
	private function mockGetConfig($folderNode, $features, $answer) {
		$this->configService->expects($this->once())
							->method('getConfig')
							->with(
								$folderNode,
								$features
							)
							->willReturn($answer);
	}

	/**
	 * Mocks SearchMediaService->getMediaFiles
	 *
	 * @param $folderNode
	 * @param $mediatypes
	 * @param $features
	 * @param $answer
	 */
	private function mockGetMediaFiles($folderNode, $mediatypes, $features, $answer) {
		$this->searchMediaService->expects($this->any())
								 ->method('getMediaFiles')
								 ->with(
									 $folderNode,
									 $mediatypes,
									 $features
								 )
								 ->willReturn($answer);
	}

}
