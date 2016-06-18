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
 * @copyright Olivier Paroz 2014-2016
 * @copyright Robin Appelman 2012-2014
 */

namespace OCA\Gallery\Controller;

use OCP\IURLGenerator;
use OCP\ILogger;
use OCP\Files\File;

use OCP\AppFramework\Http;

use OCA\Gallery\Service\ServiceException;
use OCA\Gallery\Service\NotFoundServiceException;
use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\ThumbnailService;
use OCA\Gallery\Service\PreviewService;
use OCA\Gallery\Service\DownloadService;

/**
 * Class Preview
 *
 * @package OCA\Gallery\Controller
 */
trait Preview {

	use HttpError;

	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var ConfigService */
	private $configService;
	/**  @var ThumbnailService */
	private $thumbnailService;
	/**  @var PreviewService */
	private $previewService;
	/** @var DownloadService */
	private $downloadService;
	/** @var ILogger */
	private $logger;
	/** @type bool */
	private $download = false;

	/**
	 * Exits the controller in a live environment and throws an exception when testing
	 */
	protected function exitController() {
		if (defined('PHPUNIT_RUN')) {
			throw new \Exception();
			// @codeCoverageIgnoreStart
		} else {
			exit();
		}
		// @codeCoverageIgnoreEnd
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
	 * @param double $scale whether we're allowed to scale the preview up
	 *
	 * @return array<string,array|string>
	 */
	private function getThumbnail($fileId, $square, $scale) {
		list($width, $height, $aspect, $animatedPreview, $base64Encode) =
			$this->thumbnailService->getThumbnailSpecs($square, $scale);
		/** @type File $file */
		list($file, $preview, $status) =
			$this->getData(
				$fileId, $width, $height, $aspect, $animatedPreview, $base64Encode
			);
		if ($preview === null) {
			$preview = $this->prepareEmptyThumbnail($file, $status);
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
		/** @type File $file */
		list($file, $status) = $this->getFile($fileId);
		try {
			if (!is_null($file)) {
				$data = $this->getPreviewData(
					$file, $animatedPreview, $width, $height, $keepAspect, $base64Encode
				);
			} else {
				$data = $this->getErrorData($status);
			}
		} catch (ServiceException $exception) {
			$data = $this->getExceptionData($exception);
		}
		array_unshift($data, $file);

		return $data;
	}

	/**
	 * Returns the file of which a preview will be generated
	 *
	 * @param int $fileId
	 *
	 * @return array<File|int|null>
	 */
	private function getFile($fileId) {
		$status = Http::STATUS_OK;
		try {
			/** @type File $file */
			$file = $this->previewService->getFile($fileId);
			$this->configService->validateMimeType($file->getMimeType());
		} catch (ServiceException $exception) {
			$file = null;
			$status = $this->getHttpStatusCode($exception);
		}

		return [$file, $status];
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
			$preview = $this->previewService->createPreview(
				$file, $width, $height, $keepAspect, $base64Encode
			);
		} else {
			$preview = $this->downloadService->downloadFile($file, $base64Encode);
		}
		if (!$preview) {
			list($preview, $status) = $this->getErrorData();
		}

		return [$preview, $status];
	}

	/**
	 * Returns an error array
	 *
	 * @param $status
	 *
	 * @return array<null|int>
	 */
	private function getErrorData($status = Http::STATUS_INTERNAL_SERVER_ERROR) {
		return [null, $status];
	}

	/**
	 * Returns an error array
	 *
	 * @param ServiceException $exception
	 *
	 * @return array<null|int|string>
	 */
	private function getExceptionData($exception) {
		$code = $this->getHttpStatusCode($exception);

		return $this->getErrorData($code);
	}

	/**
	 * Prepares an empty Thumbnail array to send back
	 *
	 * When we can't even get the file information, we send an empty mimeType
	 *
	 * @param File $file
	 * @param int $status
	 *
	 * @return array<string,null|string>
	 */
	private function prepareEmptyThumbnail($file, $status) {
		$thumbnail = [];
		if ($status !== Http::STATUS_NOT_FOUND) {
			$mimeType = '';
			if ($file) {
				$mimeType = $file->getMimeType();
			}
			$thumbnail = ['preview' => null, 'mimetype' => $mimeType];
		}

		return $thumbnail;
	}

}
