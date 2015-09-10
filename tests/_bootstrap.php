<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

use Codeception\Util\Autoload;

define('PHPUNIT_RUN', 1);

require_once __DIR__ . '/../../../lib/base.php';
Autoload::addNamespace('Test', '/../../../tests/lib');


// Load minimum set of apps
OC_App::loadApps(['authentication']);
OC_App::loadApps(['filesystem', 'logging']);

// Load this app
OC_App::loadApp('galleryplus');

OC_Hook::clear();
