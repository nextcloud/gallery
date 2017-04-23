<?php
/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Tests\Controller;

use OCA\Gallery\Controller\ConfigApiController;

/**
 * Class ConfigApiControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class ConfigApiControllerTest extends ConfigControllerTest {

	public function setUp() {
		parent::setUp();
		$this->controller = new ConfigApiController(
			$this->appName,
			$this->request,
			$this->configService,
			$this->logger
		);
	}

}
