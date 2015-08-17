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

use OCP\ILogger;

use OCP\AppFramework\Http;

use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\PreviewService;

/**
 * Trait Config
 *
 * @package OCA\Gallery\Controller
 */
trait Config {

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
	 * @NoAdminRequired
	 *
	 * Returns an app configuration array
	 *
	 * @param bool $extraMediaTypes
	 *
	 * @return array <string,null|array>
	 */
	private function getConfig($extraMediaTypes = false) {
		$features = $this->configService->getFeaturesList();

		//$this->logger->debug("Features: {features}", ['features' => $features]);

		$nativeSvgSupport = $this->isNativeSvgActivated($features);
		$mediaTypes =
			$this->previewService->getSupportedMediaTypes($extraMediaTypes, $nativeSvgSupport);

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
