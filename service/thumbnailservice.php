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

namespace OCA\GalleryPlus\Service;

use OCA\GalleryPlus\Environment\Environment;
use OCA\GalleryPlus\Preview\Preview;
use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Creates thumbnails for the list of images which is submitted to
 * the service
 *
 * Uses EventSource to send back thumbnails as soon as they're ready
 *
 * @package OCA\GalleryPlus\Service
 */
class ThumbnailService {

	/**
	 * @type bool
	 */
	private $animatedPreview = false;
	/**
	 * @type bool
	 */
	private $base64Encode = true;

	/**
	 * Returns thumbnail specs
	 *
	 *    * Album thumbnails need to be 200x200 and some will be resized by the
	 *      browser to 200x100 or 100x100.
	 *    * Standard thumbnails are 400x200.
	 *
	 * @param bool $square
	 * @param bool $scale
	 *
	 * @return array
	 */
	public function getThumbnailSpecs($square, $scale) {
		$height = 200 * $scale;
		if ($square) {
			$width = $height;
		} else {
			$width = 2 * $height;
		}

		$thumbnail = [$width, $height, !$square, $this->animatedPreview, $this->base64Encode];

		return $thumbnail;
	}

}
