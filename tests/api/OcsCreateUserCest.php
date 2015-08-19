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

use Codeception\Util\Xml as XmlUtils;

/**
 * Class OcsCreateUserCest
 *
 * A sample test in cest format to compare it to Gherkin scenarios
 *
 * You would normally avoid the duplication seen in the methods by placing common elements in Steps
 * or the Actor class
 */
class OcsCreateUserCest {

	private $apiUrl;
	private $userId = 'BlueDragon';

	public function _before(ApiTester $I) {
		$this->apiUrl = '/ocs/v1.php/cloud';
	}

	public function _after(ApiTester $I) {
	}

	public function createUser(ApiTester $I, \Codeception\Scenario $scenario) {
		//$scenario->skip('ownCloud master is broken');
		$I->wantTo('create a user via the provisioning API');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
		$I->sendPOST(
			$this->apiUrl . '/users',
			['userid' => $this->userId, 'password' => 'test' . $this->userId]
		);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsXml();
		$I->seeXmlResponseIncludes(
			XmlUtils::toXml(
				['status' => 'ok']
			)
		);
	}

	public function checkUserExists(ApiTester $I, \Codeception\Scenario $scenario) {
		//$scenario->skip('ownCloud master is broken');
		$I->wantTo('make sure the user exists');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
		$I->sendGET($this->apiUrl . '/users/' . $this->userId);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsXml();
		$I->seeXmlResponseIncludes(
			XmlUtils::toXml(
				['status' => 'ok']
			)
		);

	}

	public function deleteUser(ApiTester $I, \Codeception\Scenario $scenario) {
		//$scenario->skip('ownCloud master is broken');
		$I->wantTo('delete the user');
		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
		$I->sendDELETE($this->apiUrl . '/users/' . $this->userId);
		$I->seeResponseCodeIs(200);
		$I->seeResponseIsXml();
		$I->seeXmlResponseIncludes(
			XmlUtils::toXml(
				['status' => 'ok']
			)
		);
	}
}

