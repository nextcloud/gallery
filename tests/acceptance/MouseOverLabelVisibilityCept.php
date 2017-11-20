<?php
/**
 * Nextcloud - Gallery
 *
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Cedric de Saint Martin <cedric@desaintmartin.fr>
 *
 * @copyright Olivier Paroz 2017
 */

use \Page\Acceptance\Login;
use \Page\Gallery as GalleryPage;

$I = new AcceptanceTester($scenario);
$I->am('a standard user');
$I->wantTo('See image labels when mouse over');

$album1RowElement = '.row-element:nth-of-type(1)';
$file1RowElement = '.row-element:nth-of-type(2)';
$file1Label = $file1RowElement . '>.image-label>label';
$file2RowElement = '.row-element:nth-of-type(3)';
$file2Label = $file2RowElement . '>.image-label>label';

$credentials = $I->getUserCredentials();
$loginPage = new Login($I);
$loginPage->login($credentials[0], $credentials[1]);
$loginPage->confirmLogin();

// We load page with cursor at location of one image
$I->moveMouseOver(null, 300, 200);

$I->amOnPage('/index.php/apps/gallery/#folder1');
$I->waitForElement(['css' => 'a[href="#folder1%2Ftestimage.jpg"]']);
$I->waitForElementNotVisible('.icon-loading');
$I->wait(1);

$I->dontSeeElement($file2Label);

// Move mouse somewhere else
$I->moveMouseOver('body', 0, 0); // Top left
$I->waitForElementNotVisible($file1Label);
$I->dontSeeElement($file2Label);

// Move back to first element
$I->moveMouseOver($file1RowElement);
$I->waitForElementVisible($file1Label);
$I->waitForElementNotVisible($file2Label);

// Move to second element
$I->moveMouseOver($file2RowElement);
$I->waitForElementNotVisible($file1Label);
$I->waitForElementVisible($file2Label);

$I->moveMouseOver('body', 0, 0); // Top left

