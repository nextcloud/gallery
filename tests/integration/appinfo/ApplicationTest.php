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

	public function providesServiceData() {
		return [
			['ConfigController', 'OCA\GalleryPlus\Controller\ConfigController'],
			['ConfigPublicController', 'OCA\GalleryPlus\Controller\ConfigPublicController'],
			['FilesController', 'OCA\GalleryPlus\Controller\FilesController'],
			['FilesPublicController', 'OCA\GalleryPlus\Controller\FilesPublicController'],
			['PreviewController', 'OCA\GalleryPlus\Controller\PreviewController'],
			['PreviewPublicController', 'OCA\GalleryPlus\Controller\PreviewPublicController'],
			['L10N', '\OC_L10N']
		];
	}

	/**
	 * @dataProvider providesServiceData
	 *
	 * @param string $registeredService
	 * @param string $expectedClass
	 */
	public function testContainerQuery($registeredService, $expectedClass) {
		$service = $this->container->query($registeredService);

		$this->assertTrue($service instanceof $expectedClass);
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
