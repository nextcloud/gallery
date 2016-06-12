<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace Step\Api;

class TokenUser extends \ApiTester {

	/**
	 * Retrieves the shared file and its token
	 */
	public function getSharedFileInformation() {
		$I = $this;
		$sharedFile = $I->getSharedFile();

		$fileMetaData = [
			'fileId'    => $sharedFile['file']->getId(),
			'name'      => $sharedFile['file']->getName(),
			'mediatype' => $sharedFile['file']->getMimetype(),
			'token'     => $sharedFile['token']
		];

		return $fileMetaData;
	}

	/**
	 * Retrieves the shared file and its token
	 */
	public function getPrivateFileInformation() {
		$I = $this;
		$privateFile = $I->getPrivateFile();

		$fileMetaData = [
			'fileId' => $privateFile['file']->getId(),
		];

		return $fileMetaData;
	}

	/**
	 * Retrieves the shared folder and its token
	 */
	public function getSharedFolderInformation() {
		$I = $this;
		$sharedFolder = $I->getSharedFolder();

		$folderMetaData = [
			'fileId'   => $sharedFolder['folder']->getId(),
			'name'     => $sharedFolder['folder']->getName(),
			'token'    => $sharedFolder['token'],
			'password' => $sharedFolder['password']
		];

		return $folderMetaData;
	}
}
