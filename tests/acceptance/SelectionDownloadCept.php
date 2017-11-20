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
$I->wantTo('Download a selection of photos');

$credentials = $I->getUserCredentials();
$loginPage = new Login($I);
$loginPage->login($credentials[0], $credentials[1]);
$loginPage->confirmLogin();

$I->amOnPage('/index.php/apps/gallery/#folder1');
$I->waitForElement('a[href="#folder1%2Ftestimage.jpg"]');
$I->waitForElementNotVisible('.icon-loading');

$albumName = 'folder1';
$file1Order = 2;
$file1RowElement = '.row-element:nth-of-type(' . $file1Order . ')';
$file1Label = $file1RowElement . '>.image-label>label';
$file1Name = $I->executeJs('return $("' . $file1RowElement . '>.image-label>span").html();');
$file2Order = 3;
$file2RowElement = '.row-element:nth-of-type(' . $file2Order . ')';
$file2Label = $file2RowElement . '>.image-label>label';
$file2Name = $I->executeJs('return $("' . $file2RowElement . '>.image-label>span").html();');

$I->dontSeeElement('.icon-download');

// Select one file, download it
$I->dontSeeElement($file1Label);
$I->moveMouseOver($file1RowElement);
$I->waitForElementVisible($file1Label);
$I->wait(1); // Wait for animation to end
$I->click($file1Label);
$I->waitForElementVisible('.icon-download');
$I->click('.icon-download');
$I->waitForElementNotVisible('.icon-loading-small');
$I->checkSelectionDownloadUrlMatches($albumName, [$file1Name]);

// Move mouse somewhere else, still see the label
$I->moveMouseOver('body', 0, 0); // Top left
$I->seeElement($file1Label);

// Select two files, download them
// Note: first file is already selected
$I->dontSeeElement($file2Label);
$I->moveMouseOver($file2RowElement);
$I->waitForElementVisible($file2Label);
$I->wait(1); // Wait for animation to end
$I->click($file2Label);
$I->waitForElementVisible('.icon-download');
$I->click('.icon-download');
$I->waitForElementNotVisible('.icon-loading-small');
$I->checkSelectionDownloadUrlMatches($albumName, [$file2Name, $file1Name]);

// Unselect one, download the other
$I->moveMouseOver($file1RowElement);
$I->waitForElementVisible($file1Label);
$I->wait(1); // Wait for animation to end
$I->click($file1Label);
$I->moveMouseOver('body', 0, 0); // Top left
$I->waitForElementNotVisible($file1Label);
$I->click('.icon-download');
$I->waitForElementNotVisible('.icon-loading-small');
$I->checkSelectionDownloadUrlMatches($albumName, [$file2Name]);

// Change album, select one, download
$I->click('.row-element:nth-of-type(1)>span');
$I->waitForElementNotVisible("a[href='#folder1%2Ftestimage-wide.png']", 10); // An image in folder1
$I->waitForElementNotVisible('.loading');
$I->seeElement(['xpath' => '//span[text()="shared1"]']); // See shared1 in breadcrumb
$I->waitForElementVisible("a[href='#folder1%2Fshared1%2Ftestimage.eps']"); // An image in folder1/shared1

$sharedAlbumName = 'folder1/shared1';
$sharedFile1Order = 2;
$sharedFile1RowElement = '.row-element:nth-of-type(' . $sharedFile1Order . ')';
$sharedFile1Label = $sharedFile1RowElement . '>.image-label>label';
$sharedFile1Name = $I->executeJs('return $("' . $sharedFile1RowElement . '>.image-label>span").html();');

$I->dontSeeElement($sharedFile1Label);
$I->moveMouseOver($file1RowElement);
$I->waitForElementVisible($sharedFile1Label);
$I->wait(1); // Wait for animation to end
$I->click($sharedFile1Label);
$I->waitForElementVisible('.icon-download');
$I->click('.icon-download');
$I->waitForElementNotVisible('.icon-loading-small');
$I->checkSelectionDownloadUrlMatches($sharedAlbumName, [$sharedFile1Name]);

// Come back in original album through breadcrumb, select one, download
$I->moveMouseOver('body', 0, 0); // Top left
$I->click(['link' => $albumName]);
$I->waitForElementNotVisible('.loading');
$I->wait(1); // Just wait a bit so that everything is initialized
$I->moveMouseOver($file1RowElement);
$I->waitForElementVisible($file1Label);
$I->wait(1); // Wait for animation to end
$I->click($file1Label);
$I->waitForElementVisible('.icon-download');
$I->click('.icon-download');
$I->waitForElementNotVisible('.icon-loading-small');
$I->checkSelectionDownloadUrlMatches($albumName, [$file1Name]);

// Unselect all, assert download button has disappeared
$I->click($file1Label);
$I->waitForElementNotVisible('.icon-download');

$I->moveMouseOver('body', 0, 0); // Top left

