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
	 *
	 * @return File
	 * @throws NotFoundServiceException
	 */
	public function downloadFile($file) {
		try {
			$this->logger->debug(
				"[DownloadService] File to Download: {name}", ['name' => $file->getName()]
			);
			return $file;
		} catch (\Exception $exception) {
			throw new NotFoundServiceException('There was a problem accessing the file');
		}

	}

}
