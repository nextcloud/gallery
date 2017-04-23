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
 * @package OCA\Gallery\Controller
 */
trait PathManipulation {

	/**
	 * Returns a shortened path for the gallery view
	 *
	 * We only want to keep one folder between the current folder and the found media file
	 * /root/folder/sub1/sub2/file.ext
	 * becomes
	 * /root/folder/file.ext
	 *
	 * @param string $path the full path to a file, which never starts with a slash
	 * @param string $currFolderPath the current folder, which never starts with a slash
	 *
	 * @return string
	 */
	private function getReducedPath($path, $currFolderPath) {
		// Adding a slash to make sure we don't cut a folder in half
		if ($currFolderPath) {
			$currFolderPath .= '/';
			$relativePath = str_replace($currFolderPath, '', $path);
		} else {
			$relativePath = $path;
		}

		$subFolders = explode('/', $relativePath);

		if (count($subFolders) > 2) {
			$reducedPath = $currFolderPath . $subFolders[0] . '/' . array_pop($subFolders);
		} else {
			$reducedPath = $path;
		}

		return $reducedPath;
	}

}
