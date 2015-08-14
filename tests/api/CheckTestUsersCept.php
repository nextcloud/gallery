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

use Codeception\Util\Xml as XmlUtils;

$I = new ApiTester($scenario);
$I->wantTo('make sure my test users have been created');
$baseUrl = '/ocs/v1.php/cloud';
$I->amHttpAuthenticated('admin', 'admin');
$I->sendGET($baseUrl . '/users/tester');
$I->seeResponseCodeIs(200);
$I->seeResponseIsXml();
$I->seeXmlResponseIncludes(
	XmlUtils::toXml(
		['status'   => 'ok']
));
$I->sendGET($baseUrl . '/users/sharer');
$I->seeResponseCodeIs(200);
$I->seeResponseIsXml();
$I->seeXmlResponseIncludes(
	XmlUtils::toXml(
		['status'   => 'ok']
));
