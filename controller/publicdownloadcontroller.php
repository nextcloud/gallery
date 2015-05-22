<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\Controller;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ILogger;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\GalleryPlus\Http\ImageResponse;
use OCA\GalleryPlus\Service\ServiceException;
use OCA\GalleryPlus\Service\DownloadService;

/**
 * Class PublicDownloadController
 *
 * Note: Type casting only works if the "@param" parameters are also included in this class as
 * their not yet inherited
 *
 * @package OCA\GalleryPlus\Controller
 */
class PublicDownloadController extends Controller {

	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;
	/**
	 * @var DownloadService
	 */
	private $downloadService;
	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 * @param DownloadService $downloadService
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		DownloadService $downloadService,
		ILogger $logger
	) {
		parent::__construct($appName, $request);

		$this->urlGenerator = $urlGenerator;
		$this->downloadService = $downloadService;
		$this->logger = $logger;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * Downloads the file associated with a token
	 *
	 * @return \OCA\GalleryPlus\Http\ImageResponse|Http\RedirectResponse
	 */
	public function downloadFile() {
		try {
			$download = $this->downloadService->downloadFile();

			return new ImageResponse($download);
		} catch (ServiceException $exception) {
			$url = $this->urlGenerator->linkToRoute(
				$this->appName . '.page.error_page',
				[
					'message' => $exception->getMessage(),
					'code'    => Http::STATUS_NOT_FOUND
				]
			);

			return new RedirectResponse($url);
		}
	}

}
