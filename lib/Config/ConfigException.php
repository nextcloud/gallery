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
		Util::writeLog('gallery', 'Exception: ' . $msg, Util::ERROR);
		parent::__construct($msg);
	}
}
