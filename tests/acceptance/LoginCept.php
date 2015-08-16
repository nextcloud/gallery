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

use Page\Login as LoginPage;

$I = new AcceptanceTester($scenario);
$I->am('A standard user');
$I->wantTo('ensure that I can see the login page');

$I->amOnPage(LoginPage::$URL);
$I->seeElement(LoginPage::$loginButton);
