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
use OCP\Files\NotFoundException;
use OCP\ILogger;
use OCP\IPreview;

use OCP\AppFramework\Http;

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
	 * Constructor
	 *
	 * @param string $appName
	 * @param Folder|null $userFolder
	 * @param EnvironmentService $environmentService
	 * @param ILogger $logger
	 * @param IPreview $previewManager
	 */
	public function __construct(
		$appName,
		$userFolder,
		EnvironmentService $environmentService,
		ILogger $logger,
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
		$data = false;
		try {
			$node = $this->userFolder->get($albumpath);
			$nodeId = $node->getId();
			$nodePermission = $node->getPermissions();
			$data = array();
			$data['fileid'] = $nodeId;
			$data['permissions'] = $nodePermission;


		} catch (NotFoundException $exception) {
			$message = $exception->getMessage();
			$code = Http::STATUS_NOT_FOUND;
			$this->kaBoom($message, $code);
		}

		return $data;
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
		// TODO: This hard-coded array could be replaced by admin settings
		$wantedMimes = array(
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

		if ($slideshow) {
			/**
			 * These types are useful for files preview in the files app, but
			 * not for the gallery side
			 */
			$wantedMimes = array_merge(
				$wantedMimes, array(
								'application/font-sfnt',
								'application/x-font',
							)
			);
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
				'app'   => $this->appName,
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
	 * @return array[string[]] all the images we could find
	 */
	public function getImages() {
		$images = array();
		$result = array();

		$env = $this->environmentService->getEnv();
		$pathRelativeToFolder = $env['relativePath'];
		/** @type Folder $folder */
		$folder = $env['folder'];
		$folderPath = $folder->getPath();
		/** @type Folder $imagesFolder */
		$imagesFolder = $this->getResource($folder, $pathRelativeToFolder);
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

		/** @type File $image */
		foreach ($images as $image) {
			$imagePath = $image->getPath();
			$mimeType = $image->getMimetype();
			/*$this->logger->debug(
				"folderPath: {folderPath} pathRelativeToFolder: {pathRelativeToFolder} imagePath: {imagePath} mime: {mime}",
				array(
					'app'                  => $this->appName,
					'folderPath'           => $folderPath,
					'pathRelativeToFolder' => $pathRelativeToFolder,
					'imagePath'            => $imagePath,
					'mime'                 => $mimeType
				)
			);*/

			// We remove the part which goes from the user's root to the current
			// folder and we also remove the current folder for public galleries
			$fixedPath = str_replace(
				$folderPath . $pathRelativeToFolder, '', $imagePath
			);

			// On OC7, searchByMime returns images from the rubbish bin...
			// https://github.com/owncloud/core/issues/4903
			if (substr($fixedPath, 0, 9) === "_trashbin") {
				//unset($images[$key]);
				continue;
			}

			$imageData = array(
				'path'     => $fixedPath,
				'mimetype' => $mimeType
			);
			$result[] = $imageData;

		}

		/*$this->logger->debug(
			"Images array: {images}",
			array(
				'app'    => $this->appName,
				'images' => $result
			)
		);*/

		return $result;
	}

}