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
 * @copyright Olivier Paroz 2014-2016
 * @copyright Bernhard Posselt 2012-2015
 * @copyright Authors of \OCA\Files_Sharing\Helper 2014-2016
 */

namespace OCA\Gallery\Middleware;

use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\ISession;
use OCP\ILogger;
use OCP\Share;
use OCP\Share\IShare;
use OCP\Share\Exceptions\ShareNotFound;
use OCP\Security\IHasher;

use OCP\AppFramework\Http;
use OCP\AppFramework\Utility\IControllerMethodReflector;

use OCA\Gallery\Environment\Environment;
use OCP\Share\IManager;

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
	/** @var IManager */
	protected $shareManager;

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
	 * @param IManager $shareManager
	 */
	public function __construct(
		$appName,
		IRequest $request,
		IHasher $hasher,
		ISession $session,
		Environment $environment,
		IControllerMethodReflector $reflector,
		IURLGenerator $urlGenerator,
		IManager $shareManager,
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
		$this->shareManager = $shareManager;
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
			$share = $this->getShare($token);
			$password = $this->request->getParam('password');
			// Let's see if the user needs to provide a password
			$this->checkAuthorisation($share, $password);

			$this->environment->setTokenBasedEnv($share);
		}
	}

	/**
	 * Validates a token to make sure its linked to a valid resource
	 *
	 * Uses Share 2.0
	 *
	 * @fixme setIncognitoMode in 8.1 https://github.com/owncloud/core/pull/12912
	 *
	 * @param string $token
	 *
	 * @throws CheckException
	 * @return IShare
	 */
	private function getShare($token) {
		// Allows a logged in user to access public links
		\OC_User::setIncognitoMode(true);

		try {
			$share = $this->shareManager->getShareByToken($token);
		} catch (ShareNotFound $e) {
			throw new CheckException($e->getMessage(), Http::STATUS_NOT_FOUND);
		}

		$this->checkShareIsValid($share, $token);
		$this->checkItemType($share);

		return $share;
	}

	/**
	 * Makes sure that the token contains all the information that we need
	 *
	 * @param IShare $share
	 * @param string $token
	 *
	 * @throws CheckException
	 */
	private function checkShareIsValid($share, $token) {
		if ($share->getShareOwner() === null
			|| $share->getTarget() === null
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
	 * @param IShare $share
	 *
	 * @throws CheckException
	 */
	private function checkItemType($share) {
		if ($share->getNodeType() === null) {
			$message = 'No item type set for share id: ' . $share->getId();
			throw new CheckException($message, Http::STATUS_NOT_FOUND);
		}
	}


	/**
	 * Checks if a password is required or if the one supplied is working
	 *
	 * @param IShare $share
	 * @param string|null $password optional password
	 *
	 * @throws CheckException
	 */
	private function checkAuthorisation($share, $password) {
		$passwordRequired = $share->getPassword();

		if (isset($passwordRequired)) {
			if ($password !== null) {
				$this->authenticate($share, $password);
			} else {
				$this->checkSession($share);
			}
		}
	}

	/**
	 * Authenticate link item with the given password
	 * or with the session if no password was given.
	 *
	 * @param IShare $share
	 * @param string $password
	 *
	 * @return bool true if authorized, an exception is raised otherwise
	 *
	 * @throws CheckException
	 */
	private function authenticate($share, $password) {
		if ((int)$share->getShareType() === Share::SHARE_TYPE_LINK) {
			$this->checkPassword($share, $password);
		} else {
			throw new CheckException(
				'Unknown share type ' . $share->getShareType() . ' for share id '
				. $share->getId(), Http::STATUS_NOT_FOUND
			);
		}

		return true;
	}

	/**
	 * Validates the given password
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
	 * @param IShare $share
	 * @param string $password
	 *
	 * @throws CheckException
	 */
	private function checkPassword($share, $password) {
		$newHash = '';
		if ($this->shareManager->checkPassword($share, $password)) {
			// Save item id in session for future requests
			$this->session->set('public_link_authenticated', (string)$share->getId());
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
	 * @param IShare $share
	 *
	 * @throws CheckException
	 */
	private function checkSession($share) {
		// Not authenticated ?
		if (!$this->session->exists('public_link_authenticated')
			|| $this->session->get('public_link_authenticated') !== (string)$share->getId()
		) {
			throw new CheckException("Missing password", Http::STATUS_UNAUTHORIZED);
		}
	}

}
