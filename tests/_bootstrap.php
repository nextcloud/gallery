<?php
/**
 * Nextcloud - Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

use Codeception\Util\Autoload;

if (!defined('PHPUNIT_RUN')) {
	define('PHPUNIT_RUN', 1);
}

// Add core
require_once __DIR__ . '/../../../lib/base.php';

// Add core tests to the list of valid paths
OC::$loader->addValidRoot(OC::$SERVERROOT . '/tests');

// Give access to core tests to Codeception
Autoload::addNamespace('Test', OC::$SERVERROOT . '/tests/lib');
Autoload::addNamespace('OCA\Gallery\Tests', 'tests/unit');
Autoload::addNamespace('OCA\Gallery\Tests\Integration', 'tests/integration');

// Load all apps
OC_App::loadApps();

OC_Hook::clear();
