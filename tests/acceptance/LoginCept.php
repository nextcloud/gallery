<?php
use Page\Login as LoginPage;

$I = new AcceptanceTester($scenario);
$I->am('A standard user');
$I->wantTo('ensure that I can see the login page');

$I->amOnPage(LoginPage::$URL);
$I->seeElement(LoginPage::$loginButton);
