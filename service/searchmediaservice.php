<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2016
 */

namespace OCA\Gallery\Service;

use OCP\Files\Folder;
use OCP\Files\File;

/**
 * Searches the instance for media files which can be shown
 *
 * @package OCA\Gallery\Service
 */
class SearchMediaService extends FilesService {

	/** @var null|array<string,string|int> */
	private $images = [];
	/** @var null|array<string,string|int> */
	private $albums = [];
	/** @var string[] */
	private $supportedMediaTypes;

	/**
	 * This returns the list of all media files which can be shown starting from the given folder
	 *
	 * @param Folder $folderNode the current album
	 * @param string[] $supportedMediaTypes the list of supported media types
	 * @param array $features the list of supported features
	 *
	 * @return array<null|array<string,string|int>> all the images we could find
	 */
	public function getMediaFiles($folderNode, $supportedMediaTypes, $features) {
		$this->supportedMediaTypes = $supportedMediaTypes;
		$this->features = $features;
		$this->searchFolder($folderNode);

		return [$this->images, $this->albums];
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
		$this->addFolderToAlbumsArray($folder);
		$nodes = $this->getNodes($folder, $subDepth);
		foreach ($nodes as $node) {
			if (!$this->isAllowedAndAvailable($node)) {
				continue;
			}
			$nodeType = $this->getNodeType($node);
			$subFolders = array_merge($subFolders, $this->getAllowedSubFolder($node, $nodeType));
			$albumImageCounter = $this->addMediaFile($node, $nodeType, $albumImageCounter);
			if ($this->haveEnoughPictures($albumImageCounter, $subDepth)) {
				break;
			}
		}
		$albumImageCounter = $this->searchSubFolders($subFolders, $subDepth, $albumImageCounter);

		return $albumImageCounter;
	}

	/**
	 * Adds the node to the list of images if it's a file and we can generate a preview of it
	 *
	 * @param File|Folder $node
	 * @param string $nodeType
	 * @param int $albumImageCounter
	 *
	 * @return int
	 */
	private function addMediaFile($node, $nodeType, $albumImageCounter) {
		if ($nodeType === 'file') {
			$albumImageCounter = $albumImageCounter + (int)$this->isPreviewAvailable($node);
		}

		return $albumImageCounter;
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

		return $albumImageCounter === 4;
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
	 *
	 * @return int
	 */
	private function searchSubFolders($subFolders, $subDepth, $albumImageCounter) {
		if ($this->folderNeedsToBeSearched($subFolders, $subDepth, $albumImageCounter)) {
			$subDepth++;
			foreach ($subFolders as $subFolder) {
				//$this->logger->debug("Sub-Node path : {path}", ['path' => $subFolder->getPath()]);
				$albumImageCounter = $this->searchFolder($subFolder, $subDepth);
				if ($this->abortSearch($subDepth, $albumImageCounter)) {
					break;
				}
			}
		}

		return $albumImageCounter;
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
		return !empty($subFolders) && ($subDepth === 0 || $albumImageCounter === 0);
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
		return $subDepth > 1 && $count > 0;
	}

	/**
	 * Returns true if the file is of a supported media type and adds it to the array of items to
	 * return
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
			$mimeType = $file->getMimeType();
			if (in_array($mimeType, $this->supportedMediaTypes)) {
				$this->addFileToImagesArray($mimeType, $file);

				return true;
			}
		} catch (\Exception $exception) {
			return false;
		}

		return false;
	}

	/**
	 * Adds a folder to the albums array
	 *
	 * @param Folder $folder the folder to add to the albums array
	 */
	private function addFolderToAlbumsArray($folder) {
		$albumData = $this->getFolderData($folder);
		$this->albums[$albumData['path']] = $albumData;
	}

	/**
	 * Adds a file to the images array
	 *
	 * @param string $mimeType the media type of the file to add to the images array
	 * @param File $file the file to add to the images array
	 */
	private function addFileToImagesArray($mimeType, $file) {
		$imageData = $this->getNodeData($file);
		$imageData['mimetype'] = $mimeType;
		$this->images[] = $imageData;
	}

}
