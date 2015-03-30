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
 * Class PublicPreviewController
 *
 * Note: Type casting only works if the "@param" parameters are also included in this class as
 * their not yet inherited
 *
 * @package OCA\GalleryPlus\Controller
 */
class PublicPreviewController extends PreviewController {

	/**
	 * @PublicPage
	 *
	 * @inheritDoc
	 *
	 * @param bool $slideshow
	 */
	public function getMediaTypes($slideshow = false) {
		return parent::getMediaTypes($slideshow);
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
	 *
	 * @param string $images
	 * @param bool $square
	 * @param bool $scale
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
	 *
	 * @param string $file
	 * @param int $x
	 * @param int $y
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
