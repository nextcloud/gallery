<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\GalleryPlus\Service;

use Symfony\Component\Yaml\Yaml;

use OCP\Files\Folder;
use OCP\Files\File;

/**
 * Finds configurations files, parses them and returns a configuration array
 *
 * Checks the current and parent folders for configuration files
 *
 * @package OCA\GalleryPlus\Service
 */
class ConfigService extends Service {

	/**
	 * Returns information about the currently selected folder
	 *
	 *    * privacy setting
	 *    * special configuration
	 *    * permissions
	 *    * ID
	 *
	 * @param Folder $folderNode
	 * @param string $folderPathFromRoot
	 *
	 * @return array<string,string|int>
	 */
	public function getAlbumInfo($folderNode, $folderPathFromRoot) {
		$configName = 'gallery.cnf';
		$privacyChecker = '.nomedia';
		$albumInfo = [];
		list ($albumConfig, $privateAlbum) =
			$this->getAlbumConfig($folderNode, $privacyChecker, $configName);

		if (!$privateAlbum) {
			$albumInfo = [
				'path'        => $folderPathFromRoot,
				'fileid'      => $folderNode->getID(),
				'permissions' => $folderNode->getPermissions()
			];
			$albumInfo = array_merge($albumInfo, $albumConfig);
		}

		return [$albumInfo, $privateAlbum];
	}

	/**
	 * Returns an album configuration array
	 *
	 * @param Folder $folder
	 * @param string $privacyChecker
	 * @param string $configName
	 * @param int $level
	 * @param array $configArray
	 * @param bool $configComplete
	 *
	 * @return array <null|string,string>
	 */
	private function getAlbumConfig(
		$folder, $privacyChecker, $configName, $level = 0, $configArray = [],
		$configComplete = false
	) {
		if ($folder->nodeExists($privacyChecker)) {
			// Cancel as soon as we find out that the folder is private
			return [null, true];
		}
		list($configArray, $configComplete) =
			$this->parseConfig($folder, $configName, $configArray, $configComplete, $level);
		$parentFolder = $folder->getParent();
		$path = $parentFolder->getPath();
		if ($path !== '' && $path !== '/') {
			$level++;

			return $this->getAlbumConfig(
				$parentFolder, $privacyChecker, $configName, $level, $configArray, $configComplete
			);
		}

		// We have reached the root folder
		return [$configArray, false];
	}

	/**
	 * Returns a parsed configuration if one was found in the current folder
	 *
	 * @param Folder $folder
	 * @param string $configName
	 * @param array $currentConfigArray
	 * @param bool $configComplete
	 * @param int $level
	 *
	 * @return array<array,bool>
	 */
	private function parseConfig(
		$folder, $configName, $currentConfigArray, $configComplete, $level
	) {
		$configArray = $currentConfigArray;
		// Let's try to find the missing information in the configuration located in this folder
		if (!$configComplete && $folder->nodeExists($configName)) {
			/** @type File $configFile */
			$configFile = $folder->get($configName);
			try {
				$rawConfig = $configFile->getContent();
				$saneConfig = $this->bomFixer($rawConfig);
				$parsedConfigArray = Yaml::parse($saneConfig);
				list($configArray, $configComplete) =
					$this->validateConfig($currentConfigArray, $parsedConfigArray, $level);
			} catch (\Exception $exception) {
				$this->logger->error(
					"Problem while parsing the configuration file : {path}",
					['path' => $folder->getPath() . '/' . $configFile->getPath()]
				);
			}
		}

		return [$configArray, $configComplete];
	}

	/**
	 * Removes the BOM from a file
	 *
	 * http://us.php.net/manual/en/function.pack.php#104151
	 *
	 * @param string $file
	 *
	 * @return string
	 */
	private function bomFixer($file) {
		$bom = pack("CCC", 0xef, 0xbb, 0xbf);
		if (strncmp($file, $bom, 3) === 0) {
			$file = substr($file, 3);
		}

		return $file;
	}

	/**
	 * Returns either the local config or one merged with a config containing sorting information
	 *
	 * @param array $currentConfigArray
	 * @param array $parsedConfigArray
	 * @param int $level
	 *
	 * @return array<array,bool>
	 */
	private function validateConfig($currentConfigArray, $parsedConfigArray, $level) {
		$configComplete = false;
		$sorting = $parsedConfigArray['sorting'];
		$sortOrder = $parsedConfigArray['sort_order'];
		$configArray = $parsedConfigArray;
		if ($sorting) {
			$configComplete = true;
			if ($level > 0) {
				// We only need the sorting information
				$currentConfigArray['sorting'] = $sorting;
				$currentConfigArray['sort_order'] = $sortOrder;
				$configArray = $currentConfigArray;
			}
		} else {
			if ($level > 0) {
				// Reset the array to what we had earlier since we didn't find any sorting information
				$configArray = $currentConfigArray;
			}
		}

		return [$configArray, $configComplete];
	}

}
