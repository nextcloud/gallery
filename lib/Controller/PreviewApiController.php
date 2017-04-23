<?php
/**
 * Nextcloud - Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Controller;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ILogger;
use OCP\Files\File;

use OCP\AppFramework\ApiController;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

use OCA\Gallery\Http\ImageResponse;
use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\ThumbnailService;
use OCA\Gallery\Service\PreviewService;
use OCA\Gallery\Service\DownloadService;
use OCA\Gallery\Utility\EventSource;

/**
 * Class PreviewApiController
 *
 * @package OCA\Gallery\Controller
 */
class PreviewApiController extends ApiController {

	use Preview;

	/**  @var EventSource */
	private $eventSource;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IURLGenerator $urlGenerator
	 * @param ConfigService $configService
	 * @param ThumbnailService $thumbnailService
	 * @param PreviewService $previewService
	 * @param DownloadService $downloadService
	 * @param EventSource $eventSource
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		ConfigService $configService,
		ThumbnailService $thumbnailService,
		PreviewService $previewService,
		DownloadService $downloadService,
		EventSource $eventSource,
		ILogger $logger
	) {
		parent::__construct($appName, $request);

		$this->urlGenerator = $urlGenerator;
		$this->configService = $configService;
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
	 * Generates thumbnails
	 *
	 * @see PreviewController::getThumbnails()
	 *
	 * @param string $ids the ID of the files of which we need thumbnail previews of
	 * @param bool $square
	 * @param double $scale
	 *
	 * @return array<string,array|string|null>
	 */
	public function getThumbnails($ids, $square, $scale) {
		$idsArray = explode(';', $ids);

		foreach ($idsArray as $id) {
			// Casting to integer here instead of using array_map to extract IDs from the URL
			list($thumbnail, $status) = $this->getThumbnail((int)$id, $square, $scale);
			$thumbnail['fileid'] = $id;
			$thumbnail['status'] = $status;

			$this->eventSource->send('preview', $thumbnail);
		}
		$this->eventSource->close();

		$this->exitController();
		// @codeCoverageIgnoreStart
	} // @codeCoverageIgnoreEnd

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
	 * @param bool $nativesvg This is a GET parameter, so no camelCase
	 *
	 * @return ImageResponse|Http\JSONResponse
	 */
	public function getPreview($fileId, $width, $height, $nativesvg = false) {
		/** @type File $file */
		list($file, $preview, $status) = $this->getData($fileId, $width, $height);

		if (!$preview) {
			return new JSONResponse(
				[
					'message' => "I'm truly sorry, but we were unable to generate a preview for this file",
					'success' => false
				], $status
			);
		}
		$preview['name'] = $file->getName();

		// That's the only exception out of all the image media types we serve
		if ($preview['mimetype'] === 'image/svg+xml' && !$nativesvg) {
			$preview['mimetype'] = 'text/plain';
		}

		return new ImageResponse($preview, $status);
	}

}
