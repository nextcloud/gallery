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

require_once __DIR__ . '/PreviewControllerTest.php';

/**
 * Class PreviewApiControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class PreviewApiControllerTest extends PreviewControllerTest {

	public function setUp() {
		parent::setUp();
		$this->controller = new PreviewApiController(
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
