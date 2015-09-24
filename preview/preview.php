<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\Gallery\Preview;

use OCP\IConfig;
use OCP\Image;
use OCP\Files\File;
use OCP\IPreview;
use OCP\ILogger;

/**
 * Generates previews
 *
 * @todo On OC8.2, replace \OC\Preview with IPreview
 *
 * @package OCA\Gallery\Preview
 */
class Preview {

	/**
	 * @var string
	 */
	private $dataDir;
	/**
	 * @var mixed
	 */
	private $previewManager;
	/**
	 * @var ILogger
	 */
	private $logger;
	/**
	 * @var string
	 */
	private $userId;
	/**
	 * @var \OC\Preview
	 */
	private $preview;
	/**
	 * @var File
	 */
	private $file;
	/**
	 * @var int[]
	 */
	private $dims;

	/**
	 * Constructor
	 *
	 * @param IConfig $config
	 * @param IPreview $previewManager
	 * @param ILogger $logger
	 */
	public function __construct(
		IConfig $config,
		IPreview $previewManager,
		ILogger $logger
	) {
		$this->dataDir = $config->getSystemValue('datadirectory');
		$this->previewManager = $previewManager;
		$this->logger = $logger;
	}

	/**
	 * Returns true if the passed mime type is supported
	 *
	 * @param string $mimeType
	 *
	 * @return boolean
	 */
	public function isMimeSupported($mimeType = '*') {
		return $this->previewManager->isMimeSupported($mimeType);
	}

	/**
	 * Initialises the view which will be used to access files and generate previews
	 *
	 * @fixme Private API, but can't use the PreviewManager yet as it's incomplete
	 *
	 * @param string $userId
	 * @param File $file
	 * @param string $imagePathFromFolder
	 */
	public function setupView($userId, $file, $imagePathFromFolder) {
		$this->userId = $userId;
		$this->file = $file;
		$imagePathFromFolder = ltrim($imagePathFromFolder, '/');
		$this->preview = new \OC\Preview($userId, 'files', $imagePathFromFolder);
	}

	/**
	 * Returns a preview based on OC's preview class and our custom methods
	 *
	 * We check that the preview returned by the Preview class can be used by
	 * the browser. If not, we send "false" to the controller
	 *
	 * @fixme setKeepAspect is missing from public interface.
	 *     https://github.com/owncloud/core/issues/12772
	 *
	 * @param int $maxWidth
	 * @param int $maxHeight
	 * @param bool $keepAspect
	 *
	 * @return array<string,string|\OC_Image>|false
	 */
	public function preparePreview($maxWidth, $maxHeight, $keepAspect) {
		$this->dims = [$maxWidth, $maxHeight];

		$previewData = $this->getPreviewFromCore($keepAspect);

		if ($previewData && $previewData->valid()) {
			$preview = [
				'preview'  => $previewData,
				'mimetype' => $previewData->mimeType()
			];
		} else {
			$preview = false;
		}

		return $preview;
	}

	/**
	 * Makes sure we return previews of the asked dimensions and fix the cache
	 * if necessary
	 *
	 * The Preview class sometimes return previews which are either wider or
	 * smaller than the asked dimensions. This happens when one of the original
	 * dimension is smaller than what is asked for
	 *
	 * For square previews, we also need to make sure the entire surface is filled in order to make
	 * it easier to work with when building albums
	 *
	 * @param bool $square
	 *
	 * @return \OC_Image
	 */
	public function previewValidator($square) {
		list($maxWidth, $maxHeight) = $this->dims;
		$previewData = $this->preview->getPreview();
		$previewWidth = $previewData->width();
		$previewHeight = $previewData->height();

		if ($previewWidth > $maxWidth || $previewHeight > $maxHeight) {
			$previewData = $this->fixPreview(
				$previewData, $previewWidth, $previewHeight, $maxWidth, $maxHeight, $square
			);
		}

		return $previewData;
	}

	/**
	 * Asks core for a preview based on our criteria
	 *
	 * @todo Need to read scaling setting from settings
	 *
	 * @param bool $keepAspect
	 *
	 * @return \OC_Image
	 */
	private function getPreviewFromCore($keepAspect) {
		list($maxX, $maxY) = $this->dims;

		$this->preview->setMaxX($maxX);
		$this->preview->setMaxY($maxY);
		$this->preview->setScalingUp(false);
		$this->preview->setKeepAspect($keepAspect);

		//$this->logger->debug("[PreviewService] preview {preview}", ['preview' => $this->preview]);

		$previewData = $this->preview->getPreview();

		return $previewData;
	}

	/**
	 * Makes a preview fit in the asked dimension and, if required, fills the empty space
	 *
	 * @param \OCP\IImage $previewData
	 * @param int $previewWidth
	 * @param int $previewHeight
	 * @param int $maxWidth
	 * @param int $maxHeight
	 * @param bool $square
	 *
	 * @return \OC_Image
	 */
	private function fixPreview(
		$previewData, $previewWidth, $previewHeight, $maxWidth, $maxHeight, $square
	) {

		if ($square || $previewWidth > $maxWidth || $previewHeight > $maxHeight) {
			$fixedPreview = $this->resize(
				$previewData, $previewWidth, $previewHeight, $maxWidth, $maxHeight, $square
			);
			$previewData = $this->fixPreviewCache($fixedPreview);
		}

		return $previewData;
	}

	/**
	 * Makes a preview fit in the asked dimension and, if required, fills the empty space
	 *
	 * @param \OCP\IImage $previewData
	 * @param int $previewWidth
	 * @param int $previewHeight
	 * @param int $maxWidth
	 * @param int $maxHeight
	 * @param bool $fill
	 *
	 * @return resource
	 */
	private function resize(
		$previewData, $previewWidth, $previewHeight, $maxWidth, $maxHeight, $fill
	) {
		list($newX, $newY, $newWidth, $newHeight) =
			$this->calculateNewDimensions($previewWidth, $previewHeight, $maxWidth, $maxHeight);

		if (!$fill) {
			$newX = $newY = 0;
			$maxWidth = $newWidth;
			$maxHeight = $newHeight;
		}

		$resizedPreview = $this->processPreview(
			$previewData, $previewWidth, $previewHeight, $newWidth, $newHeight, $maxWidth,
			$maxHeight, $newX, $newY
		);

		return $resizedPreview;
	}

	/**
	 * Mixes a transparent background with a resized foreground preview
	 *
	 * @param \OCP\IImage $previewData
	 * @param int $previewWidth
	 * @param int $previewHeight
	 * @param int $newWidth
	 * @param int $newHeight
	 * @param int $maxWidth
	 * @param int $maxHeight
	 * @param int $newX
	 * @param int $newY
	 *
	 * @return resource
	 */
	private function processPreview(
		$previewData, $previewWidth, $previewHeight, $newWidth, $newHeight, $maxWidth, $maxHeight,
		$newX, $newY
	) {
		$fixedPreview = imagecreatetruecolor($maxWidth, $maxHeight); // Creates the canvas

		// We make the background transparent
		imagealphablending($fixedPreview, false);
		$transparency = imagecolorallocatealpha($fixedPreview, 0, 0, 0, 127);
		imagefill($fixedPreview, 0, 0, $transparency);
		imagesavealpha($fixedPreview, true);


		imagecopyresampled(
			$fixedPreview, $previewData->resource(),
			$newX, $newY, 0, 0, $newWidth, $newHeight, $previewWidth, $previewHeight
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
	 * @param int $maxWidth
	 * @param int $maxHeight
	 *
	 * @return array<int,double>
	 */
	private function calculateNewDimensions($previewWidth, $previewHeight, $maxWidth, $maxHeight) {
		if (($previewWidth / $previewHeight) >= ($maxWidth / $maxHeight)) {
			$newWidth = $maxWidth;
			$newHeight = $previewHeight * ($maxWidth / $previewWidth);
			$newX = 0;
			$newY = round(abs($maxHeight - $newHeight) / 2);
		} else {
			$newWidth = $previewWidth * ($maxHeight / $previewHeight);
			$newHeight = $maxHeight;
			$newX = round(abs($maxWidth - $newWidth) / 2);
			$newY = 0;
		}

		return [$newX, $newY, $newWidth, $newHeight];
	}

	/**
	 * Fixes the preview cache by replacing the broken thumbnail with ours
	 *
	 * WARNING: Will break if the thumbnail folder ever moves or if encryption is turned on for
	 * thumbnails
	 *
	 * @param resource $fixedPreview
	 *
	 * @return \OCP\IImage
	 */
	private function fixPreviewCache($fixedPreview) {
		$owner = $this->userId;
		$file = $this->file;
		$preview = $this->preview;
		$fixedPreviewObject = new Image($fixedPreview);
		// Get the location where the broken thumbnail is stored
		$thumbnailFolder = $this->dataDir . '/' . $owner . '/';
		$thumbnail = $thumbnailFolder . $preview->isCached($file->getId());

		// Caching it for next time
		if ($fixedPreviewObject->save($thumbnail)) {
			$previewData = $fixedPreviewObject;
		} else {
			$previewData = $preview->getPreview();
		}

		return $previewData;
	}

}
