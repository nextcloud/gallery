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
		$this->dims = ['x' => $maxX, 'y' => $maxY];
		try {
			// Can generate encryption Exceptions...
			$previewData = $this->getPreviewFromCore($keepAspect);
		} catch (\Exception $exception) {
			$this->logger->debug("[PreviewService] ERROR! Did not get a preview");
			$perfectPreview = ['preview' => $this->getMimeIcon()];
			$this->success = false;
			$perfectPreview['mimetype'] = 'image/png'; // Previews are always sent as PNG

			return $perfectPreview;
		}

		if ($previewData->valid()) {
			if ($maxX === 200) { // Only fixing the square thumbnails
				$previewData = $this->previewValidator();
			}
			$perfectPreview = ['preview' => $previewData];
		} else {
			$this->logger->debug("[PreviewService] ERROR! Did not get a preview");
			$perfectPreview = ['preview' => $this->getMimeIcon()];
			$this->success = false;
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

		$this->preview->setMaxX($this->dims['x']);
		$this->preview->setMaxY($this->dims['y']);
		$this->preview->setScalingUp(false);
		$this->preview->setKeepAspect($keepAspect);

		//$this->logger->debug("[PreviewService] preview {preview}", ['preview' => $this->preview]);

		return $this->preview->getPreview();
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
	private function previewValidator() {
		$dims = $this->dims;
		$previewData = $this->preview->getPreview();
		$previewX = $previewData->width();
		$previewY = $previewData->height();

		if (($previewX > $dims['x']
			 || ($previewX < $dims['x'] || $previewY < $dims['y']))
		) {
			$fixedPreview = $this->fixPreview($previewData, $dims['x'], $dims['y']);
			$previewData = $this->fixPreviewCache($fixedPreview);
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
		$dims = $this->dims;
		$previewWidth = $previewData->width();
		$previewHeight = $previewData->height();
		$fixedPreview = imagecreatetruecolor($dims['x'], $dims['y']); // Creates the canvas

		// We make the background transparent
		imagealphablending($fixedPreview, false);
		$transparency = imagecolorallocatealpha($fixedPreview, 0, 0, 0, 127);
		imagefill($fixedPreview, 0, 0, $transparency);
		imagesavealpha($fixedPreview, true);
		$newDimensions =
			$this->calculateNewDimensions($previewWidth, $previewHeight, $dims['x'], $dims['y']);

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
		$dims = $this->dims;
		if (($previewWidth / $previewHeight) >= ($dims['x'] / $dims['y'])) {
			$newWidth = $dims['x'];
			$newHeight = $previewHeight * ($dims['x'] / $previewWidth);
			$newX = 0;
			$newY = round(abs($dims['y'] - $newHeight) / 2);
		} else {
			$newWidth = $previewWidth * ($dims['y'] / $previewHeight);
			$newHeight = $dims['y'];
			$newX = round(abs($dims['x'] - $newWidth) / 2);
			$newY = 0;
		}

		return [
			'newX'      => $newX,
			'newY'      => $newY,
			'newWidth'  => $newWidth,
			'newHeight' => $newHeight,
		];
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
		$mime = $this->file->getMimeType();
		$iconData = new Image();

		$image = $this->dataDir . '/../' . Template::mimetype_icon($mime);
		// Alternative which does not exist yet
		//$image = $this->serverRoot() . Template::mimetype_icon($mime);

		$iconData->loadFromFile($image);

		return $iconData;
	}

}