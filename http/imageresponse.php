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

namespace OCA\GalleryPlus\Http;

use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http;

/**
 * A renderer for images
 *
 * @package OCA\GalleryPlus\Http
 */
class ImageResponse extends Response {

	/**
	 * @type string
	 */
	private $path;
	/**
	 * @type \OC_Image
	 */
	private $preview;

	/**
	 * Constructor
	 *
	 * @param array $image image meta data
	 * @param int $statusCode the HTTP status code, defaults to 200
	 */
	public function __construct(array $image, $statusCode = Http::STATUS_OK) {
		$this->path = $image['path'];
		$this->preview = $image['preview'];

		$this->setStatus($statusCode);
		$this->addHeader(
			'Content-type', $image['mimetype'] . '; charset=utf-8'
		);

		\OCP\Response::setContentDispositionHeader(
			basename($this->path), 'attachment'
		);

		/*\OC::$server
			->getLogger()
			->debug(
			"[ImageResponse] Here is the content: " . substr(
				(string)$this->previewData, 0, 20
			), array(
					'app' => 'galleryplus'
			   )
		);*/
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
