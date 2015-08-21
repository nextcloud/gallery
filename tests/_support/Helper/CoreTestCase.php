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

namespace Helper;

/**
 * Class CoreTestCase
 *
 * This class is created in order to avoid having to stay in sync with the content of these methods
 * in core
 *
 * @package OCA\Gallery\Tests\Helper
 */
class CoreTestCase extends \Test\TestCase {

	/**
	 * PHPUnit setUp
	 */
	public function setUp() {
		parent::setUp();
	}

	/**
	 * PHPUnit tearDown
	 */
	public function tearDown() {
		parent::logout();

		parent::tearDown();
	}

	/**
	 * Creates a new session for the user
	 *
	 * @param string $user
	 */
	public static function loginAsUser($user = '') {
		parent::loginAsUser($user);
	}

	/**
	 * Logs the user out
	 */
	public function logoutUser() {
		parent::logout();
	}

	/**
	 * Allows us to test private methods/properties
	 *
	 * @param $object
	 * @param $methodName
	 * @param array $parameters
	 *
	 * @return mixed
	 */
	public static function invokePrivate($object, $methodName, array $parameters = []) {
		return parent::invokePrivate($object, $methodName, $parameters);
	}

}
