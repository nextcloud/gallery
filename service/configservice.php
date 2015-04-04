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
 * Checks the current and parent folders for configuration files and the privacy flag
 * Supports explicit inheritance
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
	 * @return array<null|array,bool>
	 */
	public function getAlbumInfo($folderNode, $folderPathFromRoot) {
		$configName = 'gallery.cnf';
		$privacyChecker = '.nomedia';
		$configItems = ['information' => false, 'sorting' => false];

		list ($albumConfig, $privateAlbum) =
			$this->getAlbumConfig($folderNode, $privacyChecker, $configName, $configItems);

		if (!$privateAlbum) {
			$albumConfig =
				$this->addAlbumPermissions($albumConfig, $folderNode, $folderPathFromRoot);
		}

		return [$albumConfig, $privateAlbum];
	}

	/**
	 * Returns an album configuration array
	 *
	 * Goes through all the parent folders until either we're told the album is private or we've
	 * gathered all the information we need
	 *
	 * @param Folder $folder
	 * @param string $privacyChecker
	 * @param string $configName
	 * @param array $configItems
	 * @param int $level
	 * @param array $config
	 *
	 * @return array<null|array,bool>
	 */
	private function getAlbumConfig(
		$folder, $privacyChecker, $configName, $configItems, $level = 0, $config = []
	) {
		if ($folder->nodeExists($privacyChecker)) {
			// Cancel as soon as we find out that the folder is private
			return [null, true];
		}
		if ($folder->nodeExists($configName)) {
			list($config, $configItems) =
				$this->parseFolderConfig($folder, $configName, $config, $configItems, $level);
		}
		if (!$this->isConfigComplete($configItems)) {
			return $this->getParentConfig(
				$folder, $privacyChecker, $configName, $configItems, $level, $config
			);
		}

		// We have found a valid config or have reached the root folder
		return [$config, false];
	}

	/**
	 * Adds the permission settings to the album config
	 *
	 * @param null|array<string,string|int> $albumConfig
	 * @param Folder $folderNode
	 * @param string $folderPathFromRoot
	 *
	 * @return array
	 */
	private function addAlbumPermissions($albumConfig, $folderNode, $folderPathFromRoot) {
		$albumInfo = [
			'path'        => $folderPathFromRoot,
			'fileid'      => $folderNode->getID(),
			'permissions' => $folderNode->getPermissions()
		];

		return array_merge($albumConfig, $albumInfo);
	}

	/**
	 * Returns a parsed configuration if one was found in the current folder
	 *
	 * @param Folder $folder
	 * @param string $configName
	 * @param array $currentConfig
	 * @param array $configItems
	 * @param int $level
	 *
	 * @return array<null|array,array<string,bool>>
	 */
	private function parseFolderConfig($folder, $configName, $currentConfig, $configItems, $level) {
		$config = $currentConfig;
		/** @type File $configFile */
		$configFile = $folder->get($configName);
		try {
			$rawConfig = $configFile->getContent();
			$saneConfig = $this->bomFixer($rawConfig);
			$parsedConfig = Yaml::parse($saneConfig);

			list($config, $configItems) =
				$this->buildAlbumConfig($currentConfig, $parsedConfig, $configItems, $level);
		} catch (\Exception $exception) {
			$this->logger->error(
				"Problem while parsing the configuration file : {path}",
				['path' => $folder->getPath() . '/' . $configFile->getPath()]
			);
		}

		return [$config, $configItems];
	}

	/**
	 * Decides whether we have all the elements we need or not
	 *
	 * @param $configItems
	 *
	 * @return bool
	 */
	private function isConfigComplete($configItems) {
		$configComplete = false;
		$completedItems = 0;
		foreach ($configItems as $key => $complete) {
			if ($complete === true) {
				$completedItems++;
			}
		}
		if ($completedItems === sizeof($configItems)) {
			$configComplete = true;
		}

		return $configComplete;
	}

	/**
	 * Looks for an album configuration in the parent folder
	 *
	 * @param Folder $folder
	 * @param string $privacyChecker
	 * @param string $configName
	 * @param array $configItems
	 * @param int $level
	 * @param array <null|string,string> $config
	 *
	 * @return array<null|array,bool>
	 */
	private function getParentConfig(
		$folder, $privacyChecker, $configName, $configItems, $level, $config
	) {
		$parentFolder = $folder->getParent();
		$path = $parentFolder->getPath();
		if ($path !== '' && $path !== '/') {
			$level++;

			return $this->getAlbumConfig(
				$parentFolder, $privacyChecker, $configName, $configItems, $level, $config
			);
		}

		return [$config, false];
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
	 * @param array $currentConfig
	 * @param array $parsedConfig
	 * @param array $configItems
	 * @param int $level
	 *
	 * @return array<null|array,array<string,bool>>
	 */
	private function buildAlbumConfig($currentConfig, $parsedConfig, $configItems, $level) {
		foreach ($configItems as $key => $complete) {
			if (!$complete && array_key_exists($key, $parsedConfig)) {
				$parsedConfigItem = $parsedConfig[$key];
				list($configItem, $itemComplete) =
					$this->addConfigItem($key, $parsedConfigItem, $level);

				$currentConfig = array_merge($currentConfig, $configItem);
				$configItems[$key] = $itemComplete;
			}
		}

		return [$currentConfig, $configItems];
	}

	/**
	 * Adds a config sub-section to the global config
	 *
	 * @param string $key
	 * @param array $parsedConfigItem
	 * @param int $level
	 *
	 * @return array<null|array<string,string>,bool>
	 */
	private function addConfigItem($key, $parsedConfigItem, $level) {
		$configItem = [];
		$itemComplete = false;
		$inherit = false;

		if (array_key_exists('inherit', $parsedConfigItem)) {
			$inherit = $parsedConfigItem['inherit'];
		}

		if ($level === 0 || $inherit === 'yes') {
			$parsedConfigItem['level'] = $level;
			$configItem = [$key => $parsedConfigItem];
			$itemComplete = true;
		}

		return [$configItem, $itemComplete];
	}

}
