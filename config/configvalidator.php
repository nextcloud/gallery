<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
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
				$safe = $this->isSortingTypeSafe($parsedConfigItem, $safe);
				$safe = $this->isSortingOrderSafe($parsedConfigItem, $safe);
				break;
			case 'design':
				$safe = $this->isDesignColourSafe($parsedConfigItem, $safe);
				break;
		}

		return $safe;
	}

	/**
	 * Determines if the sorting type found in the config file is safe for web use
	 *
	 * @param array $parsedConfigItem the sorting configuration to analyse
	 * @param bool $safe whether the current config has been deemed safe to use so far
	 *
	 * @return bool
	 */
	private function isSortingTypeSafe($parsedConfigItem, $safe) {
		if ($safe && array_key_exists('type', $parsedConfigItem)) {
			$type = $parsedConfigItem['type'];
			if ($type === 'date' || $type === 'name') {
				$safe = true;
			} else {
				$safe = false;
			}
		}

		return $safe;
	}

	/**
	 * Determines if the sorting order found in the config file is safe for web use
	 *
	 * @param array $parsedConfigItem the sorting configuration to analyse
	 * @param bool $safe whether the current config has been deemed safe to use so far
	 *
	 * @return bool
	 */
	private function isSortingOrderSafe($parsedConfigItem, $safe) {
		if ($safe && array_key_exists('order', $parsedConfigItem)) {
			$order = $parsedConfigItem['order'];
			if ($order === 'des' || $order === 'asc') {
				$safe = $safe && true;
			} else {
				$safe = false;
			}
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
			if (ctype_xdigit(substr($background, 1))) {
				$safe = true;
			} else {
				$safe = false;
			}
		}

		return $safe;
	}

}
