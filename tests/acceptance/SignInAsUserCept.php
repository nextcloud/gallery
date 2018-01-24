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

use \Page\Acceptance\Login;
use \Page\Gallery as GalleryPage;

$I = new AcceptanceTester($scenario);
$I->am('a standard user');
$I->wantTo('load the Gallery app');
$I->lookForwardTo('seeing my holiday pictures');

$loginPage = new Login($I);
$loginPage->login('admin', 'admin');
$loginPage->confirmLogin();

$I->click('//li[*[normalize-space(text()) = "Gallery"]]/a', '#appmenu');
$I->seeCurrentUrlEquals(GalleryPage::$URL);
$I->seeElement(['css' => GalleryPage::$contentDiv]);
