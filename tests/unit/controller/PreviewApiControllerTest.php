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

require_once __DIR__ . '/PreviewControllerTest.php';

use OCP\Files\File;

use OCP\AppFramework\Http;

use OCA\Gallery\Http\ImageResponse;

/**
 * Class PreviewApiControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class PreviewApiControllerTest extends PreviewControllerTest {

	/** @var PreviewApiController */
	protected $controller;

	public function setUp() {
		parent::setUp();
		$this->controller = new PreviewApiController(
			$this->appName,
			$this->request,
			$this->urlGenerator,
			$this->configService,
			$this->thumbnailService,
			$this->previewService,
			$this->downloadService,
			$this->eventSource,
			$this->logger
		);
	}

	/**
	 * @return array
	 */
	public function providesTestDownloadData() {
		return [
			[1234, $this->mockSvgFile(1234), true, 'image/svg+xml'],
			[4567, $this->mockSvgFile(4567), false, 'text/plain']
		];
	}

	/**
	 * @dataProvider providesTestDownloadData
	 *
	 * @param int $fileId
	 * @param File $file
	 * @param string $nativeSvg
	 * @param string $expectedMimeType
	 *
	 * @internal param string $type
	 */
	public function testGetPreviewOfSvg($fileId, $file, $nativeSvg, $expectedMimeType) {
		$width = 1024;
		$height = 768;

		/** @type File $file */
		$preview = $this->mockGetData(
			$fileId, $file, $width, $height, $keepAspect = true, $animatedPreview = true,
			$base64Encode = false, $previewRequired = false
		);
		$preview['name'] = $file->getName();

		/** @type ImageResponse $response */
		$response = $this->controller->getPreview($fileId, $width, $height, $nativeSvg);

		$this->assertEquals(Http::STATUS_OK, $response->getStatus());

		$this->assertEquals(
			$expectedMimeType . '; charset=utf-8', $response->getHeaders()['Content-type']
		);
	}

}
