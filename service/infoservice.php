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

namespace OCA\GalleryPlus\Service;

use OCP\Files\Folder;
use OCP\Files\File;

use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Contains various methods which provide initial information about the
 * supported media types, the folder permissions and the images contained in
 * the system
 *
 * @package OCA\GalleryPlus\Service
 */
class InfoService extends Service {

	/**
	 * @type mixed
	 */
	private $previewService;
	/**
	 * @todo This hard-coded array could be replaced by admin settings
	 *
	 * @type string[]
	 */
	private $baseMimeTypes = [
		'image/png',
		'image/jpeg',
		'image/gif',
		'image/x-xbitmap',
		'image/bmp',
		'image/tiff',
		'image/x-dcraw',
		'application/x-photoshop',
		'application/illustrator',
		'application/postscript',
	];
	/**
	 * These types are useful for files preview in the files app, but
	 * not for the gallery side
	 *
	 * @type string[]
	 */
	private $slideshowMimeTypes = [
		'application/font-sfnt',
		'application/x-font',
	];

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param SmarterLogger $logger
	 * @param PreviewService $previewManager
	 */
	public function __construct(
		$appName,
		PreviewService $previewManager,
		SmarterLogger $logger

	) {
		parent::__construct($appName, $logger);

		$this->previewService = $previewManager;
	}

	/**
	 * This builds and returns a list of all supported media types
	 *
	 * @todo Native SVG could be disabled via admin settings
	 *
	 * @param bool $slideshow
	 *
	 * @return string[] all supported media types
	 */
	public function getSupportedMimes($slideshow = true) {
		$supportedMimes = [];
		$wantedMimes = $this->baseMimeTypes;

		if ($slideshow) {
			$wantedMimes = array_merge($wantedMimes, $this->slideshowMimeTypes);
		}

		foreach ($wantedMimes as $wantedMime) {
			// Let's see if a preview of files of that media type can be generated
			if ($this->previewService->isMimeSupported($wantedMime)) {
				$supportedMimes[] = $wantedMime; // We add it to the list of supported media types
			}
		}
		$supportedMimes[] = 'image/svg+xml'; // SVG is always supported

		$this->logger->debug("Supported Mimes: {mimes}", ['mimes' => $supportedMimes]);

		return $supportedMimes;
	}

	/**
	 * This returns the list of all images which can be shown starting from the given folder
	 *
	 * @param array $folderData
	 *
	 * @return array all the images we could find
	 */
	public function getImages($folderData) {
		$images = $this->searchByMime($folderData['imagesFolder']);
		$fromRootToFolder = $folderData['fromRootToFolder'];

		$result = $this->prepareImagesArray($images, $fromRootToFolder);

		//$this->logger->debug("Images array: {images}", ['images' => $result]);

		return $result;
	}

	/**
	 * Returns all the images of which we can generate a preview
	 *
	 * @param Folder $imagesFolder
	 *
	 * @return array
	 */
	private function searchByMime($imagesFolder) {
		$images = [];
		$mimes = $this->getSupportedMimes(false);

		foreach ($mimes as $mime) {
			/**
			 * We look for images of this media type in the whole system.
			 * This can lead to performance issues
			 *
			 * @todo Use an internal Class to solve the performance issue
			 */
			$mimeImages = $imagesFolder->searchByMime($mime);

			$images = array_merge($images, $mimeImages);
		}

		return $images;
	}

	/**
	 * Fixes the path of each image we've found
	 *
	 * We remove the part which goes from the user's root to the current
	 * folder and we also remove the current folder for public galleries
	 *
	 * @todo Test this on OC8
	 * On OC7, we fix searchByMime which returns images from the rubbish bin...
	 * https://github.com/owncloud/core/issues/4903
	 *
	 * Example logger
	 * $this->logger->debug(
	 * "folderPath: {folderPath} pathRelativeToFolder: {pathRelativeToFolder}
	 * imagePath: {imagePath} mime: {mime}", [
	 * 'folderPath'           => $folderPath,
	 * 'pathRelativeToFolder' => $pathRelativeToFolder,
	 * 'imagePath'            => $imagePath,
	 * 'mime'                 => $mimeType
	 * ]
	 * );
	 *
	 * @param array $images
	 * @param string $fromRootToFolder
	 *
	 * @return array
	 */
	private function prepareImagesArray($images, $fromRootToFolder) {
		$result = [];
		/** @type File $image */
		foreach ($images as $image) {
			$imagePath = $image->getPath();
			$mimeType = $image->getMimetype();
			$fixedPath = str_replace($fromRootToFolder, '', $imagePath);
			if (substr($fixedPath, 0, 9) === "_trashbin") {
				continue;
			}
			$imageData = [
				'path'     => $fixedPath,
				'mimetype' => $mimeType
			];
			$result[] = $imageData;
		}

		return $result;
	}

}
