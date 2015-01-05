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

use OCP\AppFramework\Http\JSONResponse;

/**
 * Our classes extend both Controller and ApiController, so we need to use
 * traits to add some common methods
 *
 * @package OCA\GalleryPlus\Controller
 */
trait JsonHttpError {

	/**
	 * @param \Exception $exception the message that is returned taken from the
	 * exception
	 * @param int $code the http error code
	 *
	 * @return JSONResponse
	 */
	public function error(\Exception $exception, $code = 0) {
		$message = $exception->getMessage();
		if ($code === 0) {
			$code = $exception->getCode();
		}

		return new JSONResponse(
			array(
				'message' => $message,
				'success' => false
			),
			$code
		);
	}
}