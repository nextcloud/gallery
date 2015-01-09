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
use OCP\IPreview;

use OCP\AppFramework\Http;

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
	 * @type Folder|null
	 */
	private $userFolder;
	/**
	 * @type EnvironmentService
	 */
	private $environmentService;
	/**
	 * @type mixed
	 */
	private $previewManager;
	/**
	 * @todo This hard-coded array could be replaced by admin settings
	 *
	 * @type string[]
	 */
	private static $baseMimeTypes = array(
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
	);
	/**
	 * These types are useful for files preview in the files app, but
	 * not for the gallery side
	 *
	 * @type string[]
	 */
	private static $slideshowMimeTypes = array(
		'application/font-sfnt',
		'application/x-font',
	);

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param Folder|null $userFolder
	 * @param EnvironmentService $environmentService
	 * @param SmarterLogger $logger
	 * @param IPreview $previewManager
	 */
	public function __construct(
		$appName,
		$userFolder,
		EnvironmentService $environmentService,
		SmarterLogger $logger,
		IPreview $previewManager
	) {
		parent::__construct($appName, $logger);

		$this->userFolder = $userFolder;
		$this->environmentService = $environmentService;
		$this->previewManager = $previewManager;
	}

	/**
	 * Returns information about an album, based on its path
	 *
	 * Used to see if we have access to the folder or not
	 *
	 * @param string $albumpath
	 *
	 * @return array information about the given path
	 */
	public function getAlbumInfo($albumpath) {
		$userFolder = $this->userFolder;
		$albumInfo = array();

		if ($userFolder !== null) {
			$node = $this->getNode($userFolder, $albumpath);
			$albumInfo = array(
				'fileid'      => $node->getId(),
				'permissions' => $node->getPermissions()
			);
		} else {
			$message = "Could not access the user's folder";
			$code = Http::STATUS_NOT_FOUND;
			$this->kaBoom($message, $code);
		}

		return $albumInfo;

	}

	/**
	 * This builds and returns a list of all supported media types
	 *
	 * @param bool $slideshow
	 *
	 * @return string[] all supported media types
	 */
	public function getSupportedMimes($slideshow = true) {
		$supportedMimes = array();
		$wantedMimes = self::$baseMimeTypes;

		if ($slideshow) {
			$wantedMimes =
				array_merge($wantedMimes, self::$slideshowMimeTypes);
		}

		foreach ($wantedMimes as $wantedMime) {
			// Let's see if a preview of files of that media type can be generated
			$preview = $this->previewManager;
			if ($preview->isMimeSupported($wantedMime)) {
				// We add it to the list of supported media types
				$supportedMimes[] = $wantedMime;
			}
		}

		// SVG is always supported
		// TODO: Native SVG could be disabled via admin settings
		$supportedMimes[] = 'image/svg+xml';

		$this->logger->debug(
			"Supported Mimes: {mimes}",
			array(
				'mimes' => $supportedMimes
			)
		);

		return $supportedMimes;
	}

	/**
	 * This returns the list of all images which can be shown
	 *
	 * For private galleries, it returns all images
	 * For public galleries, it starts from the folder the link gives access to
	 *
	 * @return array all the images we could find
	 */
	public function getImages() {
		$folderData = $this->getImagesFolder();

		$imagesFolder = $folderData['imagesFolder'];
		$images = $this->searchByMime($imagesFolder);

		$fromRootToFolder = $folderData['fromRootToFolder'];
		$result = $this->fixImagePath($images, $fromRootToFolder);

		/*$this->logger->debug(
			"Images array: {images}",
			array(
				'images' => $result
			)
		);*/

		return $result;
	}

	/**
	 * Returns the folder where we need to look for files, as well as the path
	 * starting from it and going up to the user's root folder
	 *
	 * @return array<string,Folder|string>
	 */
	private function getImagesFolder() {
		$env = $this->environmentService->getEnv();
		$pathRelativeToFolder = $env['relativePath'];
		/** @type Folder $folder */
		$folder = $env['folder'];
		$folderPath = $folder->getPath();
		/** @type Folder $imagesFolder */
		$imagesFolder = $this->getResource($folder, $pathRelativeToFolder);
		$fromRootToFolder = $folderPath . $pathRelativeToFolder;

		$folderData = array(
			'imagesFolder'     => $imagesFolder,
			'fromRootToFolder' => $fromRootToFolder,
		);

		return $folderData;
	}

	/**
	 * Returns all the images of which we can generate a preview
	 *
	 * @param Folder $imagesFolder
	 *
	 * @return array
	 */
	private function searchByMime($imagesFolder) {
		$images = array();
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
	 * On OC7, we fix searchByMime which returns images from the rubbish bin...
	 * https://github.com/owncloud/core/issues/4903
	 *
	 * Example logger
	 * $this->logger->debug(
	 * "folderPath: {folderPath} pathRelativeToFolder: {pathRelativeToFolder}
	 * imagePath: {imagePath} mime: {mime}", array(
	 * 'folderPath'           => $folderPath,
	 * 'pathRelativeToFolder' => $pathRelativeToFolder,
	 * 'imagePath'            => $imagePath,
	 * 'mime'                 => $mimeType
	 * )
	 * );
	 *
	 * @param array $images
	 * @param string $fromRootToFolder
	 *
	 * @return array
	 */
	private function fixImagePath($images, $fromRootToFolder) {
		$result = array();
		/** @type File $image */
		foreach ($images as $image) {
			$imagePath = $image->getPath();
			$mimeType = $image->getMimetype();
			$fixedPath = str_replace(
				$fromRootToFolder, '', $imagePath
			);
			if (substr($fixedPath, 0, 9) === "_trashbin") {
				continue;
			}
			$imageData = array(
				'path'     => $fixedPath,
				'mimetype' => $mimeType
			);
			$result[] = $imageData;
		}

		return $result;
	}

}