<?php
/**
 * Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 * @author Robin Appelman <robin@icewind.nl>
 *
 * @copyright Olivier Paroz 2014-2016
 * @copyright Robin Appelman 2014-2015
 */

namespace OCA\Gallery\AppInfo;

use OCP\Util;

$app = new Application();
$c = $app->getContainer();
$appName = $c->query('AppName');

/**
 * Menu entry
 */
$c->query('OCP\INavigationManager')
  ->add(
	  function () use ($c, $appName) {
		  $urlGenerator = $c->query('OCP\IURLGenerator');
		  $l10n = $c->query('OCP\IL10N');

		  return [
			  'id'    => $appName,

			  // Sorting weight for the navigation. The higher the number, the higher
			  // will it be listed in the navigation
			  'order' => 2,

			  // The route that will be shown on startup when called from within the GUI
			  // Public links are using another route, see appinfo/routes.php
			  'href'  => $urlGenerator->linkToRoute($appName . '.page.index'),

			  // The icon that will be shown in the navigation
			  // This file needs to exist in img/
			  'icon'  => $urlGenerator->imagePath($appName, 'app.svg'),

			  // The title of the application. This will be used in the
			  // navigation or on the settings page
			  'name'  => $l10n->t('Gallery')
		  ];
	  }
  );

/**
 * Loading translations
 *
 * The string has to match the app's folder name
 */
Util::addTranslations('gallery');

$eventDispatcher = \OC::$server->getEventDispatcher();
$eventDispatcher->addListener('OCA\Files::loadAdditionalScripts', function () use ($appName) {
	// @codeCoverageIgnoreStart
	/**
	 * Scripts for the Files app
	 */
	Util::addScript($appName, 'vendor/bigshot/bigshot-compressed');
	Util::addScript($appName, 'vendor/dompurify/src/purify');
	Util::addScript($appName, 'galleryutility');
	Util::addScript($appName, 'galleryfileaction');
	Util::addScript($appName, 'slideshow');
	Util::addScript($appName, 'slideshowcontrols');
	Util::addScript($appName, 'slideshowzoomablepreview');
	Util::addScript($appName, 'gallerybutton');

	/**
	 * Styles for the Files app
	 */
	Util::addStyle($appName, 'slideshow');
	Util::addStyle($appName, 'gallerybutton');
});
