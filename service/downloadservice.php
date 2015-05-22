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

namespace OCA\GalleryPlus\Service;

use OCP\Files\File;


/**
 * Prepares the file to download
 *
 * @package OCA\GalleryPlus\Service
 */
class DownloadService extends Service {

	use Base64Encode;

	/**
	 * Downloads the requested file
	 *
	 * @param string $image
	 * @param bool $base64Encode
	 *
	 * @return false|array
	 *
	 * @throws NotFoundServiceException
	 */
	public function downloadFile($image = '', $base64Encode = false) {
		$this->logger->debug("[DownloadService] File to Download: $image");
		$file = null;
		$download = false;
		try {
			/** @var File $file */
			$file = $this->environment->getResourceFromPath($image);
			$download = [
				'path'     => $image,
				'preview'  => $file->getContent(),
				'mimetype' => $file->getMimeType()
			];

			if ($base64Encode) {
				$download['preview'] = $this->encode($download['preview']);
			}
		} catch (\Exception $exception) {
			$this->logAndThrowNotFound($exception->getMessage());
		}

		return $download;
	}

}
