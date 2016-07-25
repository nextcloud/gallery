<?php
/**
 * Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2016
 */

namespace Step\Api;

class Anonymous extends \ApiTester {

	public function connectToTheApi($apiUrl, $description) {
		$I = $this;
		$I->am('an app');
		$I->wantTo('connect to ' . $description . ' without credentials');
		$I->sendGET($apiUrl);
		$I->seeResponseCodeIs(401);
		$I->seeResponseIsJson();
	}

}
