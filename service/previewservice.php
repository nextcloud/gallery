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
 * @copyright Robin Appelman 2012-2015
 */

namespace OCA\GalleryPlus\Service;

use OCP\Files\File;
use OCP\Files\Folder;

use OCP\AppFramework\Http;

use OCA\GalleryPlus\Http\ImageResponse;
use OCA\GalleryPlus\Preview\Preview;
use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Generates previews
 *
 * @package OCA\GalleryPlus\Service
 */
class PreviewService extends Service {

	/**
	 * @type EnvironmentService
	 */
	private $environmentService;
	/**
	 * @type Preview
	 */
	private $previewManager;
	/**
	 * @type bool
	 */
	private $animatedPreview = true;
	/**
	 * @type bool
	 */
	private $keepAspect = true;
	/**
	 * @type bool
	 */
	private $base64Encode = false;
	/**
	 * @type bool
	 */
	private $download = false;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param EnvironmentService $environmentService
	 * @param SmarterLogger $logger
	 * @param Preview $previewManager
	 */
	public function __construct(
		$appName,
		EnvironmentService $environmentService,
		SmarterLogger $logger,
		Preview $previewManager
	) {
		parent::__construct($appName, $logger);

		$this->environmentService = $environmentService;
		$this->previewManager = $previewManager;
	}

	/**
	 * @param string $image
	 * @param int $maxX
	 * @param int $maxY
	 * @param bool $keepAspect
	 *
	 * @return string[] preview data
	 */
	public function createThumbnails($image, $maxX, $maxY, $keepAspect) {
		$this->animatedPreview = false;
		$this->base64Encode = true;
		$this->keepAspect = $keepAspect;

		return $this->createPreview($image, $maxX, $maxY);
	}


	/**
	 * Sends either a large preview of the requested file or the original file
	 * itself
	 *
	 * @param string $image
	 * @param int $maxX
	 * @param int $maxY
	 *
	 * @return ImageResponse
	 */
	public function showPreview($image, $maxX, $maxY) {
		$preview = $this->createPreview($image, $maxX, $maxY);

		return new ImageResponse($preview, $preview['status']);
	}

	/**
	 * Downloads the requested file
	 *
	 * @param string $image
	 *
	 * @return ImageResponse
	 */
	public function downloadPreview($image) {
		$this->download = true;

		return $this->showPreview($image, null, null);
	}

	/**
	 * Creates an array containing everything needed to render a preview in the
	 * browser
	 *
	 * If the browser can use the file as-is or if we're asked to send it
	 * as-is, then we simply let the browser download the file, straight from
	 * Files
	 *
	 * Some files are base64 encoded. Explicitly for files which are downloaded
	 * (cached Thumbnails, SVG, GIFs) and via __toStrings for the previews
	 * which
	 * are instances of \OC_Image
	 *
	 * We check that the preview returned by the Preview class can be used by
	 * the browser. If not, we send the mime icon and change the status code so
	 * that the client knows that the process failed
	 *
	 * @todo Get the max size from the settings
	 *
	 * @param string $image path to the image, relative to the user folder
	 * @param int $maxX asked width for the preview
	 * @param int $maxY asked height for the preview
	 *
	 * @return array preview data
	 */
	private function createPreview($image, $maxX = 0, $maxY = 0) {
		$env = $this->environmentService->getEnv();
		$owner = $env['owner'];
		/** @type Folder $folder */
		$folder = $env['folder'];
		$imagePathFromFolder = $env['relativePath'] . $image;
		/** @type File $file */
		$file = $this->getResource($folder, $imagePathFromFolder);

		$this->previewManager->setupView($owner, $file, $imagePathFromFolder);

		$previewRequired =
			$this->previewManager->previewRequired($this->animatedPreview, $this->download);

		if ($previewRequired) {
			$perfectPreview =
				$this->previewManager->preparePreview($maxX, $maxY, $this->keepAspect);
		} else {
			$perfectPreview = $this->prepareDownload($file, $image);
		}
		$perfectPreview['preview'] = $this->base64EncodeCheck($perfectPreview['preview']);
		$perfectPreview['path'] = $image;

		/*$this->logger->debug(
			"[PreviewService] Path : {path} / size: {size} / mime: {mimetype} / status: {status}",
			array(
				'path'     => $perfectPreview['data']['path'],
				'mimetype' => $perfectPreview['data']['mimetype'],
				'status'   => $perfectPreview['status']
			)
				);*/

		return $perfectPreview;
	}

	/**
	 * Returns the data needed to make a file available for download
	 *
	 * @param File $file
	 * @param string $image
	 *
	 * @return array
	 */
	private function prepareDownload($file, $image) {
		$this->logger->debug("[PreviewService] Downloading file {file} as-is", ['file' => $image]);

		return array(
			'preview'  => $file->getContent(),
			'mimetype' => $file->getMimeType(),
			'status'   => Http::STATUS_OK
		);
	}

	/**
	 * Returns base64 encoded data of a preview
	 *
	 * @param \OC_Image|string $previewData
	 *
	 * @return \OC_Image|string
	 */
	private function base64EncodeCheck($previewData) {
		$base64Encode = $this->base64Encode;

		if ($base64Encode === true) {
			if ($previewData instanceof \OC_Image) {
				$previewData = (string)$previewData;
			} else {
				$previewData = base64_encode($previewData);
			}
		}

		return $previewData;
	}

}