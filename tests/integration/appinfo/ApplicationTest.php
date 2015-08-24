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

namespace OCA\GalleryPlus\AppInfo;

use OCA\GalleryPlus\Tests\Integration\GalleryIntegrationTest;

use OCA\GalleryPlus\Controller\PageController;
use OCA\GalleryPlus\Controller\ConfigController;
use OCA\GalleryPlus\Controller\ConfigPublicController;
use OCA\GalleryPlus\Controller\ConfigApiController;
use OCA\GalleryPlus\Controller\FilesController;
use OCA\GalleryPlus\Controller\FilesPublicController;
use OCA\GalleryPlus\Controller\FilesApiController;
use OCA\GalleryPlus\Controller\PreviewController;
use OCA\GalleryPlus\Controller\PreviewPublicController;
use OCA\GalleryPlus\Controller\PreviewApiController;

/**
 * Class ApplicationTest
 *
 * @package OCA\GalleryPlus\Tests\Integration
 */
class ApplicationTest extends GalleryIntegrationTest {

	public function testConfigController() {
		$controller = $this->container->query(
			'ConfigController'
		);

		$this->assertTrue($controller instanceof ConfigController);
	}

	public function testConfigPublicController() {
		$controller = $this->container->query(
			'ConfigPublicController'
		);

		$this->assertTrue($controller instanceof ConfigPublicController);
	}

	public function testFilesController() {
		$controller = $this->container->query(
			'FilesController'
		);

		$this->assertTrue($controller instanceof FilesController);
	}

	public function testFilesPublicController() {
		$controller = $this->container->query(
			'FilesPublicController'
		);

		$this->assertTrue($controller instanceof FilesPublicController);
	}

	public function testPreviewController() {
		$controller = $this->container->query(
			'PreviewController'
		);

		$this->assertTrue($controller instanceof PreviewController);
	}

	public function testPreviewPublicController() {
		$controller = $this->container->query(
			'PreviewPublicController'
		);

		$this->assertTrue($controller instanceof PreviewPublicController);
	}

	public function testToken() {
		$this->container->registerService(
			'Request', function ($c) {
			$request = $this->getMockBuilder('\OCP\IRequest')
							->disableOriginalConstructor()
							->getMock();
			$request->method('getParam')
					->with('token')
					->willReturn('some string');

			return $request;
		}
		);

		$token = $this->container->query(
			'Token'
		);

		$this->assertSame('some string', $token);
	}

}
