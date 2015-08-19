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

require_once __DIR__ . '/FilesControllerTest.php';

/**
 * Class FilesApiControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class FilesApiControllerTest extends FilesControllerTest {

	public function setUp() {
		parent::setUp();
		$this->controller = new FilesController(
			$this->appName,
			$this->request,
			$this->urlGenerator,
			$this->searchFolderService,
			$this->configService,
			$this->searchMediaService,
			$this->downloadService,
			$this->logger
		);
	}

}
