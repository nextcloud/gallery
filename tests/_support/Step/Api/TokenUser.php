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

}
