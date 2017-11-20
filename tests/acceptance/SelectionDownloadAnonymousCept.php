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
$I->am('an anonymous, non-logged-in user');
$I->wantTo('Download a selection of public photos');

$credentials = $I->getUserCredentials();
$loginPage = new Login($I);
$loginPage->login($credentials[0], $credentials[1]);
$loginPage->confirmLogin();

// Share album and get public URL
$I->amOnPage('/index.php/apps/files?dir=/folder1/');
$I->waitForElementNotVisible('.icon-loading');
$I->executeJs('$(".action-share:eq(0)").click()');
$I->waitForElementNotVisible('.loading');
$I->waitForElementVisible('.linkShareView>label');
$I->click('.linkShareView>label');
$I->waitForElementVisible('.linkText');
$url = $I->executeJs('return new URL($(".linkText").val()).pathname');
// URL constructor is not defined in phantomjs...
$url = $I->executeJs('var url = document.createElement("a");' .
 'url.href = $(".linkText").val(); return url.pathname');

// Log Out
$I->click('#expand');
$I->click('Log out');
$I->waitForText('Forgot password?');

// Go to public URL
$I->amOnPage($url);
$I->waitForElementNotVisible('.icon-loading');
//$I->click('.icon-toggle-pictures');
$I->amOnPage(str_replace('index.php', 'index.php/apps/gallery', $url));
$I->waitForElementNotVisible('.icon-loading');

$albumName = 'folder1/shared1';
$sharedAlbumName = 'folder1/shared1';
$sharedFile1Order = 2;
$sharedFile1RowElement = '.row-element:nth-of-type(' . $sharedFile1Order . ')';
$sharedFile1Label = $sharedFile1RowElement . '>.image-label>label';
$sharedFile1Name = $I->executeJs('return $("' . $sharedFile1RowElement . '>.image-label>span").html();');
$sharedFile2Order = 3;
$sharedFile2RowElement = '.row-element:nth-of-type(' . $sharedFile2Order . ')';
$sharedFile2Label = $sharedFile2RowElement . '>.image-label>label';
$sharedFile2Name = $I->executeJs('return $("' . $sharedFile2RowElement . '>.image-label>span").html();');

// Download a file
$I->waitForElement($sharedFile1RowElement);
$I->dontSeeElement($sharedFile1Label);
$I->moveMouseOver($sharedFile1RowElement);
$I->waitForElementVisible($sharedFile1Label);
$I->wait(1); // Wait for the show animation to end
$I->click($sharedFile1Label);
$I->waitForElementVisible('.button.download');
$I->click('.button.download');
$I->waitForElementNotVisible('.icon-loading-small');
$I->checkSelectionPublicDownloadUrlMatches($sharedAlbumName, [$sharedFile1Name], $url);

// Download both files
$I->dontSeeElement($sharedFile2Label);
$I->moveMouseOver($sharedFile2RowElement);
$I->waitForElementVisible($sharedFile2Label);
$I->wait(1); // Wait for the show animation to end
$I->click($sharedFile2Label);
$I->waitForElementVisible('.button.download');
$I->click('.button.download');
$I->waitForElementNotVisible('.icon-loading-small');
$I->checkSelectionPublicDownloadUrlMatches($albumName, [$sharedFile2Name, $sharedFile1Name], $url);

// Unselect all, assert download button has disappeared
$I->click($sharedFile1Label);
$I->moveMouseOver($sharedFile2RowElement);
$I->wait(1); // Wait for the show animation to end
$I->click($sharedFile2Label);
$I->waitForElementNotVisible('.button.download');

$I->moveMouseOver('body', 0, 0); // Top left

