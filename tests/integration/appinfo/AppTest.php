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

	public function testNavigationEntry() {
		$navigationManager = \OC::$server->getNavigationManager();
		$navigationManager->clear();
		$this->assertEmpty($navigationManager->getAll());
		require __DIR__ . '/../../../appinfo/app.php';
		// Test whether the navigation entry got added
		$this->assertCount(1, $navigationManager->getAll());
	}
}
