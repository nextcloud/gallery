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

use OCP\IEventSource;
use OCP\IURLGenerator;
use OCP\IRequest;
use OCP\Files\Folder;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

use OCA\GalleryPlus\Environment\Environment;
use OCA\GalleryPlus\Environment\EnvironmentException;
use OCA\GalleryPlus\Http\ImageResponse;
use OCA\GalleryPlus\Service\ServiceException;
use OCA\GalleryPlus\Service\InfoService;
use OCA\GalleryPlus\Service\ThumbnailService;
use OCA\GalleryPlus\Service\PreviewService;
use OCA\GalleryPlus\Service\DownloadService;

/**
 * Class ServiceController
 *
 * @package OCA\GalleryPlus\Controller
 */
class ServiceController extends Controller {

	use JsonHttpError;

	/**
	 * @type Environment
	 */
	private $environment;
	/**
	 * @type InfoService
	 */
	private $infoService;
	/**
	 * @type ThumbnailService
	 */
	private $thumbnailService;
	/**
	 * @type PreviewService
	 */
	private $previewService;
	/**
	 * @type DownloadService
	 */
	private $downloadService;
	/**
	 * @type IURLGenerator
	 */
	private $urlGenerator;
	/**
	 * @type IEventSource
	 */
	private $eventSource;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param Environment $environment
	 * @param InfoService $infoService
	 * @param ThumbnailService $thumbnailService
	 * @param PreviewService $previewService
	 * @param DownloadService $downloadService
	 * @param IURLGenerator $urlGenerator
	 * @param IEventSource $eventSource
	 */
	public function __construct(
		$appName,
		IRequest $request,
		Environment $environment,
		InfoService $infoService,
		ThumbnailService $thumbnailService,
		PreviewService $previewService,
		DownloadService $downloadService,
		IURLGenerator $urlGenerator,
		IEventSource $eventSource
	) {
		parent::__construct($appName, $request);

		$this->environment = $environment;
		$this->infoService = $infoService;
		$this->thumbnailService = $thumbnailService;
		$this->previewService = $previewService;
		$this->downloadService = $downloadService;
		$this->urlGenerator = $urlGenerator;
		$this->eventSource = $eventSource;
	}

	/**
	 * @NoAdminRequired
	 *
	 * Returns information about an album, based on its path
	 *
	 * Used to see if album thumbnails should be generated for a specific folder
	 *
	 * @param string $albumpath
	 *
	 * @return false|array<string,int>|Http\JSONResponse
	 */
	public function getAlbumInfo($albumpath) {
		try {
			$nodeInfo = $this->environment->getNodeInfo($albumpath);

			// Thanks to the AppFramework, Arrays are automatically JSON encoded
			return $nodeInfo;
		} catch (EnvironmentException $exception) {
			return $this->error($exception);
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * Sends back a list of all media types supported by the system
	 *
	 * @return string[]
	 */
	public function getTypes() {
		return $this->infoService->getSupportedMimes();
	}

	/**
	 * @NoAdminRequired
	 *
	 * Returns a list of all images available to the authenticated user
	 *
	 * Authentication can be via a login/password or a token/(password)
	 *
	 * For private galleries, it returns all images, with the full path from the root folder
	 * For public galleries, the path starts from the folder the link gives access to
	 *
	 * @return array|Http\JSONResponse
	 */
	public function getImages() {
		try {
			$currentFolder = $this->request->getParam('currentfolder');
			$imagesFolder = $this->environment->getResourceFromPath($currentFolder);
			
			if ($this->isFolderPrivate($imagesFolder)) {
				return new JSONResponse(['message' => 'Oh Nooooes!', 'success' => false], 500);
			}
			
			$fromRootToFolder = $this->environment->getFromRootToFolder();

			$folderData = [
				'imagesFolder'     => $imagesFolder,
				'fromRootToFolder' => $fromRootToFolder,
			];

			return $this->infoService->getImages($folderData);
		} catch (EnvironmentException $exception) {
			return $this->error($exception);
		}
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
	 * WARNING: Returning a JSON response does not work.
	 *
	 * @param string $images
	 * @param bool $square
	 * @param bool $scale
	 *
	 * @return null|Http\JSONResponse
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
		} catch (EnvironmentException $exception) {
			return $this->error($exception);
		}
	}

	/**
	 * Checks if we're authorised to look for pictures in this folder
	 *
	 * @param Folder $folder
	 *
	 * @return bool
	 */
	private function isFolderPrivate($folder) {
		if ($folder->nodeExists('.nomedia')) {
			return true;
		} else {
			$path = $folder->getPath();
			if ($path !== '' && $path !== '/') {
				$folder = $folder->getParent();

				return $this->isFolderPrivate($folder);
			}
		}

		return false;
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
	 * @return array|Http\JSONResponse
	 */
	private function getThumbnail($image, $square, $scale) {
		list($width, $height, $aspect, $animatedPreview, $base64Encode) =
			$this->thumbnailService->getThumbnailSpecs($square, $scale);

		try {
			$preview = $this->getPreview(
				$image, $width, $height, $aspect, $animatedPreview, $base64Encode
			);
		} catch (ServiceException $exception) {
			return $this->error($exception);
		}
		$thumbnail = $preview['data'];
		if ($width === 200) { // Only fixing the square thumbnails
			//$thumbnail['data'] = $this->previewService->previewValidator();
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
	 * @return mixed
	 */
	private function getPreview(
		$image, $width, $height, $keepAspect = true, $animatedPreview = true, $base64Encode = false
	) {
		$status = Http::STATUS_OK;
		$previewRequired = $this->previewService->isPreviewRequired($image, $animatedPreview);
		if ($previewRequired) {
			$preview = $this->previewService->createPreview(
				$image, $width, $height, $keepAspect, $base64Encode
			);
			if (!$this->previewService->isPreviewValid()) {
				$status = Http::STATUS_NOT_FOUND;
			}
		} else {
			$preview = $this->downloadService->downloadFile($image, $base64Encode);
		}

		return [
			'data'   => $preview,
			'status' => $status
		];
	}

}
