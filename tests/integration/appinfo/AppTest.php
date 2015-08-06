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

use \OCA\GalleryPlus\Tests\Integration\GalleryIntegrationTest;

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
}
