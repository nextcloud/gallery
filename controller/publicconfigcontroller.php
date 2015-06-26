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

namespace OCA\GalleryPlus\Controller;

/**
 * Class PublicConfigController
 *
 * Note: Type casting only works if the "@param" parameters are also included in this class as
 * their not yet inherited
 *
 * @package OCA\GalleryPlus\Controller
 */
class PublicConfigController extends ConfigController {

	/**
	 * @PublicPage
	 *
	 * Returns a list of supported features
	 *
	 * @inheritDoc
	 */
	public function getConfig($slideshow = false) {
		return parent::getConfig($slideshow);
	}

}
