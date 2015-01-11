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

use OCP\AppFramework\IApi;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;

use OCA\GalleryPlus\Service\EnvironmentService;

/**
 * Generates templates for the landing page from within ownCloud, the public
 * gallery and error pages
 *
 * @package OCA\GalleryPlus\Controller
 */
class PageController extends Controller {

	/**
	 * @type EnvironmentService
	 */
	private $environmentService;
	/**
	 * @type IURLGenerator
	 */
	private $urlGenerator;
	/**
	 * @type IApi
	 */
	private $api;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param EnvironmentService $environmentService
	 * @param IURLGenerator $urlGenerator
	 * @param IApi $api
	 */
	public function __construct(
		$appName,
		IRequest $request,
		EnvironmentService $environmentService,
		IURLGenerator $urlGenerator,
		IApi $api
	) {
		parent::__construct($appName, $request);

		$this->environmentService = $environmentService;
		$this->urlGenerator = $urlGenerator;
		$this->api = $api;
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
		$params = array(
			'appName' => $appName
		);

		// Will render the page using the template found in templates/index.php
		return new TemplateResponse($appName, 'index', $params);
	}

	/**
	 * @PublicPage
	 * @NoCSRFRequired
	 *
	 * Shows the albums and pictures the token gives access to
	 *
	 * @return TemplateResponse
	 */
	public function publicIndex() {
		$appName = $this->appName;
		$token = $this->request->getParam('token');

		$env = $this->environmentService->getEnv();
		$displayName = $env['originalOwnerDisplayName'];
		$albumName = $env['albumName'];

		// Parameters sent to the template
		$params = array(
			'appName'     => $appName,
			'token'       => $token,
			'displayName' => $displayName,
			'albumName'   => $albumName
		);

		// Will render the page using the template found in templates/public.php
		return new TemplateResponse($appName, 'public', $params, 'public');
	}

	/**
	 * @PublicPage
	 *
	 * Redirects to the public gallery after authentication
	 *
	 * At this stage te CSRF token must have been defined
	 *
	 * @return TemplateResponse
	 */
	public function publicIndexPost() {
		return $this->publicIndex();
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
		$params = array(
			'appName' => $appName,
			'message' => $message,
			'code'    => $code,
		);

		$errorTemplate = new TemplateResponse($appName, 'index', $params, 'guest');
		$errorTemplate->setStatus($code);

		return $errorTemplate;
	}

	/**
	 * @PublicPage
	 * @Guest
	 *
	 * Generates an error page based on the error code for POST requests
	 *
	 * @param string $message
	 * @param int $code
	 *
	 * @return TemplateResponse
	 */
	public function errorPagePost($message, $code) {
		return $this->errorPage($message, $code);
	}
}