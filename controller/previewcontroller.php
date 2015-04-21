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

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

use OCA\GalleryPlus\Http\ImageResponse;
use OCA\GalleryPlus\Service\ServiceException;
use OCA\GalleryPlus\Service\ThumbnailService;
use OCA\GalleryPlus\Service\PreviewService;
use OCA\GalleryPlus\Service\DownloadService;
use OCA\GalleryPlus\Utility\SmarterLogger;

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
	 * @var SmarterLogger
	 */
	private $logger;

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
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IURLGenerator $urlGenerator,
		ThumbnailService $thumbnailService,
		PreviewService $previewService,
		DownloadService $downloadService,
		IEventSource $eventSource,
		SmarterLogger $logger
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
	 * @param string $images
	 * @param bool $square
	 * @param bool $scale
	 *
	 * @return array<string,array|string>
	 */
	public function getThumbnails($images, $square, $scale) {
		$imagesArray = explode(';', $images);

		foreach ($imagesArray as $image) {
			$thumbnail = $this->getThumbnail($image, $square, $scale);
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
	 * If the browser can use the file as-is then we simply let
	 * the browser download the file, straight from the filesystem
	 *
	 * @param string $file
	 * @param int $x
	 * @param int $y
	 *
	 * @return ImageResponse|Http\JSONResponse
	 */
	public function showPreview($file, $x, $y) {
		try {
			$preview = $this->getPreview($file, $x, $y);

			return new ImageResponse($preview['data'], $preview['status']);
		} catch (ServiceException $exception) {
			return $this->error($exception);
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * Downloads the file
	 *
	 * @param string $file
	 *
	 * @return \OCA\GalleryPlus\Http\ImageResponse|Http\JSONResponse
	 */
	public function downloadPreview($file) {
		try {
			$download = $this->downloadService->downloadFile($file);

			return new ImageResponse($download);
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
	 * @param string $image
	 * @param bool $square
	 * @param bool $scale
	 *
	 * @return array<string,array|string>
	 */
	private function getThumbnail($image, $square, $scale) {
		list($width, $height, $aspect, $animatedPreview, $base64Encode) =
			$this->thumbnailService->getThumbnailSpecs($square, $scale);

		try {
			$preview = $this->getPreview(
				$image, $width, $height, $aspect, $animatedPreview, $base64Encode
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
	 * @param string $image
	 * @param int $width
	 * @param int $height
	 * @param bool $keepAspect
	 * @param bool $animatedPreview
	 * @param bool $base64Encode
	 *
	 * @return array<string,\OC_Image|string>
	 */
	private function getPreview(
		$image, $width, $height, $keepAspect = true, $animatedPreview = true, $base64Encode = false
	) {
		$status = Http::STATUS_OK;
		$previewRequired = $this->previewService->isPreviewRequired($image, $animatedPreview);
		if ($previewRequired) {
			$type = 'preview';
			$preview = $this->previewService->createPreview(
				$image, $width, $height, $keepAspect, $base64Encode
			);
			if (!$this->previewService->isPreviewValid()) {
				$type = 'error';
				$status = Http::STATUS_NOT_FOUND;
			}
		} else {
			$type = 'download';
			$preview = $this->downloadService->downloadFile($image, $base64Encode);
		}

		return ['data' => $preview, 'status' => $status, 'type' => $type];
	}

}
