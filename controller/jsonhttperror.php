<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Bernhard Posselt <dev@bernhard-posselt.com>
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Bernhard Posselt 2014-2015
 * @copyright Olivier Paroz 2014-2015
 */

namespace OCA\GalleryPlus\Controller;

use Exception;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;

use OCA\GalleryPlus\Environment\NotFoundEnvException;
use OCA\GalleryPlus\Service\NotFoundServiceException;

/**
 * Our classes extend both Controller and ApiController, so we need to use
 * traits to add some common methods
 *
 * @package OCA\GalleryPlus\Controller
 */
trait JsonHttpError {

	/**
	 * @param \Exception $exception the message that is returned taken from the exception
	 *
	 * @return JSONResponse
	 */
	public function error(Exception $exception) {
		$message = $exception->getMessage();
		$code = Http::STATUS_INTERNAL_SERVER_ERROR;

		if ($exception instanceof NotFoundServiceException || $exception instanceof NotFoundEnvException) {
			$code = Http::STATUS_NOT_FOUND;
		}

		return new JSONResponse(
			[
				'message' => $message,
				'success' => false
			],
			$code
		);
	}
}