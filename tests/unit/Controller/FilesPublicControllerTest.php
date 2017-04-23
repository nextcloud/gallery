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

use OCA\Gallery\Controller\FilesPublicController;

/**
 * Class FilesPublicControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class FilesPublicControllerTest extends FilesControllerTest {

	public function setUp() {
		parent::setUp();
		$this->controller = new FilesPublicController(
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
