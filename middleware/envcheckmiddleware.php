<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Authors of \OCA\Files_Sharing\Helper
 *
 * @copyright Olivier Paroz 2014-2015
 * @copyright Bernhard Posselt 2012-2015
 * @copyright Authors of \OCA\Files_Sharing\Helper 2014-2015
 */

namespace OCA\Gallery\Middleware;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ISession;
use OCP\ILogger;
use OCP\Share;
use OCP\Security\IHasher;

use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\IControllerMethodReflector;

use OCA\Gallery\Environment\Environment;

/**
 * Checks that we have a valid token linked to a valid resource and that the
 * user is authorised to access it
 *
 * Once all checks have been passed, the environment is ready to use
 *
 * @package OCA\Gallery\Middleware
 */
class EnvCheckMiddleware extends CheckMiddleware {

	/** @var IHasher */
	private $hasher;
	/** @var ISession */
	private $session;
	/** @var Environment */
	private $environment;
	/** @var IControllerMethodReflector */
	protected $reflector;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param IHasher $hasher
	 * @param ISession $session
	 * @param Environment $environment
	 * @param IControllerMethodReflector $reflector
	 * @param IURLGenerator $urlGenerator
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IHasher $hasher,
		ISession $session,
		Environment $environment,
		IControllerMethodReflector $reflector,
		IURLGenerator $urlGenerator,
		ILogger $logger
	) {
		parent::__construct(
			$appName,
			$request,
			$urlGenerator,
			$logger
		);

		$this->hasher = $hasher;
		$this->session = $session;
		$this->environment = $environment;
		$this->reflector = $reflector;
	}

	/**
	 * Checks that we have a valid token linked to a valid resource and that the
	 * user is authorised to access it
	 *
	 * Inspects the controller method annotations and if PublicPage is found
	 * it checks that we have a token and an optional password giving access to a valid resource.
	 * Once that's done, the environment is setup so that our services can find the resources they
	 * need.
	 *
	 * The checks are not performed on "guest" pages and the environment is not setup. Typical
	 * guest pages are anonymous error ages
	 *
	 * @inheritDoc
	 */
	public function beforeController($controller, $methodName) {
		if ($this->reflector->hasAnnotation('Guest')) {
			return;
		}
		$isPublicPage = $this->reflector->hasAnnotation('PublicPage');
		if ($isPublicPage) {
			$this->validateAndSetTokenBasedEnv();
		} else {
			$this->environment->setStandardEnv();
		}
	}

	/**
	 * Checks that we have a token and an optional password giving access to a
	 * valid resource. Sets the token based environment after that
	 *
	 * @throws CheckException
	 */
	private function validateAndSetTokenBasedEnv() {
		$token = $this->request->getParam('token');
		if (!$token) {
			throw new CheckException(
				"Can't access a public resource without a token", Http::STATUS_NOT_FOUND
			);
		} else {
			$linkItem = $this->getLinkItem($token);
			$password = $this->request->getParam('password');
			// Let's see if the user needs to provide a password
			$this->checkAuthorisation($linkItem, $password);

			$this->environment->setTokenBasedEnv($linkItem);
		}
	}

	/**
	 * Validates a token to make sure its linked to a valid resource
	 *
	 * Logic mostly duplicated from @see \OCA\Files_Sharing\Helper
	 *
	 * @fixme setIncognitoMode in 8.1 https://github.com/owncloud/core/pull/12912
	 *
	 * @param string $token
	 *
	 * @return array
	 *
	 * @throws CheckException
	 */
	private function getLinkItem($token) {
		// Allows a logged in user to access public links
		\OC_User::setIncognitoMode(true);

		$linkItem = Share::getShareByToken($token, false);

		$this->checkLinkItemExists($linkItem);
		$this->checkLinkItemIsValid($linkItem, $token);
		$this->checkItemType($linkItem);

		// Checks passed, let's store the linkItem
		return $linkItem;
	}

	/**
	 * Makes sure that the token exists
	 *
	 * @param array|bool $linkItem
	 *
	 * @throws CheckException
	 */
	private function checkLinkItemExists($linkItem) {
		if ($linkItem === false
			|| ($linkItem['item_type'] !== 'file'
				&& $linkItem['item_type'] !== 'folder')
		) {
			$message = 'Passed token parameter is not valid';
			throw new CheckException($message, Http::STATUS_BAD_REQUEST);
		}
	}

	/**
	 * Makes sure that the token contains all the information that we need
	 *
	 * @param array|bool $linkItem
	 * @param string $token
	 *
	 * @throws CheckException
	 */
	private function checkLinkItemIsValid($linkItem, $token) {
		if (!isset($linkItem['uid_owner'])
			|| !isset($linkItem['file_source'])
		) {
			$message =
				'Passed token seems to be valid, but it does not contain all necessary information . ("'
				. $token . '")';
			throw new CheckException($message, Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Makes sure an item type was set for that token
	 *
	 * @param array|bool $linkItem
	 *
	 * @throws CheckException
	 */
	private function checkItemType($linkItem) {
		if (!isset($linkItem['item_type'])) {
			$message = 'No item type set for share id: ' . $linkItem['id'];
			throw new CheckException($message, Http::STATUS_NOT_FOUND);
		}
	}

	/**
	 * Checks if a password is required or if the one supplied is working
	 *
	 * @param array|bool $linkItem
	 * @param string|null $password optional password
	 *
	 * @throws CheckException
	 */
	private function checkAuthorisation($linkItem, $password) {
		$passwordRequired = isset($linkItem['share_with']);

		if ($passwordRequired) {
			if ($password !== null) {
				$this->authenticate($linkItem, $password);
			} else {
				$this->checkSession($linkItem);
			}
		}
	}

	/**
	 * Authenticate link item with the given password
	 * or with the session if no password was given.
	 *
	 * @fixme @LukasReschke says: Migrate old hashes to new hash format
	 * Due to the fact that there is no reasonable functionality to update the password
	 * of an existing share no migration is yet performed there.
	 * The only possibility is to update the existing share which will result in a new
	 * share ID and is a major hack.
	 *
	 * In the future the migration should be performed once there is a proper method
	 * to update the share's password. (for example `$share->updatePassword($password)`
	 *
	 * @link https://github.com/owncloud/core/issues/10671
	 *
	 * @param array|bool $linkItem
	 * @param string $password
	 *
	 * @return bool true if authorized, an exception is raised otherwise
	 *
	 * @throws CheckException
	 */
	private function authenticate($linkItem, $password) {
		if ((int)$linkItem['share_type'] === Share::SHARE_TYPE_LINK) {
			$this->checkPassword($linkItem, $password);
		} else {
			throw new CheckException(
				'Unknown share type ' . $linkItem['share_type'] . ' for share id '
				. $linkItem['id'], Http::STATUS_NOT_FOUND
			);
		}

		return true;
	}

	/**
	 * Validates the given password
	 *
	 * @param array|bool $linkItem
	 * @param string $password
	 *
	 * @throws CheckException
	 */
	private function checkPassword($linkItem, $password) {
		$newHash = '';
		if ($this->hasher->verify($password, $linkItem['share_with'], $newHash)) {
			// Save item id in session for future requests
			$this->session->set('public_link_authenticated', $linkItem['id']);
			// @codeCoverageIgnoreStart
			if (!empty($newHash)) {
				// For future use
			}
			// @codeCoverageIgnoreEnd
		} else {
			throw new CheckException("Wrong password", Http::STATUS_UNAUTHORIZED);
		}
	}

	/**
	 * Makes sure the user is already properly authenticated when a password is required and none
	 * was provided
	 *
	 * @param array|bool $linkItem
	 *
	 * @throws CheckException
	 */
	private function checkSession($linkItem) {
		// Not authenticated ?
		if (!$this->session->exists('public_link_authenticated')
			|| $this->session->get('public_link_authenticated') !== $linkItem['id']
		) {
			throw new CheckException("Missing password", Http::STATUS_UNAUTHORIZED);
		}
	}

}
