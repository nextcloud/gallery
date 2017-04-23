<?php
/**
 * Nextcloud - Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 * @author Robin Appelman <robin@icewind.nl>
 *
 * @copyright Olivier Paroz 2017
 * @copyright Robin Appelman 2017
 */

namespace OCA\Gallery\Controller;

use OCP\Files\File;
use OCP\Files\Folder;
use OCP\ILogger;

use OCP\AppFramework\Http;

use OCA\Gallery\Service\SearchFolderService;
use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\SearchMediaService;
use OCA\Gallery\Service\DownloadService;

/**
 * Trait Files
 *
 * @package OCA\Gallery\Controller
 */
trait Files {

	use PathManipulation;

	/** @var SearchFolderService */
	private $searchFolderService;
	/** @var ConfigService */
	private $configService;
	/** @var SearchMediaService */
	private $searchMediaService;
	/** @var DownloadService */
	private $downloadService;
	/** @var ILogger */
	private $logger;

	/**
	 * @NoAdminRequired
	 *
	 * Returns a list of all media files and albums available to the authenticated user
	 *
	 *    * Authentication can be via a login/password or a token/(password)
	 *    * For private galleries, it returns all media files, with the full path from the root
	 *     folder For public galleries, the path starts from the folder the link gives access to
	 *     (virtual root)
	 *    * An exception is only caught in case something really wrong happens. As we don't test
	 *     files before including them in the list, we may return some bad apples
	 *
	 * @param string $location a path representing the current album in the app
	 * @param array $features the list of supported features
	 * @param string $etag the last known etag in the client
	 * @param array $mediatypes the list of supported media types
	 *
	 * @return array <string,array<string,string|int>>|Http\JSONResponse
	 */
	private function getFilesAndAlbums($location, $features, $etag, $mediatypes) {
		$files = [];
		$albums = [];
		$updated = true;
		/** @var Folder $folderNode */
		list($folderPathFromRoot, $folderNode) =
			$this->searchFolderService->getCurrentFolder(rawurldecode($location), $features);
		$albumConfig = $this->configService->getConfig($folderNode, $features);
		if ($folderNode->getEtag() !== $etag) {
			list($files, $albums) = $this->searchMediaService->getMediaFiles(
				$folderNode, $mediatypes, $features
			);
		} else {
			$updated = false;
		}
		$files = $this->fixPaths($files, $folderPathFromRoot);

		return $this->formatResults($files, $albums, $albumConfig, $folderPathFromRoot, $updated);
	}

	/**
	 * Generates shortened paths to the media files
	 *
	 * We only want to keep one folder between the current folder and the found media file
	 * /root/folder/sub1/sub2/file.ext
	 * becomes
	 * /root/folder/file.ext
	 *
	 * @param array $files
	 * @param string $folderPathFromRoot
	 *
	 * @return array
	 */
	private function fixPaths($files, $folderPathFromRoot) {
		if (!empty($files)) {
			foreach ($files as &$file) {
				$file['path'] = $this->getReducedPath($file['path'], $folderPathFromRoot);
			}
		}

		return $files;
	}

	/**
	 * Simply builds and returns an array containing the list of files, the album information and
	 * whether the location has changed or not
	 *
	 * @param array $files
	 * @param array $albums
	 * @param array $albumConfig
	 * @param string $folderPathFromRoot
	 * @param bool $updated
	 *
	 * @return array
	 * @internal param $array <string,string|int> $files
	 */
	private function formatResults($files, $albums, $albumConfig, $folderPathFromRoot, $updated) {
		return [
			'files'       => $files,
			'albums'      => $albums,
			'albumconfig' => $albumConfig,
			'albumpath'   => $folderPathFromRoot,
			'updated'     => $updated
		];
	}

	/**
	 * Generates the download data
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param string|null $filename
	 *
	 * @return array|false
	 */
	private function getDownload($fileId, $filename) {
		/** @type File $file */
		$file = $this->downloadService->getFile($fileId);
		$this->configService->validateMimeType($file->getMimeType());
		$download = $this->downloadService->downloadFile($file);
		if (is_null($filename)) {
			$filename = $file->getName();
		}
		$download['name'] = $filename;

		return $download;
	}

}
