<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\Gallery\AppInfo;

require_once __DIR__ . '/../vendor/autoload.php';

// A production environment will not have xdebug enabled and
// a development environment should have the dev packages installed
if (extension_loaded('xdebug')) {
	include_once __DIR__ . '/../c3.php';
}

use OCP\IContainer;

use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;

use OCA\Gallery\Controller\PageController;
use OCA\Gallery\Controller\ConfigController;
use OCA\Gallery\Controller\FilesController;
use OCA\Gallery\Controller\FilesPublicController;
use OCA\Gallery\Controller\FilesApiController;
use OCA\Gallery\Controller\PreviewController;
use OCA\Gallery\Controller\PublicConfigController;

use OCA\Gallery\Controller\PublicPreviewController;
use OCA\Gallery\Environment\Environment;
use OCA\Gallery\Preview\Preview;
use OCA\Gallery\Service\SearchFolderService;
use OCA\Gallery\Service\ConfigService;
use OCA\Gallery\Service\ConfigParser;
use OCA\Gallery\Service\SearchMediaService;
use OCA\Gallery\Service\ThumbnailService;
use OCA\Gallery\Service\PreviewService;
use OCA\Gallery\Service\DownloadService;
use OCA\Gallery\Middleware\SharingCheckMiddleware;
use OCA\Gallery\Middleware\EnvCheckMiddleware;

use OCA\OcUtility\AppInfo\Application as OcUtility;
use OCA\OcUtility\Service\SmarterLogger as SmarterLogger;

/**
 * Class Application
 *
 * @package OCA\Gallery\AppInfo
 */
class Application extends App {

	/**
	 * Constructor
	 *
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = []) {
		parent::__construct('gallery', $urlParams);

		$container = $this->getContainer();

		/**
		 * Controllers
		 */
		$container->registerService(
			'PageController', function (IContainer $c) {
			return new PageController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('Environment'),
				$c->query('OCP\IURLGenerator'),
				$c->query('DownloadService'),
				$c->query('OCP\IConfig')
			);
		}
		);
		$container->registerService(
			'ConfigController', function (IContainer $c) {
			return new ConfigController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('ConfigService'),
				$c->query('PreviewService'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'PublicConfigController', function (IContainer $c) {
			return new PublicConfigController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('ConfigService'),
				$c->query('PreviewService'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'FilesController', function (IContainer $c) {
			return new FilesController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('SearchFolderService'),
				$c->query('ConfigService'),
				$c->query('SearchMediaService'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'FilesPublicController', function (IContainer $c) {
			return new FilesPublicController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('SearchFolderService'),
				$c->query('ConfigService'),
				$c->query('SearchMediaService'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'FilesApiController', function (IContainer $c) {
			return new FilesApiController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('SearchFolderService'),
				$c->query('ConfigService'),
				$c->query('SearchMediaService'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'PreviewController', function (IContainer $c) {
			return new PreviewController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('OCP\IURLGenerator'),
				$c->query('ThumbnailService'),
				$c->query('PreviewService'),
				$c->query('DownloadService'),
				$c->query('OCP\IEventSource'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'PublicPreviewController', function (IContainer $c) {
			return new PublicPreviewController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('OCP\IURLGenerator'),
				$c->query('ThumbnailService'),
				$c->query('PreviewService'),
				$c->query('DownloadService'),
				$c->query('OCP\IEventSource'),
				$c->query('Logger')
			);
		}
		);

		/**
		 * Core
		 */
		$container->registerService(
			'OCP\IServerContainer', function (IAppContainer $c) {
			return $c->getServer();
		}
		);
		$container->registerService(
			'OCP\IEventSource', function (IAppContainer $c) {
			return $c->getServer()
					 ->createEventSource();
		}
		);
		$container->registerService(
			'Token', function (IContainer $c) {
			return $c->query('Request')
					 ->getParam('token');
		}
		);
		$container->registerService(
			'Session', function (IAppContainer $c) {
			return $c->getServer()
					 ->getSession();
		}
		);
		$container->registerService(
			'L10N', function (IAppContainer $c) {
			return $c->getServer()
					 ->getL10N('gallery'); // Keep the same translations
		}
		);
		$container->registerService(
			'UserFolder', function (IAppContainer $c) {
			return $c->getServer()
					 ->getUserFolder($c->query('UserId'));
		}
		);

		/**
		 * OCA
		 */
		$container->registerService(
			'ConfigParser', function () {
			return new ConfigParser();
		}
		);
		$container->registerService(
			'CustomPreviewManager', function (IContainer $c) {
			return new Preview(
				$c->query('OCP\IConfig'),
				$c->query('OCP\IPreview'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'Environment', function (IContainer $c) {
			return new Environment(
				$c->query('AppName'),
				$c->query('UserId'),
				$c->query('UserFolder'),
				$c->query('OCP\IUserManager'),
				$c->query('OCP\IServerContainer'),
				$c->query('Logger')
			);
		}
		);
		/*// The same thing as above, but in OC9, hopefully. See https://github.com/owncloud/core/issues/12676
		$container->registerService(
			'Environment', function (IAppContainer $c) {
			$token = $c->query('Token');

			return $c
				->getServer()
				->getEnvironment($token);
		}
		);*/
		/**
		 * OCA External
		 */
		if (\OCP\App::isEnabled('ocutility')) {
			$container->registerService(
				'UtilityContainer', function () {
				$app = new OcUtility();

				return $app->getContainer();
			}
			);
			$container->registerService(
				'Helper', function (IContainer $c) {
				return $c->query('UtilityContainer')
						 ->query('OCA\OcUtility\Service\Helper');
			}
			);
			$container->registerService(
				'Logger', function (IContainer $c) {
				return new SmarterLogger(
					$c->query('AppName'),
					$c->query('OCP\ILogger')
				);
			}
			);
		} else {
			$container->registerService(
				'Logger', function (IContainer $c) {
				return $c->query('OCP\ILogger');
			}
			);
		}
		/**
		 * Services
		 */
		$container->registerService(
			'SearchFolderService', function (IContainer $c) {
			return new SearchFolderService(
				$c->query('AppName'),
				$c->query('Environment'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'ConfigService', function (IContainer $c) {
			return new ConfigService(
				$c->query('AppName'),
				$c->query('Environment'),
				$c->query('ConfigParser'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'SearchMediaService', function (IContainer $c) {
			return new SearchMediaService(
				$c->query('AppName'),
				$c->query('Environment'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'ThumbnailService', function () {
			return new ThumbnailService();
		}
		);
		$container->registerService(
			'PreviewService', function (IContainer $c) {
			return new PreviewService(
				$c->query('AppName'),
				$c->query('Environment'),
				$c->query('CustomPreviewManager'),
				$c->query('Logger')
			);
		}
		);
		$container->registerService(
			'DownloadService', function (IContainer $c) {
			return new DownloadService(
				$c->query('AppName'),
				$c->query('Environment'),
				$c->query('Logger')
			);
		}
		);

		/**
		 * Middleware
		 */
		$container->registerService(
			'SharingCheckMiddleware',
			function (IAppContainer $c) {
				return new SharingCheckMiddleware(
					$c->query('AppName'),
					$c->query('Request'),
					$c->query('OCP\IConfig'),
					$c->query('OCP\AppFramework\Utility\IControllerMethodReflector'),
					$c->query('OCP\IURLGenerator'),
					$c->query('Logger')
				);
			}
		);
		$container->registerService(
			'EnvCheckMiddleware',
			function (IContainer $c) {
				return new EnvCheckMiddleware(
					$c->query('AppName'),
					$c->query('Request'),
					$c->query('OCP\Security\IHasher'),
					$c->query('Session'),
					$c->query('Environment'),
					$c->query('OCP\AppFramework\Utility\IControllerMethodReflector'),
					$c->query('OCP\IURLGenerator'),
					$c->query('Logger')
				);
			}
		);

		// Executed in the order that it is registered
		$container->registerMiddleware('SharingCheckMiddleware');
		$container->registerMiddleware('EnvCheckMiddleware');
	}

}
