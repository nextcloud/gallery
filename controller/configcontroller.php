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

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\PreviewService;

/**
 * Class ConfigController
 *
 * @package OCA\Gallery\Controller
 */
class ConfigController extends Controller {

	use Config;
	use HttpError;

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
	 * @param bool $extramediatypes
	 *
	 * @return array <string,null|array>
	 */
	public function get($extramediatypes = false) {
		try {
			return $this->getConfig($extramediatypes);
		} catch (\Exception $exception) {
			return $this->jsonError($exception);
		}
	}

}
