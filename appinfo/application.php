<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\AppInfo;

require_once __DIR__ . '/../vendor/autoload.php';

use OCP\IContainer;

use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;

use OCA\GalleryPlus\Controller\PageController;
use OCA\GalleryPlus\Controller\FilesController;
use OCA\GalleryPlus\Controller\PreviewController;
use OCA\GalleryPlus\Controller\PublicFilesController;
use OCA\GalleryPlus\Controller\PublicPreviewController;
use OCA\GalleryPlus\Environment\Environment;
use OCA\GalleryPlus\Preview\Preview;
use OCA\GalleryPlus\Service\FilesService;
use OCA\GalleryPlus\Service\ConfigService;
use OCA\GalleryPlus\Service\SearchMediaService;
use OCA\GalleryPlus\Service\ThumbnailService;
use OCA\GalleryPlus\Service\PreviewService;
use OCA\GalleryPlus\Service\DownloadService;
use OCA\GalleryPlus\Middleware\SharingCheckMiddleware;
use OCA\GalleryPlus\Middleware\EnvCheckMiddleware;
use OCA\GalleryPlus\Utility\SmarterLogger;
use OCA\GalleryPlus\Utility\Normalizer;

/**
 * Class Application
 *
 * @package OCA\GalleryPlus\AppInfo
 */
class Application extends App {

	/**
	 * Constructor
	 *
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = []) {
		parent::__construct('galleryplus', $urlParams);

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
				$c->query('OCP\IURLGenerator')
			);
		}
		);
		$container->registerService(
			'FilesController', function (IContainer $c) {
			return new FilesController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('FilesService'),
				$c->query('ConfigService'),
				$c->query('SearchMediaService'),
				$c->query('SmarterLogger')
			);
		}
		);
		$container->registerService(
			'PublicFilesController', function (IContainer $c) {
			return new PublicFilesController(
				$c->query('AppName'),
				$c->query('Request'),
				$c->query('FilesService'),
				$c->query('ConfigService'),
				$c->query('SearchMediaService'),
				$c->query('SmarterLogger')
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
				$c->query('SmarterLogger')
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
				$c->query('SmarterLogger')
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
			'Normalizer', function () {
			return new Normalizer();
		}
		);
		$container->registerService(
			'SmarterLogger', function (IContainer $c) {
			return new SmarterLogger(
				$c->query('AppName'),
				$c->query('OCP\ILogger'),
				$c->query('Normalizer')
			);
		}
		);
		$container->registerService(
			'CustomPreviewManager', function (IContainer $c) {
			return new Preview(
				$c->query('OCP\IConfig'),
				$c->query('OCP\IPreview'),
				$c->query('SmarterLogger')
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
				$c->query('SmarterLogger')
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
		 * Services
		 */
		$container->registerService(
			'FilesService', function (IContainer $c) {
			return new FilesService(
				$c->query('AppName'),
				$c->query('Environment'),
				$c->query('SmarterLogger')

			);
		}
		);
		$container->registerService(
			'ConfigService', function (IContainer $c) {
			return new ConfigService(
				$c->query('AppName'),
				$c->query('Environment'),
				$c->query('SmarterLogger')

			);
		}
		);
		$container->registerService(
			'SearchMediaService', function (IContainer $c) {
			return new SearchMediaService(
				$c->query('AppName'),
				$c->query('Environment'),
				$c->query('SmarterLogger')

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
				$c->query('SmarterLogger')

			);
		}
		);
		$container->registerService(
			'DownloadService', function (IContainer $c) {
			return new DownloadService(
				$c->query('AppName'),
				$c->query('Environment'),
				$c->query('SmarterLogger')
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
					$c->query('SmarterLogger')
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
					$c->query('SmarterLogger')
				);
			}
		);

		// Executed in the order that it is registered
		$container->registerMiddleware('SharingCheckMiddleware');
		$container->registerMiddleware('EnvCheckMiddleware');

	}

}
