<?php
/**
 * owncloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\GalleryPlus\Config;

use OCP\Util;

/**
 * Thrown when the configuration parser cannot parse a file
 */
class ConfigException extends \Exception {

	/**
	 * Constructor
	 *
	 * @param string $msg the message contained in the exception
	 */
	public function __construct($msg) {
		Util::writeLog('galleryplus', 'Exception: ' . $msg, Util::ERROR);
		parent::__construct($msg);
	}
}
