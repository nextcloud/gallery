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

use \Page\Acceptance\Login;
use \Page\Gallery as GalleryPage;

$I = new AcceptanceTester($scenario);
$I->am('a standard user');
$I->wantTo('load the Gallery app');
$I->lookForwardTo('seeing my holiday pictures');

$loginPage = new Login($I);
$loginPage->login('admin', 'admin');
$loginPage->confirmLogin();


$I->click('.menutoggle');
$I->click('Gallery', '#navigation');
$I->seeCurrentUrlEquals(GalleryPage::$URL);
$I->seeElement(GalleryPage::$contentDiv);
