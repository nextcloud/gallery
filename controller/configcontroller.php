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

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

use OCA\GalleryPlus\Service\ConfigService;
use OCA\GalleryPlus\Service\PreviewService;

/**
 * Class ConfigController
 *
 * @package OCA\GalleryPlus\Controller
 */
class ConfigController extends Controller {

	use JsonHttpError;

	/**
	 * @var ConfigService
	 */
	private $configService;
	/**
	 * @var PreviewService
	 */
	private $previewService;
	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param ConfigService $configService
	 * @param PreviewService $previewService
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		ConfigService $configService,
		PreviewService $previewService,
		ILogger $logger
	) {
		parent::__construct($appName, $request);

		$this->configService = $configService;
		$this->previewService = $previewService;
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
	 *
	 * Returns an app configuration array
	 *
	 * @param bool $slideshow
	 *
	 * @return array
	 */
	public function getConfig($slideshow = false) {
		$features = $this->configService->getFeaturesList();

		//$this->logger->debug("Features: {features}", ['features' => $features]);

		$nativeSvgSupport = $this->isNativeSvgActivated($features);
		$mediaTypes = $this->previewService->getSupportedMediaTypes($slideshow, $nativeSvgSupport);

		return ['features' => $features, 'mediatypes' => $mediaTypes];
	}

	/**
	 * Determines if the native SVG feature has been activated
	 *
	 * @param array $features
	 *
	 * @return bool
	 */
	private function isNativeSvgActivated($features) {
		$nativeSvgSupport = false;
		if (!empty($features)
			&& array_key_exists('native_svg', $features)
			&& $features['native_svg'] === 'yes'
		) {
			$nativeSvgSupport = true;
		}

		return $nativeSvgSupport;
	}

}
