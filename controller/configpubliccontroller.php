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

namespace OCA\Gallery\Controller;

/**
 * Class ConfigPublicController
 *
 * Note: Type casting only works if the "@param" parameters are also included in this class as
 * their not yet inherited
 *
 * @package OCA\Gallery\Controller
 */
class ConfigPublicController extends ConfigController {

	/**
	 * @PublicPage
	 *
	 * Returns a list of supported features
	 *
	 * @inheritDoc
	 *
	 * @param bool $extramediatypes
	 */
	public function get($extramediatypes = false) {
		return parent::get($extramediatypes);
	}

}
