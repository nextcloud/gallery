<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\Preview;

use OCP\Files\File;

use OCP\AppFramework\Http;

use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Generates previews
 *
 * @todo On OC8.1, replace \OC\Preview with OC::$server->getPreviewManager()
 *
 * @package OCA\GalleryPlus\Preview
 */
class Preview {

	/**
	 * @type SmarterLogger
	 */
	private $logger;
	/**
	 * @type string
	 */
	private $owner;
	/**
	 * @type \OC\Preview
	 */
	private $preview;
	/**
	 * @type File
	 */
	private $file;
	/**
	 * @type int
	 */
	private $maxX;
	/**
	 * @type int
	 */
	private $maxY;


	/**
	 * Constructor
	 *
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		SmarterLogger $logger
	) {
		$this->logger = $logger;
	}

	/**
	 * Initialises the object
	 *
	 * @fixme Private API, but can't use the PreviewManager yet as it's incomplete
	 *
	 * @param string $owner
	 * @param File $file
	 * @param string $imagePathFromFolder
	 */
	public function setupView($owner, $file, $imagePathFromFolder) {
		$this->owner = $owner;
		$this->file = $file;
		$this->preview = new \OC\Preview($owner, 'files', $imagePathFromFolder);
	}

	/**
	 * Decides if we should download the file instead of generating a preview
	 *
	 * @param bool $animatedPreview
	 * @param bool $download
	 *
	 * @return bool
	 */
	public function previewRequired($animatedPreview, $download) {
		$mime = $this->file->getMimeType();

		if ($mime === 'image/svg+xml') {
			return $this->isSvgPreviewRequired();
		}
		if ($mime === 'image/gif') {
			return $this->isGifPreviewRequired($animatedPreview);
		}

		return !$download;
	}

	/**
	 * Decides if we should download the SVG or generate a preview
	 *
	 * SVGs are downloaded if the SVG converter is disabled
	 * Files of any media type are downloaded if requested by the client
	 *
	 * @return bool
	 */
	private function isSvgPreviewRequired() {
		if (!$this->preview->isMimeSupported($this->file->getMimeType())) {
			return false;
		}

		return true;
	}

	/**
	 * Decides if we should download the GIF or generate a preview
	 *
	 * GIFs are downloaded if they're animated and we want to show
	 * animations
	 *
	 * @param bool $animatedPreview
	 *
	 * @return bool
	 */
	private function isGifPreviewRequired($animatedPreview) {
		$animatedGif = $this->isGifAnimated();

		if ($animatedPreview && $animatedGif) {
			return false;
		}

		return true;
	}

	/**
	 * Tests if a GIF is animated
	 *
	 * An animated gif contains multiple "frames", with each frame having a
	 * header made up of:
	 *    * a static 4-byte sequence (\x00\x21\xF9\x04)
	 *    * 4 variable bytes
	 *    * a static 2-byte sequence (\x00\x2C) (Photoshop uses \x00\x21)
	 *
	 * We read through the file until we reach the end of the file, or we've
	 * found at least 2 frame headers
	 *
	 * @link http://php.net/manual/en/function.imagecreatefromgif.php#104473
	 *
	 * @return bool
	 */
	private function isGifAnimated() {
		$fileHandle = $this->file->fopen('rb');
		$count = 0;
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
	 * Returns a preview based on OC's preview class and our custom methods
	 *
	 * We don't throw an exception when the preview generator fails,
	 * instead, until the Preview class is fixed, we send the mime
	 * icon along with a 415 error code.
	 *
	 * @fixme setKeepAspect is missing from public interface.
	 *     https://github.com/owncloud/core/issues/12772
	 *
	 * @param int $maxX
	 * @param int $maxY
	 * @param bool $keepAspect
	 *
	 * @return array
	 */
	public function preparePreview($maxX, $maxY, $keepAspect) {
		$this->preview->setMaxX($this->maxX = $maxX);
		$this->preview->setMaxY($this->maxY = $maxY);
		$this->preview->setScalingUp(false); // TODO: Need to read from settings
		$this->preview->setKeepAspect($keepAspect);
		$this->logger->debug("[PreviewService] Generating a new preview");
		/** @type \OC_Image $previewData */
		$previewData = $this->preview->getPreview();
		if ($previewData->valid()) {
			$perfectPreview = $this->previewValidator($maxX, $maxY);
		} else {
			$this->logger->debug("[PreviewService] ERROR! Did not get a preview");
			$perfectPreview = array(
				'preview' => $this->getMimeIcon(),
				'status'  => Http::STATUS_UNSUPPORTED_MEDIA_TYPE
			);
		}
		$perfectPreview['mimetype'] = 'image/png'; // Previews are always sent as PNG

		return $perfectPreview;
	}

	/**
	 * Makes sure we return previews of the asked dimensions and fix the cache
	 * if necessary
	 *
	 * The Preview class of OC7 sometimes return previews which are either
	 * wider or smaller than the asked dimensions. This happens when one of the
	 * original dimension is smaller than what is asked for
	 *
	 * @return array<resource,int>
	 */
	private function previewValidator() {
		$maxX = $this->maxX;
		$maxY = $this->maxY;
		$previewData = $this->preview->getPreview();
		$previewX = $previewData->width();
		$previewY = $previewData->height();
		$minWidth = 200; // Only fixing the square thumbnails

		if (($previewX > $maxX
			 || ($previewX < $maxX || $previewY < $maxY)
				&& $maxX === $minWidth)
		) {
			$fixedPreview = $this->fixPreview($previewData, $maxX, $maxY);
			$previewData = $this->fixPreviewCache($fixedPreview);
		}

		return array(
			'preview' => $previewData,
			'status'  => Http::STATUS_OK
		);
	}

	/**
	 * Makes a preview fit in the asked dimension and fills the empty space
	 *
	 * @param \OC_Image $previewData
	 *
	 * @return resource
	 */
	private function fixPreview($previewData) {
		$previewWidth = $previewData->width();
		$previewHeight = $previewData->height();
		$fixedPreview = imagecreatetruecolor($this->maxX, $this->$maxY); // Creates the canvas

		// We make the background transparent
		imagealphablending($fixedPreview, false);
		$transparency = imagecolorallocatealpha($fixedPreview, 0, 0, 0, 127);
		imagefill($fixedPreview, 0, 0, $transparency);
		imagesavealpha($fixedPreview, true);

		$newDimensions =
			$this->calculateNewDimensions($previewWidth, $previewHeight, $this->maxX, $this->$maxY);

		imagecopyresampled(
			$fixedPreview, $previewData->resource(), $newDimensions['newX'], $newDimensions['newY'],
			0, 0, $newDimensions['newWidth'], $newDimensions['newHeight'],
			$previewWidth, $previewHeight
		);

		return $fixedPreview;
	}

	/**
	 * Calculates the new dimensions so that it fits in the dimensions requested by the client
	 *
	 * @link https://stackoverflow.com/questions/3050952/resize-an-image-and-fill-gaps-of-proportions-with-a-color
	 *
	 * @param int $previewWidth
	 * @param int $previewHeight
	 *
	 * @return array
	 */
	private function calculateNewDimensions($previewWidth, $previewHeight) {
		if (($previewWidth / $previewHeight) >= ($maxX = $this->maxX / $maxY = $this->$maxY)) {
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

		return array(
			'newX'      => $newX,
			'newY'      => $newY,
			'newWidth'  => $newWidth,
			'newHeight' => $newHeight,
		);
	}

	/**
	 * Fixes the preview cache by replacing the broken thumbnail with ours
	 *
	 * @param resource $fixedPreview
	 *
	 * @return mixed
	 */
	private function fixPreviewCache($fixedPreview) {
		$owner = $this->owner;
		$file = $this->file;
		$preview = $this->preview;
		$fixedPreviewObject = new \OC_Image($fixedPreview); // FIXME: Private API
		$previewData = $preview->getPreview();

		// Get the location where the broken thumbnail is stored
		// FIXME: Private API
		$thumbnailFolder = \OC::$SERVERROOT . '/data/' . $owner . '/';
		$thumbnail = $thumbnailFolder . $preview->isCached($file->getId());

		// Caching it for next time
		if ($fixedPreviewObject->save($thumbnail)) {
			$previewData = $fixedPreviewObject->data();
		}

		return $previewData;
	}

	/**
	 * Returns the media type icon when the server fails to generate a preview
	 *
	 * It's not more efficient for the browser to download the mime icon
	 * directly and won't be necessary once the Preview class sends the mime
	 * icon when it can't generate a proper preview
	 * https://github.com/owncloud/core/pull/12546
	 *
	 * @return \OC_Image
	 */
	private function getMimeIcon() {
		$mime = $this->file->getMimeType();
		$iconData = new \OC_Image(); // FIXME: Private API

		// FIXME: private API
		$image = \OC::$SERVERROOT . mimetype_icon($mime);
		// OC8 version
		//$image = $this->serverRoot() . \OCP\Template::mimetype_icon($mime);

		$iconData->loadFromFile($image);

		return $iconData;
	}

}