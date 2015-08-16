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

namespace OCA\GalleryPlus\Controller;

use OCP\IRequest;
use OCP\ILogger;

use OCP\AppFramework\ApiController;

use OCA\GalleryPlus\Service\SearchFolderService;
use OCA\GalleryPlus\Service\ConfigService;
use OCA\GalleryPlus\Service\SearchMediaService;

/**
 * Class FilesApiController
 *
 * @package OCA\GalleryPlus\Controller
 */
class FilesApiController extends ApiController {

	use Files;
	use JsonHttpError;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param SearchFolderService $searchFolderService
	 * @param ConfigService $configService
	 * @param SearchMediaService $searchMediaService
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		SearchFolderService $searchFolderService,
		ConfigService $configService,
		SearchMediaService $searchMediaService,
		ILogger $logger
	) {
		parent::__construct($appName, $request);

		$this->searchFolderService = $searchFolderService;
		$this->configService = $configService;
		$this->searchMediaService = $searchMediaService;
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 * @CORS
	 *
	 * Returns a list of all media files available to the authenticated user
	 *
	 *    * Authentication can be via a login/password or a token/(password)
	 *    * For private galleries, it returns all media files, with the full path from the root
	 *     folder For public galleries, the path starts from the folder the link gives access to
	 *     (virtual root)
	 *    * An exception is only caught in case something really wrong happens. As we don't test
	 *     files before including them in the list, we may return some bad apples
	 *
	 * @param string $location a path representing the current album in the app
	 * @param string $features the list of supported features
	 * @param string $etag the last known etag in the client
	 * @param string $mediatypes the list of supported media types
	 *
	 * @return array <string,array<string,string|int>>|Http\JSONResponse
	 */
	public function getList($location, $features, $etag, $mediatypes) {
		$featuresArray = explode(',', $features);
		$mediaTypesArray = explode(';', $mediatypes);
		try {
			return $this->getFiles($location, $featuresArray, $etag, $mediaTypesArray);
		} catch (\Exception $exception) {
			return $this->error($exception);
		}
	}

}
