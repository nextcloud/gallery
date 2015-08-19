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

namespace OCA\GalleryPlus\Controller;

require_once __DIR__ . '/PreviewControllerTest.php';

/**
 * Class PreviewPublicControllerTest
 *
 * @package OCA\GalleryPlus\Controller
 */
class PreviewPublicControllerTest extends PreviewControllerTest {

	public function setUp() {
		parent::setUp();
		$this->controller = new PreviewPublicController(
			$this->appName,
			$this->request,
			$this->urlGenerator,
			$this->thumbnailService,
			$this->previewService,
			$this->downloadService,
			$this->eventSource,
			$this->logger
		);
	}

}
