<?php
/**
 * Nextcloud - Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Config;

/**
 * Validates parsed configuration elements
 *
 * @package OCA\Gallery\Config
 */
class ConfigValidator {

	/**
	 * Determines if the content of that sub-section is safe for web use
	 *
	 * @param string $key the configuration sub-section identifier
	 * @param array $parsedConfigItem the configuration for a sub-section
	 *
	 * @return bool
	 */
	public function isConfigSafe($key, $parsedConfigItem) {
		$safe = true;

		switch ($key) {
			case 'sorting':
				$safe = $this->isSortingSafe('type',$parsedConfigItem, $safe);
				$safe = $this->isSortingSafe('order',$parsedConfigItem, $safe);
				break;
			case 'design':
				$safe = $this->isDesignColourSafe($parsedConfigItem, $safe);
				break;
		}

		return $safe;
	}

	/**
	 * Determines if the sorting type found in the config file is safe for web use
	 * @param string  will specify the key to check 'type' or 'order'
	 * @param array $parsedConfigItem the sorting configuration to analyse
	 * @param bool $safe whether the current config has been deemed safe to use so far
	 * @return bool
	 */
	private function isSortingSafe($key,$parsedConfigItem, $safe) {
		if ($safe && array_key_exists($key, $parsedConfigItem)) {
			$safe = $safe && $this->sortingValidator($key, $parsedConfigItem[ $key ]);
		}

		return $safe;
	}

	/**
	 * Determines if the background colour found in the config file is safe for web use
	 *
	 * @param array $parsedConfigItem the design configuration to analyse
	 * @param bool $safe whether the current config has been deemed safe to use so far
	 *
	 * @return bool
	 */
	private function isDesignColourSafe($parsedConfigItem, $safe) {
		if (array_key_exists('background', $parsedConfigItem)) {
			$background = $parsedConfigItem['background'];
			$safe = $safe && ctype_xdigit(substr($background, 1));
		}

		return $safe;
	}

	/**
	 * Validates the parsed sorting values against allowed values
	 *
	 * @param string $section the section in the sorting config to be analysed
	 * @param string $value the value found in that section
	 *
	 * @return bool
	 */
	private function sortingValidator($section, $value) {
		if ($section === 'type') {
			$validValues = ['date', 'name'];
		} else {
			$validValues = ['des', 'asc'];
		}

		return in_array($value, $validValues);
	}

}
