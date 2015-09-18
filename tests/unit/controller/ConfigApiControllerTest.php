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

require_once __DIR__ . '/ConfigControllerTest.php';

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
