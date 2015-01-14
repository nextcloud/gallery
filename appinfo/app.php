<?php
/**
 * ownCloud - galleryplus application
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Robin Appelman <icewind@owncloud.com>
 *
 * @copyright Olivier Paroz 2014-2015
 * @copyright Robin Appelman 2014-2015
 */

namespace OCA\GalleryPlus\AppInfo;

use OCP\Util;

$app = new Application();
$c = $app->getContainer();
$appName = $c->query('AppName');

/**
 * Menu entry in ownCloud
 */
$navConfig = [
	'id'    => $appName,

	// Sorting weight for the navigation. The higher the number, the higher
	// will it be listed in the navigation
	'order' => 3,

	// The route that will be shown on startup when called from within ownCloud
	// Public links are using another route, see appinfo/routes.php
	'href'  => $c->query('URLGenerator')
				 ->linkToRoute($appName . '.page.index'),

	// The icon that will be shown in the navigation
	// This file needs to exist in img/
	'icon'  => $c->query('URLGenerator')
				 ->imagePath($appName, 'picture.svg'),

	// The title of the application. This will be used in the
	// navigation or on the settings page
	'name'  => $c->query('L10N')
				 ->t('Gallery+')
];
$c->query('ServerContainer')
  ->getNavigationManager()
  ->add($navConfig);

/**
 * Loading translations
 */
Util::addTranslations('galleryplus');

/**
 * Scripts for the Files app
 */
$c->query('API')
  ->addScript('vendor/bigshot/bigshot', $appName);
$c->query('API')
  ->addScript('slideshow', $appName);
$c->query('API')
  ->addScript('public', $appName);

/**
 * Styles for the Files app
 */
$c->query('API')
  ->addStyle('slideshow', $appName);