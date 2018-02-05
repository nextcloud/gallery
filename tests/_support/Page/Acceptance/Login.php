<?php
/**
 * Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2016
 */

namespace Page\Acceptance;

use Page\Login as LoginPage;
use Page\Files as FilesPage;

class Login {
	// include url of current page
	public static $URL = '';

	/**
	 * Declare UI map for this page here. CSS or XPath allowed.
	 * public static $usernameField = '#username';
	 * public static $formSubmitButton = "#mainForm input[type=submit]";
	 */

	/**
	 * Basic route example for your current URL
	 * You can append any additional parameter to URL
	 * and use it in tests like: Page\Edit::route('/123-post');
	 */
	public static function route($param) {
		return static::$URL . $param;
	}

	/**
	 * @var \AcceptanceTester;
	 */
	protected $acceptanceTester;

	public function __construct(\AcceptanceTester $I) {
		$this->acceptanceTester = $I;
	}

	public function login($name, $password) {
		$I = $this->acceptanceTester;

		$I->amOnPage(LoginPage::$URL);
		$I->fillField(LoginPage::$usernameField, $name);
		$I->fillField(LoginPage::$passwordField, $password);
		$I->click(LoginPage::$loginButton);

		return $this;
	}

	public function confirmLogin() {
		$I = $this->acceptanceTester;

		// 30 is the default timeout used in the WebDriver extension for
		// requests, so it is used here too for consistency
		$I->waitForElement(['css' => FilesPage::$contentDiv], 30);
		$I->seeInCurrentUrl(FilesPage::$URL);
	}
}
