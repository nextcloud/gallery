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
 * Class FilesPublicController
 *
 * Note: Type casting only works if the "@param" parameters are also included in this class as
 * their not yet inherited
 *
 * @package OCA\Gallery\Controller
 */
class FilesPublicController extends FilesController {

	/**
	 * @PublicPage
	 *
	 * Returns a list of all images from the folder the link gives access to
	 *
	 * @inheritDoc
	 *
	 * @param string $location a path representing the current album in the app
	 * @param string $features the list of supported features
	 * @param string $etag the last known etag in the client
	 * @param string $mediatypes the list of supported media types
	 */
	public function getList($location, $features, $etag, $mediatypes) {
		return parent::getList($location, $features, $etag, $mediatypes);
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * Sends the file matching the fileId
	 *
	 * @inheritDoc
	 *
	 * @param int $fileId the ID of the file we want to download
	 * @param string|null $filename
	 */
	public function download($fileId, $filename = null) {
		return parent::download($fileId, $filename);
	}
}
