<?php
/**
 * Nextcloud - Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 * @author Robin Appelman <robin@icewind.nl>
 *
 * @copyright Olivier Paroz 2017
 * @copyright Robin Appelman 2017
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
Util::addTranslations($appName);

$loadScripts = function() use ($appName) {
	// @codeCoverageIgnoreStart
	/**
	 * Scripts for the Files app
	 */
	Util::addScript($appName, 'scripts-for-file-app');

	/**
	 * Styles for the Files app
	 */
	Util::addStyle($appName, 'slideshow');
	Util::addStyle($appName, 'gallerybutton');
	Util::addStyle($appName, 'share');
};// @codeCoverageIgnoreEnd

\OC::$server->getEventDispatcher()->addListener('OCA\Files::loadAdditionalScripts', $loadScripts);
\OC::$server->getEventDispatcher()->addListener('OCA\Files_Sharing::loadAdditionalScripts', $loadScripts);
