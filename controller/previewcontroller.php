<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Robin Appelman <icewind@owncloud.com>
 *
 * @copyright Olivier Paroz 2014-2015
 * @copyright Robin Appelman 2012-2014
 */

namespace OCA\GalleryPlus\Controller;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IEventSource;
use OCP\ILogger;
use OCP\Files\File;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

use OCA\GalleryPlus\Http\ImageResponse;
use OCA\GalleryPlus\Service\ServiceException;
use OCA\GalleryPlus\Service\NotFoundServiceException;
use OCA\GalleryPlus\Service\ThumbnailService;
use OCA\GalleryPlus\Service\PreviewService;
use OCA\GalleryPlus\Service\DownloadService;

/**
 * Class PreviewController
 *
 * @package OCA\GalleryPlus\Controller
 */
class PreviewController extends Controller {

	use JsonHttpError;

	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;
	/**
	 * @var ThumbnailService
	 */
	private $thumbnailService;
	/**
	 * @var PreviewService
	 */
	private $previewService;
	/**
	 * @var DownloadService
	 */
	private $downloadService;
	/**
	 * @var IEventSource
	 */
	private $eventSource;
	/**
	 * @var ILogger
	 */
	private $logger;
	/**
	 * @type bool
	 */
	private $download = false;

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
	 *
	 * Sends back a list of all media types supported by the system, as well as the name of their
	 *     icon
	 *
	 * @param bool $slideshow
	 *
	 * @return array <string,string>|null
	 */
	public function getMediaTypes($slideshow = false) {
		return $this->previewService->getSupportedMediaTypes($slideshow);
	}

	/**
	 * @NoAdminRequired
	 *
	 * Generates thumbnails
	 *
	 * Uses EventSource to send thumbnails back as soon as they're created
	 *
	 * FIXME: @LukasReschke says: The exit is required here because
	 * otherwise the AppFramework is trying to add headers as well after
	 * dispatching the request which results in a "Cannot modify header
	 * information" notice.
	 *
	 * WARNING: Returning a JSON response does not get rid of the problem
	 *
	 * @param string $ids the ID of the files of which we need thumbnail previews of
	 * @param bool $square
	 * @param bool $scale
	 *
	 * @return array<string,array|string>
	 */
	public function getThumbnails($ids, $square, $scale) {
		$idsArray = explode(';', $ids);

		foreach ($idsArray as $id) {
			$thumbnail = $this->getThumbnail((int)$id, $square, $scale);
			$thumbnail['fileid'] = $id;
			$this->eventSource->send('preview', $thumbnail);
		}
		$this->eventSource->close();

		exit();
	}

	/**
	 * @NoAdminRequired
	 *
	 * Sends either a large preview of the requested file or the
	 * original file itself
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param int $width
	 * @param int $height
	 * @param string|null $download
	 *
	 * @return ImageResponse|Http\JSONResponse
	 */
	public function getPreview($fileId, $width, $height, $download) {
		if (!is_null($download)) {
			$this->download = true;
		}
		try {
			$preview = $this->getPreviewData($fileId, $width, $height);

			return new ImageResponse($preview['data'], $preview['status']);
		} catch (ServiceException $exception) {
			return $this->error($exception);
		}
	}


	/**
	 * Retrieves the thumbnail to send back to the browser
	 *
	 * The thumbnail is either a resized preview of the file or the original file
	 * Thumbnails are base64encoded before getting sent back
	 *
	 *
	 * @param int $fileId the ID of the file of which we need a thumbnail preview of
	 * @param bool $square whether the thumbnail should be square
	 * @param bool $scale whether we're allowed to scale the preview up
	 *
	 * @return array<string,array|string>
	 */
	private function getThumbnail($fileId, $square, $scale) {
		list($width, $height, $aspect, $animatedPreview, $base64Encode) =
			$this->thumbnailService->getThumbnailSpecs($square, $scale);

		try {
			$preview = $this->getPreviewData(
				$fileId, $width, $height, $aspect, $animatedPreview, $base64Encode
			);
		} catch (ServiceException $exception) {
			$preview = ['data' => null, 'status' => 500, 'type' => 'error'];
		}
		$thumbnail = $preview['data'];
		if ($preview['status'] === 200 && $preview['type'] === 'preview') {
			$thumbnail['preview'] = $this->previewService->previewValidator($square, $base64Encode);
		}
		$thumbnail['status'] = $preview['status'];

		return $thumbnail;
	}

	/**
	 * Returns either a generated preview (or the mime-icon when the preview generation fails)
	 * or the file as-is
	 *
	 * Sample logger
	 * We can't just send the preview array as it can contain quite a large data stream
	 * $this->logger->debug("[Batch] THUMBNAIL NAME : {image} / PATH : {path} /
	 * MIME : {mimetype} / DATA : {preview}", [
	 *                'image'    => $preview['data']['image'],
	 *                'path'     => $preview['data']['path'],
	 *                'mimetype' => $preview['data']['mimetype'],
	 *                'preview'  => substr($preview['data']['preview'], 0, 20),
	 *              ]
	 *            );
	 *
	 * @param int $fileId
	 * @param int $width
	 * @param int $height
	 * @param bool $keepAspect
	 * @param bool $animatedPreview
	 * @param bool $base64Encode
	 *
	 * @return array<string,\OC_Image|string>
	 *
	 * @throws NotFoundServiceException
	 */
	private function getPreviewData(
		$fileId, $width, $height, $keepAspect = true, $animatedPreview = true, $base64Encode = false
	) {
		$status = Http::STATUS_OK;
		try {
			/** @type File $file */
			$file = $this->previewService->getResourceFromId($fileId);
			if (!$this->download) {
				$previewRequired =
					$this->previewService->isPreviewRequired($file, $animatedPreview);
				if ($previewRequired) {
					$type = 'preview';
					$preview = $this->previewService->createPreview(
						$file, $width, $height, $keepAspect, $base64Encode
					);
					if (!$this->previewService->isPreviewValid()) {
						$type = 'error';
						$status = Http::STATUS_NOT_FOUND;
					}
				} else {
					$type = 'download';
					$preview = $this->downloadService->downloadFile($file, $base64Encode);
				}
			} else {
				$type = 'download';
				$preview = $this->downloadService->downloadFile($file, $base64Encode);
			}

			$preview['name'] = $file->getName();

		} catch (\Exception $exception) {
			$type = 'error';
			$status = Http::STATUS_INTERNAL_SERVER_ERROR;
			$preview = null;
		}

		return ['data' => $preview, 'status' => $status, 'type' => $type];
	}

}
