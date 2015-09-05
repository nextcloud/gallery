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

/**
 * Class ApplicationTest
 *
 * @package OCA\Gallery\Tests\Integration
 */
class ApplicationTest extends GalleryIntegrationTest {

	public function providesServiceData() {
		return [
			['ConfigController', 'OCA\Gallery\Controller\ConfigController'],
			['ConfigPublicController', 'OCA\Gallery\Controller\ConfigPublicController'],
			['FilesController', 'OCA\Gallery\Controller\FilesController'],
			['FilesPublicController', 'OCA\Gallery\Controller\FilesPublicController'],
			['PreviewController', 'OCA\Gallery\Controller\PreviewController'],
			['PreviewPublicController', 'OCA\Gallery\Controller\PreviewPublicController'],
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
