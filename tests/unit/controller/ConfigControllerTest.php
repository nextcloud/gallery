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

use OCP\IRequest;
use OCP\ILogger;

use OCP\AppFramework\Http;

use OCA\GalleryPlus\Service\ConfigService;
use OCA\GalleryPlus\Service\PreviewService;
use OCA\GalleryPlus\Service\ServiceException;


/**
 * Class ConfigControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class ConfigControllerTest extends \Test\TestCase {

	/** @var string */
	private $appName = 'gallery';
	/** @var IRequest */
	private $request;
	/** @var ConfigController */
	private $controller;
	/** @var ConfigService */
	private $configService;
	/** @var PreviewService */
	private $previewService;
	/** @var ILogger */
	private $logger;
	/** @var array */
	private $baseMimeTypes = [
		'image/png',
		'image/jpeg',
		'image/gif',
		'image/x-xbitmap',
		'image/bmp',
		'image/tiff',
		'image/x-dcraw',
		'application/x-photoshop',
		'application/illustrator',
		'application/postscript',
	];

	/**
	 * Test set up
	 */
	public function setUp() {
		$this->request = $this->getMockBuilder('\OCP\IRequest')
							  ->disableOriginalConstructor()
							  ->getMock();
		$this->configService = $this->getMockBuilder('\OCA\GalleryPlus\Service\ConfigService')
									->disableOriginalConstructor()
									->getMock();
		$this->previewService = $this->getMockBuilder('\OCA\GalleryPlus\Service\PreviewService')
									 ->disableOriginalConstructor()
									 ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->controller = new ConfigController(
			$this->appName,
			$this->request,
			$this->configService,
			$this->previewService,
			$this->logger
		);
	}

	/**
	 * @return array
	 */
	public function providesConfigData() {
		$noFeatures = [];

		$features = [
			'external_shares' => 'yes',
			'toggle_background' => 'yes',
		];

		$featuresWithSvg = array_merge(
			$features,
			[
				'native_svg' => 'yes',
			]
		);

		$slideshowMimes = array_merge(
			$this->baseMimeTypes,
			[
				'application/font-sfnt',
				'application/x-font',
			]
		);

		$baseMimeTypesWithSvg = array_merge(
			$this->baseMimeTypes,
			[
				'image/svg+xml',
			]
		);

		$slideshowMimesWithSvg = array_merge(
			$slideshowMimes,
			[
				'image/svg+xml',
			]
		);

		return [
			[$noFeatures, $this->baseMimeTypes, false, false],
			[$noFeatures, $slideshowMimes, false, true],
			[$features, $this->baseMimeTypes, false, false],
			[$features, $slideshowMimes, false, true],
			[$featuresWithSvg, $baseMimeTypesWithSvg, true, false],
			[$featuresWithSvg, $slideshowMimesWithSvg, true, true],
		];
	}

	/**
	 * @dataProvider providesConfigData
	 *
	 * @param array $features
	 * @param array $mimeTypes
	 * @param bool $nativeSvgSupport
	 * @param bool $slideshow
	 */
	public function testGetConfig($features, $mimeTypes, $nativeSvgSupport, $slideshow) {

		$this->configService->expects($this->once())
							->method('getFeaturesList')
							->willReturn($features);

		$this->previewService->expects($this->once())
							 ->method('getSupportedMediaTypes')
							 ->with(
								 $this->equalTo($slideshow),
								 $this->equalTo($nativeSvgSupport)
							 )
							 ->willReturn($mimeTypes);

		$response = $this->controller->getConfig($slideshow);

		$this->assertEquals(['features' => $features, 'mediatypes' => $mimeTypes], $response);
	}

	/**
	 * Not being able to get a config file is not a hard failure
	 */
	public function testCannotGetConfig() {
		$exception = new ServiceException('Config corrupt');
		$errorMessage = $exception->getMessage() . "</br></br>Config location: /user1";
		$features = ['error' => ['message' => $errorMessage]];
		$nativeSvgSupport = false;
		$slideshow = true;

		$this->configService->expects($this->once())
							->method('getFeaturesList')
							->willReturn($features);

		$this->previewService->expects($this->once())
							 ->method('getSupportedMediaTypes')
							 ->with(
								 $this->equalTo($slideshow),
								 $this->equalTo($nativeSvgSupport)
							 )
							 ->willReturn($this->baseMimeTypes);

		$response = $this->controller->getConfig($slideshow);

		$this->assertEquals(
			['features' => $features, 'mediatypes' => $this->baseMimeTypes], $response
		);
	}

}
