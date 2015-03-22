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
	 * @type array<string, string|int>
	 */
	private $images = [];
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
	 * @return array<string,string|int> all the images we could find
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
	 *
	 * @return int
	 */
	private function searchFolder($folder, $subDepth = 0) {
		$albumImageCounter = 0;
		$subFolders = [];

		$nodes = $this->getNodes($folder, $subDepth);
		foreach ($nodes as $node) {
			//$this->logger->debug("Sub-Node path : {path}", ['path' => $node->getPath()]);
			$nodeType = $this->getNodeType($node);
			$subFolders = array_merge($subFolders, $this->allowedSubFolder($node, $nodeType));

			if ($nodeType === 'file') {
				$albumImageCounter = $albumImageCounter + (int)$this->isPreviewAvailable($node);
				if ($this->haveEnoughPictures($albumImageCounter, $subDepth)) {
					break;
				}
			}
		}
		$this->searchSubFolders($subFolders, $subDepth, $albumImageCounter);

		return $albumImageCounter;
	}

	/**
	 * Retrieves all files and sub-folders contained in a folder
	 *
	 * If we can't find anything in the current folder, we throw an exception as there is no point
	 * in doing any more work, but if we're looking at a sub-folder, we return an empty array so
	 * that it can be simply ignored
	 *
	 * @param Folder $folder
	 * @param int $subDepth
	 *
	 * @return array
	 *
	 * @throws NotFoundServiceException
	 */
	private function getNodes($folder, $subDepth) {
		$nodes = [];
		try {
			if ($folder->isReadable()
				&& $folder->getStorage()
						  ->isLocal()
			) {
				$nodes = $folder->getDirectoryListing();
			}
		} catch (\Exception $exception) {
			$nodes = $this->recoverFromGetNodesError($subDepth, $exception);
		}

		return $nodes;
	}

	/**
	 * Throws an exception if this problem occurs in the current folder, otherwise just ignores the
	 * sub-folder
	 *
	 * @param int $subDepth
	 * @param \Exception $exception
	 *
	 * @return array
	 * @throws NotFoundServiceException
	 */
	private function recoverFromGetNodesError($subDepth, $exception) {
		if ($subDepth === 0) {
			$this->logAndThrowNotFound($exception->getMessage());
		}

		return [];
	}

	/**
	 * Returns the node type, either 'dir' or 'file'
	 *
	 * If there is a problem, we return an empty string so that the node can be ignored
	 *
	 * @param Node $node
	 *
	 * @return string
	 */
	private function getNodeType($node) {
		try {
			$nodeType = $node->getType();
		} catch (\Exception $exception) {
			return '';
		}

		return $nodeType;
	}

	/**
	 * Returns the node if it's a folder we have access to
	 *
	 * @param Folder $node
	 * @param string $nodeType
	 *
	 * @return array|Folder
	 */
	private function allowedSubFolder($node, $nodeType) {
		if ($nodeType === 'dir') {
			/** @type Folder $node */
			if (!$node->nodeExists('.nomedia')) {
				return [$node];
			}
		}

		return [];
	}

	/**
	 * Checks if we've collected enough pictures to be able to build the view
	 *
	 * An album is full when we find max 4 pictures at the same level
	 *
	 * @param int $albumImageCounter
	 * @param int $subDepth
	 *
	 * @return bool
	 */
	private function haveEnoughPictures($albumImageCounter, $subDepth) {
		if ($subDepth === 0) {
			return false;
		}
		if ($albumImageCounter === 4) {
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
	 * @param int $subDepth
	 * @param int $albumImageCounter
	 */
	private function searchSubFolders($subFolders, $subDepth, $albumImageCounter) {
		if ($this->folderNeedsToBeSearched($subFolders, $subDepth, $albumImageCounter)) {
			$subDepth++;
			foreach ($subFolders as $subFolder) {
				$count = $this->searchFolder($subFolder, $subDepth);
				if ($this->abortSearch($subDepth, $count)) {
					break;
				}
			}
		}
	}

	/**
	 * Checks if we need to look for media files in the specified folder
	 *
	 * @param array <Folder> $subFolders
	 * @param int $subDepth
	 * @param int $albumImageCounter
	 *
	 * @return bool
	 */
	private function folderNeedsToBeSearched($subFolders, $subDepth, $albumImageCounter) {
		if (!empty($subFolders) && ($subDepth === 0 || $albumImageCounter === 0)) {
			return true;
		}

		return false;
	}

	/**
	 * Returns true if there is no need to check any other sub-folder at the same depth level
	 *
	 * @param int $subDepth
	 * @param int $count
	 *
	 * @return bool
	 */
	private function abortSearch($subDepth, $count) {
		if ($subDepth > 1 && $count > 0) {
			return true;
		}

		return false;
	}

	/**
	 * Returns true if the file is of a supported media type and adds it to the array of items to
	 * return
	 *
	 * We remove the part which goes from the user's root to the current
	 * folder and we also remove the current folder for public galleries
	 *
	 * @todo We could potentially check if the file is readable ($file->stat() maybe) in order to
	 *     only return valid files, but this may slow down operations
	 *
	 * @param File $file the file to test
	 *
	 * @return bool
	 */
	private function isPreviewAvailable($file) {
		try {
			$mimeType = $file->getMimetype();
			$isLocal = $file->getStorage()
							->isLocal();
			if ($isLocal && in_array($mimeType, $this->supportedMimes)) {
				$imagePath = $file->getPath();
				$fixedPath = str_replace($this->fromRootToFolder, '', $imagePath);
				$imageId = $file->getId();
				$mTime = $file->getMTime();
				$imageData = [
					'path'     => $fixedPath,
					'fileid'   => $imageId,
					'mimetype' => $mimeType,
					'mtime'    => $mTime
				];
				$this->images[] = $imageData;

				/*$this->logger->debug(
					"Image path : {path}", ['path' => $imagePath]
				);*/

				return true;
			}
		} catch (\Exception $exception) {
			return false;
		}

		return false;
	}
}
