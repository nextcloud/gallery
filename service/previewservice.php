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
use OCP\IPreview;

use OCP\AppFramework\Http;

use OCA\GalleryPlus\Http\ImageResponse;
use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Generates previews
 *
 * @todo On OC8.1, replace \OC\Preview with OC::$server->getPreviewManager()
 *
 * @package OCA\GalleryPlus\Service
 */
class PreviewService extends Service {

	/**
	 * @type EnvironmentService
	 */
	private $environmentService;
	/**
	 * @type mixed
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
	 * @param IPreview $previewManager
	 */
	public function __construct(
		$appName,
		EnvironmentService $environmentService,
		SmarterLogger $logger,
		IPreview $previewManager
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

		return new ImageResponse($preview['data'], $preview['status']);
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
		$response = array();

		$env = $this->environmentService->getEnv();
		$owner = $env['owner'];

		/** @type Folder $folder */
		$folder = $env['folder'];
		$imagePathFromFolder = $env['relativePath'] . $image;
		/** @type File $file */
		$file = $this->getResource($folder, $imagePathFromFolder);

		// FIXME: Private API, but can't use the PreviewManager yet as it's incomplete
		$preview = new \OC\Preview($owner, 'files', $imagePathFromFolder);

		$previewRequired =
			$this->previewRequired($file, $preview);

		if ($previewRequired) {
			$this->logger->debug("[PreviewService] Generating a new preview");

			$perfectPreview =
				$this->preparePreview($owner, $file, $preview, $maxX, $maxY);

			$previewData = $perfectPreview['previewData'];
			$previewMime = $perfectPreview['previewMime'];
			$statusCode = $perfectPreview['statusCode'];
		} else {
			$this->logger->debug(
				"[PreviewService] Downloading file {file} as-is",
				array(
					'file' => $image
				)
			);

			$previewData = $file->getContent();
			$previewMime = $file->getMimeType();
			$statusCode = Http::STATUS_OK;
		}

		$previewData = $this->base64EncodeIfNecessary($previewData);

		$response['data'] = array(
			'path'     => $image,
			'mimetype' => $previewMime,
			'preview'  => $previewData,
		);

		$response['status'] = $statusCode;

		/*$this->logger->debug(
			"[PreviewService] PREVIEW Path : {path} / size: {size} / mime: {mimetype} / status: {status}",
			array(
				'path'     => $response['data']['path'],
				'mimetype' => $response['data']['mimetype'],
				'status'   => $response['status']
			)
		);*/

		return $response;
	}

	/**
	 * Decides if we should download the file instead of generating a preview
	 *
	 * @param File $file
	 * @param \OC\Preview $preview
	 *
	 * @return bool
	 */
	private function previewRequired($file, $preview) {
		$animatedPreview = $this->animatedPreview;
		$download = $this->download;
		$mime = $file->getMimeType();
		$animatedGif = $this->isGifAnimated($file);

		/**
		 * GIFs are downloaded if they're animated and we want to show
		 * animations
		 * SVGs are downloaded if the SVG converter is disabled
		 * Files of any media type are downloaded if requested by the client
		 */
		if (($mime === 'image/gif' && $animatedPreview && $animatedGif)
			|| ($mime === 'image/svg+xml' && !$preview->isMimeSupported($mime)
				|| $download === true)
		) {
			return false;
		}

		return true;
	}

	/**
	 * Returns a preview based on OC's preview class and our custom methods
	 *
	 * @param string $owner
	 * @param File $file
	 * @param \OC\Preview $preview
	 * @param int $maxX
	 * @param int $maxY
	 *
	 * @return array
	 */
	private function preparePreview($owner, $file, $preview, $maxX, $maxY) {
		$keepAspect = $this->keepAspect;
		$scalingUp = false; // TODO: Need to read from settings
		$preview->setMaxX($maxX);
		$preview->setMaxY($maxY);
		$preview->setScalingUp($scalingUp);
		// Crop and center for square pictures. Resize for large thumbnails
		$preview->setKeepAspect(
			$keepAspect
		); // FIXME: Missing from public interface. https://github.com/owncloud/core/issues/12772
		$perfectPreview = array();

		// Returns an \OC_Image instance
		$previewData = $preview->getPreview();
		if ($previewData->valid()) {
			/**
			 * We make sure we return a preview which matches the asked
			 * dimensions and repair the cache if needed
			 */
			$previewData = $this->previewValidator(
				$owner, $file, $preview, $maxX, $maxY
			);
			$statusCode = Http::STATUS_OK;
		} else {
			$this->logger->debug(
				"[PreviewService] ERROR! Did not get a preview"
			);

			/**
			 * We don't throw an exception when the preview generator fails,
			 * instead, until the Preview classe is fixed, we send the mime
			 * icon along with a 415 error code.
			 */
			$previewData = $this->getMimeIcon($file);
			$statusCode = Http::STATUS_UNSUPPORTED_MEDIA_TYPE;
		}

		// Previews are always sent as PNG
		$previewMime = 'image/png';

		$perfectPreview['previewData'] = $previewData;
		$perfectPreview['previewMime'] = $previewMime;
		$perfectPreview['statusCode'] = $statusCode;

		return $perfectPreview;
	}

	/**
	 * Tests if a GIF is animated
	 *
	 * @link http://php.net/manual/en/function.imagecreatefromgif.php#104473
	 *
	 * @param File $file
	 *
	 * @return bool
	 */
	private function isGifAnimated($file) {
		$fileHandle = $file->fopen('rb');
		$count = 0;
		/**
		 * An animated gif contains multiple "frames", with each frame having a
		 * header made up of:
		 *    * a static 4-byte sequence (\x00\x21\xF9\x04)
		 *    * 4 variable bytes
		 *    * a static 2-byte sequence (\x00\x2C) (Photoshop uses \x00\x21)
		 *
		 * We read through the file until we reach the end of the file, or we've
		 * found at least 2 frame headers
		 */
		while (!feof($fileHandle) && $count < 2) {
			$chunk = fread($fileHandle, 1024 * 100); //read 100kb at a time
			$count += preg_match_all(
				'#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches
			);
		}

		fclose($fileHandle);

		return $count > 1;
	}

	/**
	 * Make sure we return previews of the asked dimensions
	 *
	 * The Preview class of OC7 sometimes return previews which are either
	 * wider or smaller than the asked dimensions. This happens when one of the
	 * original dimension is smaller than what is asked for
	 *
	 * @param string $owner
	 * @param File $file
	 * @param \OC\Preview $preview
	 * @param int $maxX
	 * @param int $maxY
	 *
	 * @return string
	 */
	private function previewValidator(
		$owner, $file, $preview, $maxX, $maxY
	) {
		$previewData = $preview->getPreview();
		$previewX = $previewData->width();
		$previewY = $previewData->height();
		$minWidth = 200; // Only fixing the square thumbnails

		if (($previewX > $maxX
			 || ($previewX < $maxX || $previewY < $maxY)
				&& $maxX === $minWidth)
		) {
			$fixedPreview = $this->fixPreview($previewData, $maxX, $maxY);
			$fixedPreviewObject =
				new \OC_Image($fixedPreview); // FIXME: Private API

			// Get the location where the broken thumbnail is stored
			// FIXME: Private API
			$thumbPath = \OC::$SERVERROOT . '/data/' . $owner . '/'
						 . $preview->isCached($file->getId());

			// Caching it for next time
			if ($fixedPreviewObject->save($thumbPath)) {
				$previewData = $fixedPreviewObject->data();
			}
		}

		return $previewData;
	}

	/**
	 * Makes a preview fit in the asked dimension and fills the empty space
	 *
	 * @param \OC_Image $previewData
	 * @param int $maxX
	 * @param int $maxY
	 *
	 * @return resource
	 */
	private function fixPreview($previewData, $maxX, $maxY) {
		$previewWidth = $previewData->width();
		$previewHeight = $previewData->height();

		// Creates the canvas
		$fixedPreview = imagecreatetruecolor($maxX, $maxY);
		// We make the background transparent
		imagealphablending($fixedPreview, false);
		$transparency =
			imagecolorallocatealpha($fixedPreview, 0, 0, 0, 127);
		imagefill($fixedPreview, 0, 0, $transparency);
		imagesavealpha($fixedPreview, true);

		/** @link https://stackoverflow.com/questions/3050952/resize-an-image-and-fill-gaps-of-proportions-with-a-color */
		if (($previewWidth / $previewHeight) >= ($maxX / $maxY)) {
			$newWidth = $maxX;
			$newHeight = $previewHeight * ($maxX / $previewWidth);
			$newX = 0;
			$newY = round(abs($maxY - $newHeight) / 2);
		} else {
			$newWidth = $previewWidth * ($maxY / $previewHeight);
			$newHeight = $maxY;
			$newX = round(abs($maxX - $newWidth) / 2);
			$newY = 0;
		}

		imagecopyresampled(
			$fixedPreview, $previewData->resource(), $newX, $newY, 0, 0,
			$newWidth,
			$newHeight, $previewWidth, $previewHeight
		);

		return $fixedPreview;
	}

	/**
	 * Returns the media type icon when the server fails to generate a preview
	 *
	 * It's not more efficient for the browser to download the mime icon
	 * directly and won't be necessary once the Preview class sends the mime
	 * icon when it can't generate a proper preview
	 * https://github.com/owncloud/core/pull/12546
	 *
	 * @param File $file
	 *
	 * @return \OC_Image
	 */
	private function getMimeIcon($file) {
		$mime = $file->getMimeType();
		$iconData = new \OC_Image(); // FIXME: Private API

		// FIXME: private API
		$image = \OC::$SERVERROOT . mimetype_icon($mime);
		// OC8 version
		//$image = $this->serverRoot() . \OCP\Template::mimetype_icon($mime);

		$iconData->loadFromFile($image);

		return $iconData;
	}

	/**
	 * Returns base64 encoded data of a preview
	 *
	 * @param \OC_Image|string $previewData
	 *
	 * @return string
	 */
	private function base64EncodeIfNecessary($previewData) {
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