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

use OCP\IRequest;
use OCP\ILogger;

use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http;

use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\PreviewService;
use OCA\Gallery\Service\ServiceException;

/**
 * Class ConfigControllerTest
 *
 * @package OCA\Gallery\Controller
 */
class ConfigControllerTest extends \Test\TestCase {

	/** @var string */
	protected $appName = 'gallery';
	/** @var IRequest */
	protected $request;
	/** @var ConfigController */
	protected $controller;
	/** @var ConfigService */
	protected $configService;
	/** @var PreviewService */
	protected $previewService;
	/** @var ILogger */
	protected $logger;
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
		parent::setUp();

		$this->request = $this->getMockBuilder('\OCP\IRequest')
							  ->disableOriginalConstructor()
							  ->getMock();
		$this->configService = $this->getMockBuilder('\OCA\Gallery\Service\ConfigService')
									->disableOriginalConstructor()
									->getMock();
		$this->previewService = $this->getMockBuilder('\OCA\Gallery\Service\PreviewService')
									 ->disableOriginalConstructor()
									 ->getMock();
		$this->logger = $this->getMockBuilder('\OCP\ILogger')
							 ->disableOriginalConstructor()
							 ->getMock();
		$this->controller = new ConfigController(
			$this->appName,
			$this->request,
			$this->configService,
			$this->logger
		);
	}

	/**
	 * @return array
	 */
	public function providesConfigData() {
		$noFeatures = [];

		$features = [
			'external_shares',
			'background_colour_toggle',
		];

		$featuresWithSvg = array_merge(
			$features,
			[
				'native_svg'
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
		$this->mockFeaturesList($features);
		$this->mockSupportedMediaTypes($slideshow, $nativeSvgSupport, $mimeTypes);

		$response = $this->controller->get($slideshow);

		$this->assertEquals(['features' => $features, 'mediatypes' => $mimeTypes], $response);
	}

	/**
	 * Not being able to get a config file is not a hard failure
	 */
	public function testCannotGetConfig() {
		$features = $this->mockConfigRetrievalError();
		$slideshow = true;
		$nativeSvgSupport = false;
		$this->mockSupportedMediaTypes($slideshow, $nativeSvgSupport, $this->baseMimeTypes);

		$response = $this->controller->get($slideshow);

		$this->assertEquals(
			['features' => $features, 'mediatypes' => $this->baseMimeTypes], $response
		);
	}

	public function testGetConfigWithBrokenSystem() {
		$slideshow = true;
		$exceptionMessage = 'Aïe!';
		$this->configService->expects($this->any())
							->method('getFeaturesList')
							->willThrowException(new ServiceException($exceptionMessage));
		// Default status code when something breaks
		$status = Http::STATUS_INTERNAL_SERVER_ERROR;
		$errorMessage = [
			'message' => $exceptionMessage  . ' (' . $status . ')',
			'success' => false
		];
		/** @type JSONResponse $response */
		$response = $this->controller->get($slideshow);

		$this->assertEquals($errorMessage, $response->getData());
	}

	/**
	 * Mocks ConfigService->getFeaturesList
	 *
	 * @param $features
	 */
	private function mockFeaturesList($features) {
		$this->configService->expects($this->any())
							->method('getFeaturesList')
							->willReturn($features);
	}

	/**
	 * Mocks PreviewService->getSupportedMediaTypes
	 *
	 * @param $slideshow
	 * @param $nativeSvgSupport
	 * @param $mimeTypes
	 */
	private function mockSupportedMediaTypes($slideshow, $nativeSvgSupport, $mimeTypes) {
		$this->configService->expects($this->any())
							 ->method('getSupportedMediaTypes')
							 ->with(
								 $this->equalTo($slideshow),
								 $this->equalTo($nativeSvgSupport)
							 )
							 ->willReturn($mimeTypes);
	}

	/**
	 * Returns an error message instead of a proper features list
	 *
	 * @return array
	 */
	private function mockConfigRetrievalError() {
		$exception = new ServiceException('Config corrupt');
		$errorMessage = $exception->getMessage() . "</br></br>Config location: /user1";
		$features = ['error' => ['message' => $errorMessage]];

		$this->mockFeaturesList($features);

		return $features;
	}

}
