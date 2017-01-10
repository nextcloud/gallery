<?php
/**
 * Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2014-2016
 */

namespace OCA\Gallery\Preview;

use OCP\Files\File;
use OCP\Files\NotFoundException;
use OCP\Image;
use OCP\IPreview;

/**
 * Generates previews
 *
 * @package OCA\Gallery\Preview
 */
class Preview {

	/**
	 * @var IPreview
	 */
	private $previewManager;

	/**
	 * Constructor
	 *
	 * @param IPreview $previewManager
	 */
	public function __construct(
		IPreview $previewManager
	) {
		$this->previewManager = $previewManager;
	}

	/**
	 * Returns true if the passed mime type is supported
	 *
	 * @param string $mimeType
	 *
	 * @return boolean
	 */
	public function isMimeSupported($mimeType = '*') {
		return $this->previewManager->isMimeSupported($mimeType);
	}

	/**
	 * @param File $file
	 * @param int $maxX
	 * @param int $maxY
	 * @param bool $keepAspect
	 * @return false|array<string,string|\OC_Image>
	 */
	public function getPreview(File $file, $maxX, $maxY, $keepAspect) {
		try {
			$preview = $this->previewManager->getPreview($file, $maxX, $maxY, !$keepAspect);
		} catch (NotFoundException $e) {
			return false;
		}

		$img = new Image($preview->getContent());
		return [
			'preview'  => $img,
			'mimetype' => $img->mimeType()
		];
	}

}
