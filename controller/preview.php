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

use OCP\IURLGenerator;
use OCP\ILogger;
use OCP\Files\File;

use OCP\AppFramework\Http;

use OCA\GalleryPlus\Service\ServiceException;
use OCA\GalleryPlus\Service\NotFoundServiceException;
use OCA\GalleryPlus\Service\ThumbnailService;
use OCA\GalleryPlus\Service\PreviewService;
use OCA\GalleryPlus\Service\DownloadService;

/**
 * Class Preview
 *
 * @package OCA\GalleryPlus\Controller
 */
trait Preview {

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
	 * @var ILogger
	 */
	private $logger;
	/**
	 * @type bool
	 */
	private $download = false;


	/**
	 * Retrieves the thumbnail to send back to the browser
	 *
	 * The thumbnail is either a resized preview of the file or the original file
	 * Thumbnails are base64encoded before getting sent back
	 *
	 *
	 * @param int $fileId the ID of the file of which we need a thumbnail preview of
	 * @param bool $square whether the thumbnail should be square
	 * @param double $scale whether we're allowed to scale the preview up
	 *
	 * @return array<string,array|string>
	 */
	private function getThumbnail($fileId, $square, $scale) {
		list($width, $height, $aspect, $animatedPreview, $base64Encode) =
			$this->thumbnailService->getThumbnailSpecs($square, $scale);
		/** @type File $file */
		list($file, $preview, $status, $type) =
			$this->getData(
				$fileId, $width, $height, $aspect, $animatedPreview, $base64Encode
			);
		if ($preview === null) {
			if ($status !== Http::STATUS_NOT_FOUND) {
				$preview = ['preview' => null, 'mimetype' => $file->getMimeType()];
			}
		} else {
			if ($type === 'preview') {
				$preview['preview'] =
					$this->previewService->previewValidator($square, $base64Encode);
			}
		}

		return [$preview, $status];
	}

	/**
	 * Returns either a generated preview, the file as-is or an empty object
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
	private function getData(
		$fileId, $width, $height, $keepAspect = true, $animatedPreview = true, $base64Encode = false
	) {
		try {
			/** @type File $file */
			$file = $this->previewService->getResourceFromId($fileId);
			if (!is_null($file)) {
				$previewRequired = $this->isPreviewRequired($file, $animatedPreview);
				$data = $this->getPreviewData(
					$file, $previewRequired, $width, $height, $keepAspect, $base64Encode
				);
			} else {
				// Uncaught problem, should never reach this point...
				$data = $this->getErrorData(Http::STATUS_NOT_FOUND);
			}
		} catch (ServiceException $exception) {
			$file = null;
			$data = $this->getExceptionData($exception);
		}
		array_unshift($data, $file);

		return $data;
	}

	/**
	 * Returns true if we need to generate a preview for that file
	 *
	 * @param $file
	 * @param bool $animatedPreview
	 *
	 * @return bool
	 */
	private function isPreviewRequired($file, $animatedPreview) {
		$previewRequired = false;

		if (!$this->download) {
			$previewRequired =
				$this->previewService->isPreviewRequired($file, $animatedPreview);
		}

		return $previewRequired;
	}

	/**
	 * @param $file
	 * @param $previewRequired
	 * @param $width
	 * @param $height
	 * @param $keepAspect
	 * @param $base64Encode
	 *
	 * @return array
	 */
	private function getPreviewData(
		$file, $previewRequired, $width, $height, $keepAspect, $base64Encode
	) {
		$status = Http::STATUS_OK;
		if ($previewRequired) {
			$type = 'preview';
			$preview = $this->previewService->createPreview(
				$file, $width, $height, $keepAspect, $base64Encode
			);
		} else {
			$type = 'download';
			$preview = $this->downloadService->downloadFile($file, $base64Encode);
		}
		if (!$preview) {
			$type = 'error';
			$status = Http::STATUS_INTERNAL_SERVER_ERROR;
			$preview = null;
		}

		return [$preview, $status, $type];
	}

	/**
	 * Returns an error array
	 *
	 * @param $status
	 *
	 * @return array<null|int|string>
	 */
	private function getErrorData($status) {
		return [null, $status, 'error'];
	}

	/**
	 * Returns an error array
	 *
	 * @param $exception
	 *
	 * @return array<null|int|string>
	 */
	private function getExceptionData($exception) {
		if ($exception instanceof NotFoundServiceException) {
			$status = Http::STATUS_NOT_FOUND;
		} else {
			$status = Http::STATUS_INTERNAL_SERVER_ERROR;
		}

		return $this->getErrorData($status);
	}

	/**
	 * Returns an URL based on the HTTP status code
	 *
	 * @param string $appName
	 * @param int $status
	 *
	 * @return string
	 */
	private function getErrorUrl($appName, $status) {
		return $this->urlGenerator->linkToRoute(
			$appName . '.page.error_page',
			[
				'message' => 'There was a problem accessing the file',
				'code'    => $status
			]
		);
	}

}
