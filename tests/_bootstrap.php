<?php

use Codeception\Util\Autoload;

define('PHPUNIT_RUN', 1);

require_once __DIR__ . '/../../../lib/base.php';
Autoload::addNamespace('Test', '/../../../tests/lib');


// load minimum set of apps
OC_App::loadApps(array('authentication'));
OC_App::loadApps(array('filesystem', 'logging'));
OC_Hook::clear();
