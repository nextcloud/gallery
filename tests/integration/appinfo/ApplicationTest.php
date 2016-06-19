<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2016
 */

namespace OCA\GalleryPlus\AppInfo;

use OCA\GalleryPlus\Tests\Integration\GalleryIntegrationTest;

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
			['L10N', '\OCP\IL10N']
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
