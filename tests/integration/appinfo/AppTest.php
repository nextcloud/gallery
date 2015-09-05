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

use \OCP\App;

use OCA\GalleryPlus\Tests\Integration\GalleryIntegrationTest;

/**
 * Class AppTest
 *
 * @package OCA\GalleryPlus\Tests\Integration
 */
class AppTest extends GalleryIntegrationTest {

	public function testAppInstalled() {
		$appManager = $this->container->query('OCP\App\IAppManager');
		$this->assertTrue($appManager->isInstalled('galleryplus'));
	}

	public function testAppName() {
		$appData = App::getAppInfo('galleryplus');

		$this->assertSame('galleryplus', $appData['id']);
	}

	public function testAppLicense() {
		$appData = App::getAppInfo('galleryplus');

		$this->assertSame('AGPL', $appData['licence']);
	}

	public function testAppMaxPhpVersion() {
		$appData = App::getAppInfo('galleryplus');

		$this->assertSame('7', $appData['dependencies']['php']['@attributes']['max-version']);
	}

	/**
	 * Routes are not loaded any more, so we can't test the navigation entry, but we know there
	 * will be an exception thrown when trying to load the navigation
	 *
	 * @expectedException \Symfony\Component\Routing\Exception\RouteNotFoundException
	 */
	public function testNavigationEntry() {
		$this->assertCount(1, \OC_App::getAppNavigationEntries('gallery'));
	}
}
