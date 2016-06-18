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

namespace OCA\Gallery\Http;

use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http;

/**
 * A renderer for images
 *
 * @package OCA\Gallery\Http
 */
class ImageResponse extends Response {

	/**
	 * @var \OC_Image|string
	 */
	private $preview;

	/**
	 * Constructor
	 *
	 * @param array $image image meta data
	 * @param int $statusCode the HTTP status code, defaults to 200
	 */
	public function __construct(array $image, $statusCode = Http::STATUS_OK) {
		$name = $image['name'];
		$this->preview = $image['preview'];

		$this->setStatus($statusCode);
		$this->addHeader('Content-type', $image['mimetype'] . '; charset=utf-8');
		$this->addHeader('Content-Disposition',
						 'attachment; filename*=UTF-8\'\'' . rawurlencode($name) . '; filename="'
						 . rawurlencode($name) . '"'
		);
	}

	/**
	 * Returns the rendered image
	 *
	 * @return string the file
	 */
	public function render() {
		if ($this->preview instanceof \OC_Image) {
			// Uses imagepng() to output the image
			return $this->preview->data();
		} else {
			return $this->preview;
		}
	}

}
