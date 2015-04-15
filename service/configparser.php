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
 * Parses configuration files
 *
 * @package OCA\GalleryPlus\Service
 */
class ConfigParser {

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
	 *
	 * @throws ServiceException
	 */
	public function parseFolderConfig(
		$folder, $configName, $currentConfig, $configItems, $level, $isRootFolder
	) {
		/** @type File $configFile */
		$configFile = $folder->get($configName);
		try {
			$rawConfig = $configFile->getContent();
			$saneConfig = $this->bomFixer($rawConfig);
			$parsedConfig = Yaml::parse($saneConfig);
			//\OC::$server->getLogger()->debug("rawConfig : {path}", ['path' => $rawConfig]);
			list($config, $configItems) =
				$this->buildAlbumConfig(
					$currentConfig, $parsedConfig, $configItems, $level, $isRootFolder
				);
		} catch (\Exception $exception) {
			$errorMessage = "Problem while parsing the configuration file";
			throw new ServiceException($errorMessage);
		}

		return [$config, $configItems];
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
	 * @param array <string,bool> $configItems
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
	 * Determines if we already have everything we need for this configuration sub-section
	 *
	 * @param string $key
	 * @param array $parsedConfig
	 * @param bool $complete
	 *
	 * @return bool
	 */
	private function isConfigItemComplete($key, $parsedConfig, $complete) {
		return !(!$complete && array_key_exists($key, $parsedConfig));
	}

	/**
	 * Determines if we can use this configuration sub-section
	 *
	 * @param string $key
	 * @param array $parsedConfigItem
	 * @param int $level
	 * @param bool $isRootFolder
	 *
	 * @return bool
	 */
	private function isConfigUsable($key, $parsedConfigItem, $level, $isRootFolder) {
		$inherit = $this->isConfigInheritable($parsedConfigItem);
		$features = $this->isFeaturesListValid($key, $isRootFolder);

		return $level === 0 || $inherit || $features;
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
		return $key === 'features' && $isRootFolder;
	}
}
