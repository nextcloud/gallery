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

use OCP\Files\Folder;

/**
 * Finds configurations files and returns a configuration array
 *
 * Checks the current and parent folders for configuration files and the privacy flag
 * Supports explicit inheritance
 *
 * @package OCA\GalleryPlus\Service
 */
class ConfigService extends FilesService {

	/**
	 * @type array <string,bool>
	 */
	private $configItems = ['information' => false, 'sorting' => false, 'features' => false];

	/**
	 * @type ConfigParser
	 */
	private $configParser;

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
	 * @return null|array
	 */
	public function getAlbumInfo($folderNode, $folderPathFromRoot) {
		$configName = 'gallery.cnf';
		$privacyChecker = '.nomedia';

		$this->configParser = new ConfigParser();
		list ($albumConfig, $privateAlbum) =
			$this->getAlbumConfig($folderNode, $privacyChecker, $configName);
		if ($privateAlbum) {
			$this->logAndThrowForbidden('Album is private or unavailable');
		}
		$albumInfo = [
			'path'        => $folderPathFromRoot,
			'fileid'      => $folderNode->getID(),
			'permissions' => $folderNode->getPermissions()
		];
		// There is always an albumInfo, but the albumConfig may be empty
		$albumConfig = array_merge($albumInfo, $albumConfig);

		return $albumConfig;
	}

	/**
	 * Returns an album configuration array
	 *
	 * Goes through all the parent folders until either we're told the album is private or we've
	 * reached the root folder
	 *
	 * @param Folder $folder
	 * @param string $privacyChecker
	 * @param string $configName
	 * @param int $level
	 * @param array $config
	 *
	 * @return array<null|array,bool>
	 */
	private function getAlbumConfig(
		$folder, $privacyChecker, $configName, $level = 0, $config = []
	) {
		if ($folder->nodeExists($privacyChecker)) {
			// Cancel as soon as we find out that the folder is private or external
			return [null, true];
		}
		$isRootFolder = $this->isRootFolder($folder, $level);
		if ($folder->nodeExists($configName)) {
			list($config) =
				$this->parseFolderConfig(
					$folder, $configName, $config, $level, $isRootFolder
				);
		}
		if (!$isRootFolder) {
			return $this->getParentConfig(
				$folder, $privacyChecker, $configName, $level, $config
			);
		}
		$config = $this->validatesInfoConfig($config);

		// We have reached the root folder
		return [$config, false];
	}

	/**
	 * Returns a parsed configuration if one was found in the current folder or generates an error
	 * message to send back
	 *
	 * @param Folder $folder
	 * @param string $configName
	 * @param $config
	 * @param $level
	 * @param $isRootFolder
	 *
	 * @return array
	 */
	private function parseFolderConfig($folder, $configName, $config, $level, $isRootFolder) {
		try {
			list($config, $configItems) =
				$this->configParser->parseFolderConfig(
					$folder, $configName, $config, $this->configItems, $level, $isRootFolder
				);
			$this->configItems = $configItems;
		} catch (ServiceException $exception) {
			list($config) =
				$this->buildErrorMessage($exception, $folder);
		}

		return [$config];
	}

	/**
	 * Builds the error message to send back when there is an error
	 *
	 * @fixme Missing translations
	 *
	 * @param ServiceException $exception
	 * @param Folder $folder
	 *
	 * @return array <null|array<string,string>,bool>
	 * @internal param $array <string,bool> $configItems
	 *
	 */
	private function buildErrorMessage($exception, $folder) {
		$configPath = $this->environment->getPathFromVirtualRoot($folder);
		$errorMessage = $exception->getMessage() . "</br></br>Config location: /$configPath";
		$this->logger->error($errorMessage);

		$config = ['information' => ['description' => $errorMessage]];
		$configItems = $this->configItems;
		foreach ($configItems as $key => $complete) {
			$configItems[$key] = true;
		}
		$this->configItems = $configItems;

		return [$config];
	}

	/**
	 * Removes links if they were collected outside of the virtual root
	 *
	 * This is for shared folders which have a virtual root
	 *
	 * @param array $albumConfig
	 *
	 * @return array
	 */
	private function validatesInfoConfig($albumConfig) {
		$this->virtualRootLevel;
		if (array_key_exists('information', $albumConfig)) {
			$info = $albumConfig['information'];
			if (array_key_exists('level', $info)) {
				$level = $info['level'];
				if ($level > $this->virtualRootLevel) {
					$albumConfig['information']['description_link'] = null;
					$albumConfig['information']['copyright_link'] = null;
				}
			}
		}

		return $albumConfig;
	}

	/**
	 * Looks for an album configuration in the parent folder
	 *
	 * We will look up to the real root folder, not the virtual root of a shared folder
	 *
	 * @param Folder $folder
	 * @param string $privacyChecker
	 * @param string $configName
	 * @param int $level
	 * @param array $config
	 *
	 * @return array<null|array,bool>
	 */
	private function getParentConfig($folder, $privacyChecker, $configName, $level, $config) {
		$parentFolder = $folder->getParent();
		$level++;

		return $this->getAlbumConfig(
			$parentFolder, $privacyChecker, $configName, $level, $config
		);
	}

}
