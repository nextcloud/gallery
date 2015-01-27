<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\Controller;

/**
 * Class PublicServiceController
 *
 * @package OCA\GalleryPlus\Controller
 */
class PublicServiceController extends ServiceController {

	/**
	 * @PublicPage
	 *
	 * @inheritDoc
	 */
	public function getTypes() {
		return parent::getTypes();
	}

	/**
	 * @PublicPage
	 *
	 * Returns a list of all images from the folder the link gives access to
	 *
	 * @inheritDoc
	 */
	public function getImages() {
		return parent::getImages();
	}

	/**
	 * @PublicPage
	 * @UseSession
	 *
	 * Generates thumbnails for public galleries
	 *
	 * The session needs to be maintained open or previews can't be generated
	 * for files located on encrypted storage
	 *
	 * @inheritDoc
	 */
	public function getThumbnails($images, $square, $scale) {
		return parent::getThumbnails($images, $square, $scale);
	}

	/**
	 * @PublicPage
	 * @UseSession
	 *
	 * Shows a large preview of a file
	 *
	 * The session needs to be maintained open or previews can't be generated
	 * for files located on encrypted storage
	 *
	 * @inheritDoc
	 */
	public function showPreview($file, $x, $y) {
		return parent::showPreview($file, $x, $y);
	}

	/**
	 * @PublicPage
	 * @UseSession
	 *
	 * Downloads the file
	 *
	 * The session needs to be maintained open or previews can't be generated
	 * for files located on encrypted storage
	 *
	 * @inheritDoc
	 */
	public function downloadPreview($file) {
		return parent::downloadPreview($file);
	}

}
