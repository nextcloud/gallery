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

namespace OCA\Gallery\AppInfo;

use OCA\Gallery\Tests\Integration\GalleryIntegrationTest;

use OCA\Gallery\Controller\PageController;
use OCA\Gallery\Controller\ConfigController;
use OCA\Gallery\Controller\ConfigPublicController;
use OCA\Gallery\Controller\ConfigApiController;
use OCA\Gallery\Controller\FilesController;
use OCA\Gallery\Controller\FilesPublicController;
use OCA\Gallery\Controller\FilesApiController;
use OCA\Gallery\Controller\PreviewController;
use OCA\Gallery\Controller\PreviewPublicController;
use OCA\Gallery\Controller\PreviewApiController;

/**
 * Class ApplicationTest
 *
 * @package OCA\Gallery\Tests\Integration
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
