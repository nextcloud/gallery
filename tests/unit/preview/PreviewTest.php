<?php
/**
 * Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2016
 */

namespace OCA\Gallery\Preview;

use OCP\Files\File;
use OCP\IPreview;

/**
 * Class PreviewTest
 *
 * @package OCA\Gallery\Environment
 */
class PreviewTest extends \Test\GalleryUnitTest {

	/** @var IPreview|\PHPUnit_Framework_MockObject_MockObject */
	private $corePreviewManager;
	/** @var Preview */
	private $previewManager;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$this->corePreviewManager = $this->getMockBuilder(IPreview::class)
										 ->disableOriginalConstructor()
										 ->getMock();
		$this->previewManager = new Preview($this->corePreviewManager);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testGetPreviewFromCoreWithBrokenSystem() {
		$keepAspect = true; // Doesn't matter
		$exception = new \Exception('Encryption ate your file');
		$this->corePreviewManager->method('getPreview')
			->willThrowException($exception);

		$this->previewManager->getPreview(
			$this->createMock(File::class),
			42,
			42,
			$keepAspect
		);
	}
}
