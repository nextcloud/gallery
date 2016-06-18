<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2016
 */

namespace OCA\Gallery\Config;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

use OCP\Files\Folder;
use OCP\Files\File;

/**
 * Parses configuration files
 *
 * @package OCA\Gallery\Config
 */
class ConfigParser {
	/** @var ConfigValidator */
	private $configValidator;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->configValidator = new ConfigValidator();
	}

	/**
	 * Returns a parsed global configuration if one was found in the root folder
	 *
	 * @param Folder $folder the current folder
	 * @param string $configName name of the configuration file
	 *
	 * @return null|array
	 */
	public function getFeaturesList($folder, $configName) {
		$featuresList = [];
		$parsedConfig = $this->parseConfig($folder, $configName);
		$key = 'features';
		if (array_key_exists($key, $parsedConfig)) {
			$featuresList = $this->parseFeatures($parsedConfig[$key]);
		}

		return $featuresList;
	}

	/**
	 * Returns a parsed configuration if one was found in the current folder
	 *
	 * @param Folder $folder the current folder
	 * @param string $configName name of the configuration file
	 * @param array $currentConfig the configuration collected so far
	 * @param array <string,bool> $completionStatus determines if we already have all we need for a
	 *     config sub-section
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
	 *
	 * @return array <null|array,array<string,bool>>
	 * @throws ConfigException
	 */
	public function getFolderConfig($folder, $configName, $currentConfig, $completionStatus, $level
	) {
		$parsedConfig = $this->parseConfig($folder, $configName);
		list($config, $completionStatus) =
			$this->buildAlbumConfig($currentConfig, $parsedConfig, $completionStatus, $level);

		return [$config, $completionStatus];
	}

	/**
	 * Returns a parsed configuration
	 *
	 * @param Folder $folder the current folder
	 * @param string $configName
	 *
	 * @return array
	 *
	 * @throws ConfigException
	 */
	private function parseConfig($folder, $configName) {
		try {
			/** @var File $configFile */
			$configFile = $folder->get($configName);
			$rawConfig = $configFile->getContent();
			$saneConfig = $this->bomFixer($rawConfig);
			$yaml = new Parser();
			$parsedConfig = $yaml->parse($saneConfig);

			//\OC::$server->getLogger()->debug("rawConfig : {path}", ['path' => $rawConfig]);

			return $parsedConfig;
		} catch (\Exception  $exception) {
			$errorMessage = "Problem while reading or parsing the configuration file";
			throw new ConfigException($errorMessage);
		}
	}

	/**
	 * Returns only the features which have been enabled
	 *
	 * @param array <string,string> $featuresList the list of features collected from the
	 *     configuration file
	 *
	 * @return array
	 */
	private function parseFeatures($featuresList) {
		$parsedFeatures = $featuresList;
		if (!empty($parsedFeatures)) {
			$parsedFeatures = array_keys($featuresList, 'yes');
		}

		return $parsedFeatures;
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
	 * @param array $currentConfig the configuration collected so far
	 * @param array $parsedConfig the configuration collected in the current folder
	 * @param array <string,bool> $completionStatus determines if we already have all we need for a
	 *     config sub-section
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
	 *
	 * @return array <null|array,array<string,bool>>
	 */
	private function buildAlbumConfig($currentConfig, $parsedConfig, $completionStatus, $level) {
		foreach ($completionStatus as $key => $complete) {
			if (!$this->isConfigItemComplete($key, $parsedConfig, $complete)) {
				$parsedConfigItem = $parsedConfig[$key];
				if ($this->isConfigUsable($key, $parsedConfigItem, $level)) {
					list($configItem, $itemComplete) =
						$this->addConfigItem($key, $parsedConfigItem, $level);
					$currentConfig = array_merge($currentConfig, $configItem);
					$completionStatus[$key] = $itemComplete;
				}
			}
		}

		return [$currentConfig, $completionStatus];
	}

	/**
	 * Determines if we already have everything we need for this configuration sub-section
	 *
	 * @param string $key the configuration sub-section identifier
	 * @param array $parsedConfig the configuration for that sub-section
	 * @param bool $complete
	 *
	 * @return bool
	 */
	private function isConfigItemComplete($key, $parsedConfig, $complete) {
		return !(!$complete
				 && array_key_exists($key, $parsedConfig)
				 && !empty($parsedConfig[$key]));
	}

	/**
	 * Determines if we can use this configuration sub-section
	 *
	 * It's possible in two cases:
	 *    * the configuration was collected from the currently opened folder
	 *    * the configuration was collected in a parent folder and is inheritable
	 *
	 * We also need to make sure that the values contained in the configuration are safe for web use
	 *
	 * @param string $key the configuration sub-section identifier
	 * @param array $parsedConfigItem the configuration for a sub-section
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
	 *
	 * @return bool
	 */
	private function isConfigUsable($key, $parsedConfigItem, $level) {
		$inherit = $this->isConfigInheritable($parsedConfigItem);

		$usable = $level === 0 || $inherit;

		$safe = $this->configValidator->isConfigSafe($key, $parsedConfigItem);

		return $usable && $safe;
	}

	/**
	 * Adds a config sub-section to the global config
	 *
	 * @param string $key the configuration sub-section identifier
	 * @param array $parsedConfigItem the configuration for a sub-section
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
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
	 * @param array $parsedConfigItem the configuration for a sub-section
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

}
