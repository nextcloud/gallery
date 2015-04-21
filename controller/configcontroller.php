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

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

use OCA\GalleryPlus\Service\ConfigService;
use OCA\GalleryPlus\Utility\SmarterLogger;

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
	 * @var SmarterLogger
	 */
	private $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param ConfigService $configService
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		ConfigService $configService,
		SmarterLogger $logger
	) {
		parent::__construct($appName, $request);

		$this->configService = $configService;
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
	 *
	 * Returns an app configuration array
	 *
	 * @return array
	 */
	public function getConfig() {
		return $this->getFeaturesList();
	}

	/**
	 * @NoAdminRequired
	 *
	 * Returns a list of supported features
	 *
	 * @return array
	 */
	private function getFeaturesList() {
		return $this->configService->getFeaturesList();
	}

}
