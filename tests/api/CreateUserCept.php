<?php
//use Codeception\Util\Xml as XmlUtils;
//
//$I = new ApiTester($scenario);
//$I->wantTo('create a user via the provisioning API');
//$baseUrl = '/ocs/v1.php/cloud';
//$I->amHttpAuthenticated('admin', 'admin');
//$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
//$I->sendPOST($baseUrl . '/users', ['userid' => 'test', 'password' => 'test']);
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsXml();
//$I->seeXmlResponseIncludes(
//	XmlUtils::toXml(
//		['status'   => 'ok']
//));
//
//// Make sure the user exists
//$I->sendGET($baseUrl . '/users/test');
//$I->seeResponseCodeIs(200);
//$I->seeResponseIsXml();
//$I->seeXmlResponseIncludes(
//	XmlUtils::toXml(
//		['status'   => 'ok']
//));
