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
	 * @param File $file
	 * @param bool $base64Encode
	 *
	 * @return false|array
	 *
	 * @throws NotFoundServiceException
	 */
	public function downloadFile($file = null, $base64Encode = false) {
		$download = false;
		try {
			// If no file is given, we try to get it from the token
			if (is_null($file)) {
				$this->logger->debug("[DownloadService] File to Download: File linked with token");
				$file = $this->environment->getResourceFromPath('');
			} else {
				$this->logger->debug(
					"[DownloadService] File to Download: {name}", ['name' => $file->getName()]
				);
			}
			$download = [
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
