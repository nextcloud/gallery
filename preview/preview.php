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

use OCP\IConfig;
use OCP\Image;
use OCP\Files\File;
use OCP\IPreview;
use OCP\Template;

use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Generates previews
 *
 * @todo On OC8.1, replace \OC\Preview with IPreview
 *
 * @package OCA\GalleryPlus\Preview
 */
class Preview {

	/**
	 * @type string
	 */
	private $dataDir;
	/**
	 * @type mixed
	 */
	private $previewManager;
	/**
	 * @type SmarterLogger
	 */
	private $logger;
	/**
	 * @type string
	 */
	private $userId;
	/**
	 * @type \OC\Preview
	 */
	private $preview;
	/**
	 * @type File
	 */
	private $file;
	/**
	 * @type int[]
	 */
	private $dims;
	/**
	 * @type bool
	 */
	private $success = true;

	/**
	 * Constructor
	 *
	 * @param IConfig $config
	 * @param IPreview $previewManager
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		IConfig $config,
		IPreview $previewManager,
		SmarterLogger $logger
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
		$this->preview = new \OC\Preview($userId, 'files', $imagePathFromFolder);
	}

	/**
	 * Returns a preview based on OC's preview class and our custom methods
	 *
	 * We check that the preview returned by the Preview class can be used by
	 * the browser. If not, we send the mime icon and change the status code so
	 * that the client knows that the process has failed.
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
		$this->dims = [$maxX, $maxY];
		$perfectPreview = [];

		$previewData = $this->getPreviewFromCore($keepAspect);

		if ($previewData && $previewData->valid()) {
			$perfectPreview['preview'] = $previewData;
		} else {
			$perfectPreview['preview'] = $this->getMimeIcon();
		}
		$perfectPreview['mimetype'] = 'image/png'; // Previews are always sent as PNG

		return $perfectPreview;
	}

	/**
	 * Returns true if the preview was successfully generated
	 *
	 * @return bool
	 */
	public function isPreviewValid() {
		return $this->success;
	}

	/**
	 * Makes sure we return previews of the asked dimensions and fix the cache
	 * if necessary
	 *
	 * The Preview class sometimes return previews which are either wider or
	 * smaller than the asked dimensions. This happens when one of the original
	 * dimension is smaller than what is asked for
	 *
	 * @return resource
	 */
	public function previewValidator() {
		list($maxX, $maxY) = $this->dims;
		$previewData = $this->preview->getPreview();
		$previewX = $previewData->width();
		$previewY = $previewData->height();

		if (($previewX > $maxX
			 || ($previewX < $maxX || $previewY < $maxY))
		) {
			$fixedPreview = $this->fixPreview($previewData, $maxX, $maxY);
			$previewData = $this->fixPreviewCache($fixedPreview);
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
		$this->logger->debug("[PreviewService] Fetching the preview");
		list($maxX, $maxY) = $this->dims;

		$this->preview->setMaxX($maxX);
		$this->preview->setMaxY($maxY);
		$this->preview->setScalingUp(false);
		$this->preview->setKeepAspect($keepAspect);

		//$this->logger->debug("[PreviewService] preview {preview}", ['preview' => $this->preview]);
		try {
			// Can generate encryption Exceptions...
			$previewData = $this->preview->getPreview();
		} catch (\Exception $exception) {
			return null;
		}

		return $previewData;
	}

	/**
	 * Makes a preview fit in the asked dimension and fills the empty space
	 *
	 * @param \OC_Image $previewData
	 *
	 * @return resource
	 */
	private function fixPreview($previewData) {
		list($maxX, $maxY) = $this->dims;
		$previewWidth = $previewData->width();
		$previewHeight = $previewData->height();
		$fixedPreview = imagecreatetruecolor($maxX, $maxY); // Creates the canvas

		// We make the background transparent
		imagealphablending($fixedPreview, false);
		$transparency = imagecolorallocatealpha($fixedPreview, 0, 0, 0, 127);
		imagefill($fixedPreview, 0, 0, $transparency);
		imagesavealpha($fixedPreview, true);
		list($newX, $newY, $newWidth, $newHeight) =
			$this->calculateNewDimensions($previewWidth, $previewHeight);

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
	 *
	 * @return array
	 */
	private function calculateNewDimensions($previewWidth, $previewHeight) {
		list($maxX, $maxY) = $this->dims;
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

		return [$newX, $newY, $newWidth, $newHeight];
	}

	/**
	 * Fixes the preview cache by replacing the broken thumbnail with ours
	 *
	 * @param resource $fixedPreview
	 *
	 * @return mixed
	 */
	private function fixPreviewCache($fixedPreview) {
		$owner = $this->userId;
		$file = $this->file;
		$preview = $this->preview;
		$fixedPreviewObject = new Image($fixedPreview);
		$previewData = $preview->getPreview();

		// Get the location where the broken thumbnail is stored
		$thumbnailFolder = $this->dataDir . '/' . $owner . '/';
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
	 * @return Image
	 */
	private function getMimeIcon() {
		$this->logger->debug("[PreviewService] ERROR! Did not get a preview, sending mime icon");
		$this->success = false;

		$mime = $this->file->getMimeType();
		$iconData = new Image();

		$image = $this->dataDir . '/../' . Template::mimetype_icon($mime);
		// Alternative which does not exist yet
		//$image = $this->serverRoot() . Template::mimetype_icon($mime);

		$iconData->loadFromFile($image);

		return $iconData;
	}

}
