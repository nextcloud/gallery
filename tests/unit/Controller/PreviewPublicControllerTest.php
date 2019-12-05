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

use OCA\Gallery\Controller\PreviewPublicController;

/**
 * Class PreviewPublicControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class PreviewPublicControllerTest extends PreviewControllerTest {

	protected function setUp(): void {
		parent::setUp();
		$this->controller = new PreviewPublicController(
			$this->appName,
			$this->request,
			$this->urlGenerator,
			$this->configService,
			$this->thumbnailService,
			$this->previewService,
			$this->downloadService,
			$this->eventSource,
			$this->logger
		);
	}

}
