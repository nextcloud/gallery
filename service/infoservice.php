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
use OCP\Files\Node;

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
	 * @type Node[]|null
	 */
	private $images;
	/**
	 * @type string[]
	 */
	private $supportedMimes;
	/**
	 * @type string
	 */
	private $fromRootToFolder;

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
	 * If the starting URL is one of a fullscreen preview, we'll return the images of the
	 * containing folder
	 *
	 * @param array <Node, string> $folderData
	 *
	 * @return array all the images we could find
	 */
	public function getImages($folderData) {
		$this->supportedMimes = $this->getSupportedMimes(false);
		$this->fromRootToFolder = $folderData['fromRootToFolder'];

		/** @type Node $node */
		$node = $folderData['imagesFolder'];
		if ($node->getType() === 'dir') {
			$this->searchFolder($node);
		} else {
			$this->searchFolder($node->getParent());
		}

		return $this->images;
	}

	/**
	 * Look for media files and folders in the given folder
	 *
	 * @param Folder $folder
	 * @param int $subDepth
	 */
	private function searchFolder($folder, $subDepth = 0) {
		$albumImageCounter = 0;
		$nodes = [];
		$subFolders = [];
		try {
			$nodes = $folder->getDirectoryListing();
		} catch (\Exception $exception) {
			$this->logAndThrowNotFound($exception->getMessage());
		}

		foreach ($nodes as $node) {
			//$this->logger->debug("Sub-Node path : {path}", ['path' => $node->getPath()]);
			if ($node->getType() === 'dir') {
				/** @type Folder $node */
				if (!$node->nodeExists('.nomedia')) {
					$subFolders[] = $node;
				}
			} else {
				$albumImageCounter = $albumImageCounter + (int)$this->isPreviewAvailable($node);
				if ($this->haveEnoughPictures($albumImageCounter, $subDepth)) {
					break;
				}
			}
		}

		$this->searchSubFolders($subFolders, $albumImageCounter, $subDepth);
	}

	/**
	 * Checks if we've collected enough pictures to give be able to build the view
	 *
	 * At level 1, an album is full when we find 4 pictures and at lower levels, we stop looking
	 * after we've found just one
	 *
	 * @param int $albumImageCounter
	 * @param int $subDepth
	 *
	 * @return bool
	 */
	private function haveEnoughPictures($albumImageCounter, $subDepth) {
		if ($subDepth === 0) {
			return false;
		} elseif ($subDepth === 1) {
			$maxAlbumThumbnail = 4;
		} else {
			$maxAlbumThumbnail = 1;
		}
		if ($albumImageCounter === $maxAlbumThumbnail) {
			return true;
		}

		return false;
	}

	/**
	 * Looks for pictures in sub-folders
	 *
	 * If we're at level 0, we need to look for pictures in sub-folders no matter what
	 * If we're at deeper levels, we only need to go further if we haven't managed to find one
	 * picture in the current folder
	 *
	 * @param array <Folder> $subFolders
	 * @param int $albumImageCounter
	 * @param int $subDepth
	 */
	private function searchSubFolders($subFolders, $albumImageCounter, $subDepth) {
		if ($subDepth === 0 || $albumImageCounter === 0) {
			$subDepth++;
			foreach ($subFolders as $subFolder) {
				$this->searchFolder($subFolder, $subDepth);
			}
		}
	}

	/**
	 * Returns true if the file is of a supported media type and adds it to the array of items to
	 * return
	 *
	 * We remove the part which goes from the user's root to the current
	 * folder and we also remove the current folder for public galleries
	 *
	 * @param File $node
	 *
	 * @return bool
	 */
	private function isPreviewAvailable($node) {
		// This can break on oC 8. See https://github.com/owncloud/core/issues/14390
		try {
			$mimeType = $node->getMimetype();
		} catch (\Exception $exception) {
			return false;
		}

		if (!$node->isMounted() && in_array($mimeType, $this->supportedMimes)) {
			$imagePath = $node->getPath();
			$fixedPath = str_replace($this->fromRootToFolder, '', $imagePath);
			$imageData = [
				'path'     => $fixedPath,
				'mimetype' => $mimeType
			];
			$this->images[] = $imageData;

			/*$this->logger->debug(
				"Image path : {path}", ['path' => $node->getPath()]
			);*/

			return true;
		}

		return false;
	}

}
