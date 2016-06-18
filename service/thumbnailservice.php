<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2016
 */

namespace OCA\Gallery\Service;

/**
 * Deals with any thumbnail specific requests
 *
 * @package OCA\Gallery\Service
 */
class ThumbnailService {

	/**
	 * @var bool
	 */
	private $animatedPreview = false;
	/**
	 * @var bool
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
	 * @param double $scale
	 *
	 * @return array<double|boolean>
	 */
	public function getThumbnailSpecs($square, $scale) {
		$height = ceil(200 * $scale);
		if ($square) {
			$width = $height;
		} else {
			$width = 2 * $height;
		}

		$thumbnail = [$width, $height, !$square, $this->animatedPreview, $this->base64Encode];

		return $thumbnail;
	}

}
