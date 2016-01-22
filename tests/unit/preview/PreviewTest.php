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

namespace OCA\Gallery\Preview;

use OCP\IConfig;
use OCP\IPreview;
use OCP\ILogger;

use OCA\Gallery\AppInfo\Application;


/**
 * Class PreviewTest
 *
 * @package OCA\Gallery\Environment
 */
class PreviewTest extends \Test\GalleryUnitTest {

	/** @var IConfig */
	private $config;
	/** @var IPreview */
	private $corePreviewManager;
	/** @var ILogger */
	protected $logger;
	/** @var Preview */
	private $previewManager;

	/**
	 * Test set up
	 */
	public function setUp() {
		parent::setUp();

		$this->config = $this->getMockBuilder('\OCP\IConfig')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->corePreviewManager = $this->getMockBuilder('\OCP\IPreview')
										 ->disableOriginalConstructor()
										 ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->previewManager = new Preview(
			$this->config,
			$this->corePreviewManager,
			$this->logger
		);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testGetPreviewFromCoreWithBrokenSystem() {
		$keepAspect = true; // Doesn't matter
		$exception = new \Exception('Encryption ate your file');
		$preview = $this->mockGetPreviewWithBrokenSetup($exception);
		self::invokePrivate($this->previewManager, 'preview', [$preview]);

		self::invokePrivate($this->previewManager, 'getPreviewFromCore', [$keepAspect]);
	}

	/**
	 * @param $fileId
	 * @param $width
	 * @param $height
	 *
	 * @return object|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockGetPreview($fileId, $width, $height) {
		$image = new \OC_Image(file_get_contents(\OC::$SERVERROOT . '/tests/data/testimage.jpg'));
		$image->preciseResize($width, $height);

		$preview = $this->getMockBuilder('\OC\Preview')
						->disableOriginalConstructor()
						->getMock();
		$preview->method('getPreview')
				->willReturn($image);
		$preview->method('isCached')
				->willReturn($fileId);

		return $preview;
	}

	private function mockGetPreviewWithBrokenSetup($exception) {
		$preview = $this->getMockBuilder('\OC\Preview')
						->disableOriginalConstructor()
						->getMock();
		$preview->method('setMaxX')
				->willReturn(null);
		$preview->method('setMaxY')
				->willReturn(null);
		$preview->method('setScalingUp')
				->willReturn(null);
		$preview->method('setKeepAspect')
				->willReturn(null);
		$preview->method('getPreview')
				->willThrowException($exception);

		return $preview;
	}

}
