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

namespace OCA\Gallery\Service;

use OCP\Files\File;


/**
 * Prepares the file to download
 *
 * @package OCA\Gallery\Service
 */
class DownloadService extends Service {

	use Base64Encode;

	/**
	 * Downloads the requested file
	 *
	 * @param File $file
	 * @param bool $base64Encode
	 *
	 * @return array|false
	 * @throws NotFoundServiceException
	 */
	public function downloadFile($file, $base64Encode = false) {
		try {
			$this->logger->debug(
				"[DownloadService] File to Download: {name}", ['name' => $file->getName()]
			);
			$download = [
				'preview'  => $file->getContent(),
				'mimetype' => $file->getMimeType()
			];

			if ($base64Encode) {
				$download['preview'] = $this->encode($download['preview']);
			}

			return $download;
		} catch (\Exception $exception) {
			throw new NotFoundServiceException('There was a problem accessing the file');
		}

	}

}
