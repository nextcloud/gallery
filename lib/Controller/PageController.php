<?php
/**
 * Nextcloud - Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Robin Appelman <robin@icewind.nl>
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Robin Appelman 2017
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Controller;

use OCP\AppFramework\Http\Template\ExternalShareMenuAction;
use OCP\AppFramework\Http\Template\LinkMenuAction;
use OCP\AppFramework\Http\Template\PublicTemplateResponse;
use OCP\AppFramework\Http\Template\SimpleMenuAction;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IRequest;
use OCP\IConfig;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\Gallery\Environment\Environment;
use OCA\Gallery\Http\ImageResponse;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Generates templates for the landing page from within ownCloud, the public
 * gallery and error pages
 *
 * @package OCA\Gallery\Controller
 */
class PageController extends Controller {

	/** @var Environment */
	private $environment;
	/** @var IURLGenerator */
	private $urlGenerator;
	/** @var IConfig */
	private $appConfig;
	/** @var EventDispatcherInterface */
	private $dispatcher;
	/** @var IL10N */
	private $l10n;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param Environment $environment
	 * @param IURLGenerator $urlGenerator
	 * @param IConfig $appConfig
	 * @param EventDispatcherInterface $dispatcher
	 * @param IL10N $l10n
	 */
	public function __construct(
		$appName,
		IRequest $request,
		Environment $environment,
		IURLGenerator $urlGenerator,
		IConfig $appConfig,
		EventDispatcherInterface $dispatcher,
		IL10N $l10n
	) {
		parent::__construct($appName, $request);

		$this->environment = $environment;
		$this->urlGenerator = $urlGenerator;
		$this->appConfig = $appConfig;
		$this->dispatcher = $dispatcher;
		$this->l10n = $l10n;
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

		// Parameters sent to the template
		$params = $this->getIndexParameters($appName);

		$this->dispatcher->dispatch('OCP\Share::loadSocial');

		// Will render the page using the template found in templates/index.php
		$response = new TemplateResponse($appName, 'index', $params);
		$this->addContentSecurityToResponse($response);

		return $response;
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * Shows the albums and pictures or redirects to the download location the token gives access to
	 *
	 * @param string $token
	 * @param null|string $filename
	 *
	 * @return PublicTemplateResponse|ImageResponse|RedirectResponse
	 */
	public function publicIndex($token, $filename) {
		$node = $this->environment->getSharedNode();
		if ($node->getType() === 'dir') {
			return $this->showPublicPage($token);
		} else {
			$url = $this->urlGenerator->linkToRoute(
				$this->appName . '.files_public.download',
				[
					'token'    => $token,
					'fileId'   => $node->getId(),
					'filename' => $filename
				]
			);

			return new RedirectResponse($url);
		}
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 * @Guest
	 *
	 * Generates an error page based on the error code
	 *
	 * @param int $code
	 *
	 * @return TemplateResponse
	 */
	public function errorPage($code) {
		$appName = $this->appName;
		$message = $this->request->getCookie('galleryErrorMessage');
		$params = [
			'appName' => $appName,
			'message' => $message,
			'code'    => $code,
		];

		$errorTemplate = new TemplateResponse($appName, 'index', $params, 'guest');
		$errorTemplate->setStatus($code);
		$errorTemplate->invalidateCookie('galleryErrorMessage');

		return $errorTemplate;
	}

	/**
	 * Adds the domain "data:" to the allowed image domains
	 * this function is called by reference
	 *
	 * @param TemplateResponse $response
	 */
	private function addContentSecurityToResponse($response) {
		$csp = new Http\ContentSecurityPolicy();
		$csp->addAllowedFontDomain("data:");
		$response->setContentSecurityPolicy($csp);
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
	 * Returns the parameters to be used in the index function
	 *
	 * @param $appName
	 *
	 * @return array<string,string>
	 */
	private function getIndexParameters($appName) {

		// Parameters sent to the index function
		$params = [
			'appName' => $appName,
			'uploadUrl' => $this->urlGenerator->linkTo(
				'files', 'ajax/upload.php'
			),
			'publicUploadEnabled' => $this->appConfig->getAppValue(
				'core', 'shareapi_allow_public_upload', 'yes'
			),
			'mailNotificationEnabled' => $this->appConfig->getAppValue(
				'core', 'shareapi_allow_mail_notification', 'no'
			),
			'mailPublicNotificationEnabled' => $this->appConfig->getAppValue(
				'core', 'shareapi_allow_public_notification', 'no'
			)
		];

		return $params;
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
		$downloadUrl = $this->urlGenerator->linkToRouteAbsolute('files_sharing.sharecontroller.downloadShare', ['token' => $token]);

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
		$response = new PublicTemplateResponse($this->appName, 'public', $params);
		$response->setHeaderTitle($params['albumName']);
		$response->setHeaderDetails($this->l10n->t('shared by %s', [$params['displayName']]));
		$response->setHeaderActions([
			new SimpleMenuAction('download', $this->l10n->t('Download'), 'icon-download-white', $downloadUrl, 0),
			new SimpleMenuAction('download', $this->l10n->t('Download'), 'icon-download', $downloadUrl, 10),
			new LinkMenuAction($this->l10n->t('Direct link'), 'icon-public', $downloadUrl),
			new ExternalShareMenuAction($this->l10n->t('Add to your Nextcloud'), 'icon-external', $this->environment->getUserId(), $params['displayName'], $params['albumName'])
		]);
		$this->addContentSecurityToResponse($response);

		return $response;
	}

	/**
	 * Determines if we can add external shared to this instance
	 *
	 * @return array<bool,string>
	 */
	private function getServer2ServerProperties() {
		$server2ServerSharing = $this->appConfig->getAppValue(
			'files_sharing', 'outgoing_server2server_share_enabled', 'yes'
		);
		$server2ServerSharing = ($server2ServerSharing === 'yes') ? true : false;
		$password = $this->environment->getSharePassword();
		$passwordProtected = ($password) ? 'true' : 'false';

		return [$server2ServerSharing, $passwordProtected];
	}
}
