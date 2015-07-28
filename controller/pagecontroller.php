<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Robin Appelman <icewind@owncloud.com>
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Robin Appelman 2012-2015
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\Controller;

use OCP\IURLGenerator;
use OCP\IRequest;
use OCP\IConfig;
use OCP\Files\File;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\GalleryPlus\Environment\Environment;
use OCA\GalleryPlus\Http\ImageResponse;
use OCA\GalleryPlus\Service\ServiceException;
use OCA\GalleryPlus\Service\DownloadService;

/**
 * Generates templates for the landing page from within ownCloud, the public
 * gallery and error pages
 *
 * @package OCA\GalleryPlus\Controller
 */
class PageController extends Controller {

	/**
	 * @var Environment
	 */
	private $environment;
	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;
	/**
	 * @var DownloadService
	 */
	private $downloadService;
	/**
	 * @var IConfig
	 */
	private $appConfig;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param Environment $environment
	 * @param IURLGenerator $urlGenerator
	 * @param DownloadService $downloadService
	 * @param IConfig $appConfig
	 */
	public function __construct(
		$appName,
		IRequest $request,
		Environment $environment,
		IURLGenerator $urlGenerator,
		DownloadService $downloadService,
		IConfig $appConfig
	) {
		parent::__construct($appName, $request);

		$this->environment = $environment;
		$this->urlGenerator = $urlGenerator;
		$this->downloadService = $downloadService;
		$this->appConfig = $appConfig;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * Shows the albums and pictures at the root folder or a message if
	 * there are no pictures.
	 *
	 * This is the entry page for logged-in users accessing the app from
	 * within ownCloud.
	 * A TemplateResponse response uses a template from the templates folder
	 * and parameters provided here to build the page users will see
	 *
	 * @return TemplateResponse
	 */
	public function index() {
		$appName = $this->appName;
		if (\OCP\App::isEnabled('gallery')) {
			$url = $this->urlGenerator->linkToRoute(
				$appName . '.page.error_page',
				[
					'message' => "You need to disable the Pictures app before being able to use the Gallery app",
					'code'    => Http::STATUS_INTERNAL_SERVER_ERROR
				]
			);

			return new RedirectResponse($url);
		} else {
			// Parameters sent to the template
			$params = ['appName' => $appName];

			// Will render the page using the template found in templates/index.php
			return new TemplateResponse($appName, 'index', $params);
		}
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * Shows the albums and pictures or download the single file the token gives access to
	 *
	 * @param string $token
	 * @param null|string $filename
	 *
	 * @return TemplateResponse|ImageResponse|RedirectResponse
	 */
	public function publicIndex($token, $filename) {
		$node = $this->environment->getSharedNode();
		if ($node->getType() === 'dir') {
			return $this->showPublicPage($token);
		} else {
			return $this->downloadFile($node, $filename);
		}
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 * @Guest
	 *
	 * Generates an error page based on the error code
	 *
	 * @param string $message
	 * @param int $code
	 *
	 * @return TemplateResponse
	 */
	public function errorPage($message, $code) {
		$appName = $this->appName;
		$params = [
			'appName' => $appName,
			'message' => $message,
			'code'    => $code,
		];

		$errorTemplate = new TemplateResponse($appName, 'index', $params, 'guest');
		$errorTemplate->setStatus($code);

		return $errorTemplate;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 * @Guest
	 *
	 * Returns the slideshow template
	 *
	 * @return TemplateResponse
	 */
	public function slideshow() {
		return new TemplateResponse($this->appName, 'slideshow', [], 'blank');
	}

	/**
	 * Shows the albums and pictures the token gives access to
	 *
	 * @param $token
	 *
	 * @return TemplateResponse
	 */
	private function showPublicPage($token) {
		$albumName = $this->environment->getSharedFolderName();
		list($server2ServerSharing, $protected) = $this->getServer2ServerProperties();

		// Parameters sent to the template
		$params = [
			'appName'              => $this->appName,
			'token'                => $token,
			'displayName'          => $this->environment->getDisplayName(),
			'albumName'            => $albumName,
			'server2ServerSharing' => $server2ServerSharing,
			'protected'            => $protected,
			'filename'             => $albumName
		];

		// Will render the page using the template found in templates/public.php
		return new TemplateResponse($this->appName, 'public', $params, 'public');
	}

	/**
	 * Downloads the file associated with a token
	 *
	 * @param File $file
	 * @param string|null $filename
	 *
	 * @return ImageResponse|RedirectResponse
	 */
	private function downloadFile($file, $filename) {
		try {
			$download = $this->downloadService->downloadFile($file);
			if (is_null($filename)) {
				$filename = $file->getName();
			}
			$download['name'] = $filename;

			return new ImageResponse($download);
		} catch (ServiceException $exception) {
			$url = $this->urlGenerator->linkToRoute(
				$this->appName . '.page.error_page',
				[
					'message' => $exception->getMessage(),
					'code'    => Http::STATUS_NOT_FOUND
				]
			);

			return new RedirectResponse($url);
		}
	}

	/**
	 * Determines if we can add external shared to this instance
	 *
	 * @return array
	 */
	private function getServer2ServerProperties() {
		$server2ServerSharing = $this->appConfig->getAppValue(
			'files_sharing', 'outgoing_server2server_share_enabled', 'yes'
		);
		$server2ServerSharing = ($server2ServerSharing === 'yes') ? true : false;
		$protected = $this->environment->isShareProtected();
		$protected = ($protected) ? 'true' : 'false';

		return [$server2ServerSharing, $protected];
	}
}
