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
use OCP\IURLGenerator;
use OCP\IEventSource;
use OCP\ILogger;
use OCP\Files\File;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\JSONResponse;

use OCA\Gallery\Http\ImageResponse;
use OCA\Gallery\Service\ThumbnailService;
use OCA\Gallery\Service\PreviewService;
use OCA\Gallery\Service\DownloadService;

/**
 * Class PreviewApiController
 *
 * @package OCA\Gallery\Controller
 */
class PreviewApiController extends ApiController {

	use Preview;
	use JsonHttpError;

	/**
	 * @var IEventSource
	 */
	private $eventSource;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 * @param ThumbnailService $thumbnailService
	 * @param PreviewService $previewService
	 * @param DownloadService $downloadService
	 * @param IEventSource $eventSource
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		ThumbnailService $thumbnailService,
		PreviewService $previewService,
		DownloadService $downloadService,
		IEventSource $eventSource,
		ILogger $logger
	) {
		parent::__construct($appName, $request);

		$this->urlGenerator = $urlGenerator;
		$this->thumbnailService = $thumbnailService;
		$this->previewService = $previewService;
		$this->downloadService = $downloadService;
		$this->eventSource = $eventSource;
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * Sends either a large preview of the requested file or the original file itself
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param int $width
	 * @param int $height
	 * @param string|null $download
	 *
	 * @return ImageResponse|RedirectResponse|Http\JSONResponse
	 */
	public function getPreview($fileId, $width, $height, $download) {
		if (!is_null($download)) {
			$this->download = true;
		}
		/** @type File $file */
		list($file, $preview, $status) = $this->getData($fileId, $width, $height);

		if ($preview === null) {
			if ($this->download) {
				$url = $this->getErrorUrl($status);

				return new RedirectResponse($url);
			} else {

				return new JSONResponse(['message' => 'Oh Nooooes!', 'success' => false], $status);
			}
		}
		$preview['name'] = $file->getName();

		return new ImageResponse($preview, $status);
	}

}
