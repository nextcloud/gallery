<?php
use \Page\Acceptance\Login;
use \Page\Gallery as GalleryPage;

$I = new AcceptanceTester($scenario);
$I->am('A standard user');
$I->wantTo('load the Gallery app');
$I->lookForwardTo('seeing my holiday pictures');

$loginPage = new Login($I);
$loginPage->login('admin', 'admin');
$loginPage->confirmLogin();


$I->click('.menutoggle');
$I->click('Gallery', '#navigation');
$I->seeCurrentUrlEquals(GalleryPage::$URL);
$I->seeElement(GalleryPage::$contentDiv);
