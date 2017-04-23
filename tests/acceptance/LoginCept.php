<?php
/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

use Page\Login as LoginPage;

$I = new AcceptanceTester($scenario);
$I->am('A standard user');
$I->wantTo('ensure that I can see the login page');

$I->amOnPage(LoginPage::$URL);
$I->seeElement(['css' => LoginPage::$loginButton]);
