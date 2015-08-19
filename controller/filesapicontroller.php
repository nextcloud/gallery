<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\Gallery\Controller;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ILogger;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\Gallery\Http\ImageResponse;
use OCA\Gallery\Service\SearchFolderService;
use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\SearchMediaService;
use OCA\Gallery\Service\DownloadService;

/**
 * Class FilesApiController
 *
 * @package OCA\Gallery\Controller
 */
class FilesApiController extends ApiController {

	use Files;
	use JsonHttpError;

	/** @var IURLGenerator */
	private $urlGenerator;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 * @param SearchFolderService $searchFolderService
	 * @param ConfigService $configService
	 * @param SearchMediaService $searchMediaService
	 * @param DownloadService $downloadService
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		SearchFolderService $searchFolderService,
		ConfigService $configService,
		SearchMediaService $searchMediaService,
		DownloadService $downloadService,
		ILogger $logger
	) {
		parent::__construct($appName, $request);

		$this->urlGenerator = $urlGenerator;
		$this->searchFolderService = $searchFolderService;
		$this->configService = $configService;
		$this->searchMediaService = $searchMediaService;
		$this->downloadService = $downloadService;
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * Returns a list of all media files available to the authenticated user
	 *
	 * @see FilesController::getList()
	 *
	 * @param string $location a path representing the current album in the app
	 * @param string $features the list of supported features
	 * @param string $etag the last known etag in the client
	 * @param string $mediatypes the list of supported media types
	 *
	 * @return array <string,array<string,string|int>>|Http\JSONResponse
	 */
	public function getList($location, $features, $etag, $mediatypes) {
		$featuresArray = explode(',', $features);
		$mediaTypesArray = explode(';', $mediatypes);
		try {
			return $this->getFiles($location, $featuresArray, $etag, $mediaTypesArray);
		} catch (\Exception $exception) {
			return $this->error($exception);
		}
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * Sends the file matching the fileId
	 *
	 * @param int $fileId the ID of the file we want to download
	 * @param string|null $filename
	 *
	 * @return ImageResponse|RedirectResponse
	 */
	public function download($fileId, $filename = null) {
		$download = $this->getDownload($fileId, $filename);

		if (!$download) {
			$url = $this->urlGenerator->linkToRoute(
				$this->appName . '.page.error_page',
				[
					'message' => 'There was a problem accessing the file',
					'code'    => Http::STATUS_NOT_FOUND
				]
			);

			return new RedirectResponse($url);
		}

		return new ImageResponse($download);
	}

}
