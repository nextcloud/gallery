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

namespace OCA\Gallery\Controller;

/**
 * Class PreviewPublicController
 *
 * Note: Type casting only works if the "@param" parameters are also included in this class as
 * their not yet inherited
 *
 * @package OCA\Gallery\Controller
 */
class PreviewPublicController extends PreviewController {

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
	 * @param string $ids the ID of the files of which we need thumbnail previews of
	 * @param bool $square
	 * @param float $scale
	 */
	public function getThumbnails($ids, $square, $scale) {
		return parent::getThumbnails($ids, $square, $scale);
	}

	/**
	 * @PublicPage
	 * @UseSession
	 * @NoCSRFRequired
	 *
	 * Shows a large preview of a file
	 *
	 * The session needs to be maintained open or previews can't be generated
	 * for files located on encrypted storage
	 *
	 * @inheritDoc
	 *
	 * @param int $fileId the ID of the file of which we need a large preview of
	 * @param int $width
	 * @param int $height
	 */
	public function getPreview($fileId, $width, $height) {
		return parent::getPreview($fileId, $width, $height);
	}

}
