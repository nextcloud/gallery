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

use Helper\DataSetup;
use Codeception\Util\Xml as XmlUtils;

/**
 * Class CheckTestUsersCest
 */
class CheckTestUsersCest {

	private $setupData;
	private $userId;
	private $sharerUserId;
	private $baseUrl = '/ocs/v1.php/cloud';


	/**
	 * Injects objects we need
	 *
	 * @param DataSetup $setupData
	 */
	protected function _inject(DataSetup $setupData) {
		$this->setupData = $setupData;
	}

	public function _before(ApiTester $I) {
		$this->userId = $this->setupData->userId;
		$this->sharerUserId = $this->setupData->sharerUserId;
	}

	public function _after(ApiTester $I) {
	}

	public function testTestUsersCreation(ApiTester $I) {
		$I->wantTo('make sure my test users have been created');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->sendGET($this->baseUrl . '/users/' . $this->userId);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsXml();
		$I->seeXmlResponseIncludes(
			XmlUtils::toXml(
				['status' => 'ok']
			)
		);
		$I->sendGET($this->baseUrl . '/users/' . $this->sharerUserId);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsXml();
		$I->seeXmlResponseIncludes(
			XmlUtils::toXml(
				['status' => 'ok']
			)
		);
	}
}
