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
	 * @type int
	 */
	private $virtualRootLevel = null;

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
		$configItems = ['information' => false, 'sorting' => false, 'features' => false];

		list ($albumConfig, $privateAlbum) =
			$this->getAlbumConfig($folderNode, $privacyChecker, $configName, $configItems);

		if (!$privateAlbum) {
			$albumInfo = [
				'path'        => $folderPathFromRoot,
				'fileid'      => $folderNode->getID(),
				'permissions' => $folderNode->getPermissions()
			];

			// There is always an albumInfo, but the config may be empty
			$albumConfig = array_merge($albumInfo, $albumConfig);
		}

		return [$albumConfig, $privateAlbum];
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
		$isRootFolder = $this->isRootFolder($folder, $level);
		if ($folder->nodeExists($configName)) {
			list($config, $configItems) =
				$this->parseFolderConfig(
					$folder, $configName, $config, $configItems, $level, $isRootFolder
				);
		}
		if (!$isRootFolder) {
			return $this->getParentConfig(
				$folder, $privacyChecker, $configName, $configItems, $level, $config
			);
		}
		$config = $this->validatesInfoConfig($config);

		// We have reached the root folder
		return [$config, false];
	}

	/**
	 * Determines if we've reached the root folder
	 *
	 * @param Folder $folder
	 * @param int $level
	 *
	 * @return bool
	 */
	private function isRootFolder($folder, $level) {
		$isRootFolder = false;
		$rootFolder = $this->environment->getNode('');
		if ($folder->getPath() === $rootFolder->getPath()) {
			$isRootFolder = true;
		}
		$virtualRootFolder = $this->environment->getPathFromVirtualRoot($folder);
		if (empty($virtualRootFolder)) {
			$this->virtualRootLevel = $level;
		}

		return $isRootFolder;
	}

	/**
	 * Returns a parsed configuration if one was found in the current folder
	 *
	 * @param Folder $folder
	 * @param string $configName
	 * @param array $currentConfig
	 * @param array<string,bool> $configItems
	 * @param int $level
	 * @param bool $isRootFolder
	 *
	 * @return array <null|array,array<string,bool>>
	 */
	private function parseFolderConfig(
		$folder, $configName, $currentConfig, $configItems, $level, $isRootFolder
	) {
		/** @type File $configFile */
		$configFile = $folder->get($configName);
		try {
			$rawConfig = $configFile->getContent();
			$saneConfig = $this->bomFixer($rawConfig);
			$parsedConfig = Yaml::parse($saneConfig);

			list($config, $configItems) =
				$this->buildAlbumConfig(
					$currentConfig, $parsedConfig, $configItems, $level, $isRootFolder
				);
		} catch (\Exception $exception) {
			list($config, $configItems) = $this->buildErrorMessage($folder, $configItems);
		}

		return [$config, $configItems];
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
		$level = $albumConfig['information']['level'];
		if ($level > $this->virtualRootLevel) {
			$albumConfig['information']['description_link'] = null;
			$albumConfig['information']['copyright_link'] = null;
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
	 * @param array<string,bool> $configItems
	 * @param int $level
	 * @param array $config
	 *
	 * @return array<null|array,bool>
	 */
	private function getParentConfig(
		$folder, $privacyChecker, $configName, $configItems, $level, $config
	) {
		$parentFolder = $folder->getParent();
		$level++;

		return $this->getAlbumConfig(
			$parentFolder, $privacyChecker, $configName, $configItems, $level, $config
		);
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
	 * @param array<string,bool> $configItems
	 * @param int $level
	 * @param bool $isRootFolder
	 *
	 * @return array<null|array,array<string,bool>>
	 */
	private function buildAlbumConfig(
		$currentConfig, $parsedConfig, $configItems, $level, $isRootFolder
	) {
		foreach ($configItems as $key => $complete) {
			if (!$this->isConfigItemComplete($key, $parsedConfig, $complete)) {
				$parsedConfigItem = $parsedConfig[$key];
				if ($this->isConfigUsable($key, $parsedConfigItem, $level, $isRootFolder)) {
					list($configItem, $itemComplete) =
						$this->addConfigItem($key, $parsedConfigItem, $level);
					$currentConfig = array_merge($currentConfig, $configItem);
					$configItems[$key] = $itemComplete;
				}

			}
		}

		return [$currentConfig, $configItems];
	}

	/**
	 * Builds the error message to send back when there is an error
	 *
	 * @fixme Missing translations
	 *
	 * @param Folder $folder
	 * @param array <string,bool> $configItems
	 *
	 * @return array<null|array<string,string>,bool>
	 */
	private function buildErrorMessage($folder, $configItems) {
		$configPath = $this->environment->getPathFromVirtualRoot($folder);
		$errorMessage = "Problem while parsing the configuration file located in: $configPath";
		$this->logger->error($errorMessage);

		$config = ['information' => ['description' => $errorMessage]];
		foreach ($configItems as $key => $complete) {
			$configItems[$key] = true;
		}

		return [$config, $configItems];
	}

	/**
	 * Determines if we already have everything we need for this configuration sub-section
	 *
	 * @param string $key
	 * @param array $parsedConfig
	 * @param bool $complete
	 *
	 * @return bool
	 */
	private function isConfigItemComplete($key, $parsedConfig, $complete) {
		if (!$complete && array_key_exists($key, $parsedConfig)) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if we can use this configuration sub-section
	 *
	 * @param string $key
	 * @param array $parsedConfigItem
	 * @param int $level
	 * @param bool $isRootFolder
	 *
	 * @return array<null|array<string,string>,bool>
	 */
	private function isConfigUsable($key, $parsedConfigItem, $level, $isRootFolder) {
		$inherit = $this->isConfigInheritable($parsedConfigItem);
		$features = $this->isFeaturesListValid($key, $isRootFolder);

		if ($level === 0 || $inherit || $features) {
			return true;
		}

		return false;
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
		if ($key === 'sorting' && !array_key_exists('type', $parsedConfigItem)) {

			return [[], false];
		} else {
			$parsedConfigItem['level'] = $level;
			$configItem = [$key => $parsedConfigItem];
			$itemComplete = true;

			return [$configItem, $itemComplete];
		}
	}

	/**
	 * Determines if we can use a configuration sub-section found in parent folders
	 *
	 * @param array $parsedConfigItem
	 *
	 * @return bool
	 */
	private function isConfigInheritable($parsedConfigItem) {
		$inherit = false;
		if (array_key_exists('inherit', $parsedConfigItem)) {
			$inherit = $parsedConfigItem['inherit'];
		}

		if ($inherit === 'yes') {
			$inherit = true;
		}

		return $inherit;

	}

	/**
	 * Determines if we can use the "features" sub-section
	 *
	 * @param string $key
	 * @param bool $isRootFolder
	 *
	 * @return bool
	 */
	private function isFeaturesListValid($key, $isRootFolder) {
		if ($key === 'features' && $isRootFolder) {
			return true;
		}

		return false;
	}
}
