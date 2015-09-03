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

use Codeception\Util\Autoload;

define('PHPUNIT_RUN', 1);

require_once __DIR__ . '/../../../lib/base.php';
OC::$loader->addValidRoot(OC::$SERVERROOT . '/tests');
Autoload::addNamespace('Test', '/../../../tests/lib');


// load minimum set of apps
OC_App::loadApps(array('authentication'));
OC_App::loadApps(array('filesystem', 'logging'));
OC_Hook::clear();
