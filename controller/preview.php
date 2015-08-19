<?php
/**
 * ownCloud - gallery
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

namespace OCA\Gallery\Controller;

use OCP\IURLGenerator;
use OCP\ILogger;
use OCP\Files\File;

use OCP\AppFramework\Http;

use OCA\Gallery\Service\ServiceException;
use OCA\Gallery\Service\NotFoundServiceException;
use OCA\Gallery\Service\ThumbnailService;
use OCA\Gallery\Service\PreviewService;
use OCA\Gallery\Service\DownloadService;

/**
 * Class Preview
 *
 * @package OCA\Gallery\Controller
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
				$data = $this->getPreviewData(
					$file, $animatedPreview, $width, $height, $keepAspect, $base64Encode
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
	 * @param File $file
	 * @param bool $animatedPreview
	 * @param int $width
	 * @param int $height
	 * @param bool $keepAspect
	 * @param bool $base64Encode
	 *
	 * @return array
	 */
	private function getPreviewData(
		$file, $animatedPreview, $width, $height, $keepAspect, $base64Encode
	) {
		$status = Http::STATUS_OK;
		if ($this->previewService->isPreviewRequired($file, $animatedPreview)) {
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

}
