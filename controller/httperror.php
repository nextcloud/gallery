<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Bernhard Posselt 2014-2015
 * @copyright Olivier Paroz 2014-2016
 */

namespace OCA\Gallery\Controller;

use Exception;

use OCP\IURLGenerator;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\RedirectResponse;

use OCA\Gallery\Environment\NotFoundEnvException;
use OCA\Gallery\Service\NotFoundServiceException;
use OCA\Gallery\Service\ForbiddenServiceException;

/**
 * Our classes extend both Controller and ApiController, so we need to use
 * traits to add some common methods
 *
 * @package OCA\Gallery\Controller
 */
trait HttpError {

	/**
	 * @param \Exception $exception
	 *
	 * @return JSONResponse
	 */
	public function jsonError(Exception $exception) {
		$message = $exception->getMessage();
		$code = $this->getHttpStatusCode($exception);

		return new JSONResponse(
			[
				'message' => $message . ' (' . $code . ')',
				'success' => false
			],
			$code
		);
	}

	/**
	 * @param IURLGenerator $urlGenerator
	 * @param string $appName
	 * @param \Exception $exception
	 *
	 * @return RedirectResponse
	 */
	public function htmlError($urlGenerator, $appName, Exception $exception) {
		$message = $exception->getMessage();
		$code = $this->getHttpStatusCode($exception);
		$url = $urlGenerator->linkToRoute(
			$appName . '.page.error_page', ['code' => $code]
		);

		$response = new RedirectResponse($url);
		$response->addCookie('galleryErrorMessage', $message);

		return $response;
	}

	/**
	 * Returns an error array
	 *
	 * @param $exception
	 *
	 * @return array<null|int|string>
	 */
	public function getHttpStatusCode($exception) {
		$code = Http::STATUS_INTERNAL_SERVER_ERROR;
		if ($exception instanceof NotFoundServiceException
			|| $exception instanceof NotFoundEnvException
		) {
			$code = Http::STATUS_NOT_FOUND;
		}
		if ($exception instanceof ForbiddenServiceException) {
			$code = Http::STATUS_FORBIDDEN;
		}

		return $code;
	}
}
