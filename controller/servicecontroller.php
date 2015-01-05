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

use OCP\ILogger;
use OCP\IURLGenerator;
use OCP\IRequest;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

use OCA\GalleryPlus\Service\InfoService;
use OCA\GalleryPlus\Service\ThumbnailService;
use OCA\GalleryPlus\Service\PreviewService;
use OCA\GalleryPlus\Service\ServiceException;

/**
 * Class ServiceController
 *
 * @package OCA\GalleryPlus\Controller
 */
class ServiceController extends Controller {

	use JsonHttpError;

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
	 * @type IURLGenerator
	 */
	private $urlGenerator;
	/**
	 * @type ILogger
	 */
	private $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param InfoService $infoService
	 * @param ThumbnailService $thumbnailService
	 * @param PreviewService $previewService
	 * @param IURLGenerator $urlGenerator
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		InfoService $infoService,
		ThumbnailService $thumbnailService,
		PreviewService $previewService,
		IURLGenerator $urlGenerator,
		ILogger $logger
	) {
		parent::__construct($appName, $request);

		$this->infoService = $infoService;
		$this->thumbnailService = $thumbnailService;
		$this->previewService = $previewService;
		$this->urlGenerator = $urlGenerator;
		$this->logger = $logger;
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
	 * @return array|Http\JSONResponse
	 */
	public function getAlbumInfo($albumpath) {
		try {
			// Thanks to the AppFramework, Arrays are automatically JSON encoded
			return $this->infoService->getAlbumInfo($albumpath);
		} catch (ServiceException $exception) {
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
	 * Returns a list of all images available to the logged-in user
	 *
	 * @return array[string[]]|Http\JSONResponse
	 */
	public function getImages() {
		try {
			return $this->infoService->getImages();
		} catch (ServiceException $exception) {
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
	 * @param string $images
	 * @param bool $square
	 * @param bool $scale
	 *
	 * @return Http\JSONResponse
	 */
	public function getThumbnails($images, $square, $scale) {
		try {
			$this->thumbnailService->getAlbumThumbnails(
				$images, $square, $scale
			);
		} catch (ServiceException $exception) {
			return $this->error($exception);
		}
	}

	/**
	 * @NoAdminRequired
	 *
	 * Shows a large preview of a file
	 *
	 * @param string $file
	 * @param int $x
	 * @param int $y
	 *
	 * @return \OCA\GalleryPlus\Http\ImageResponse|Http\JSONResponse
	 */
	public function showPreview($file, $x, $y) {
		try {
			return $this->previewService->showPreview(
				$file, $x, $y
			);
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
			return $this->previewService->downloadPreview($file);
		} catch (ServiceException $exception) {
			return $this->error($exception);
		}
	}

}