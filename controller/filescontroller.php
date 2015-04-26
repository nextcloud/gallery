<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Robin Appelman <icewind@owncloud.com>
 *
 * @copyright Olivier Paroz 2014-2015
 * @copyright Robin Appelman 2012-2014
 */

namespace OCA\GalleryPlus\Controller;

use OCP\IRequest;
use OCP\Files\Folder;
use OCP\ILogger;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;

use OCA\GalleryPlus\Service\FilesService;
use OCA\GalleryPlus\Service\ConfigService;
use OCA\GalleryPlus\Service\SearchMediaService;

/**
 * Class FilesController
 *
 * @package OCA\GalleryPlus\Controller
 */
class FilesController extends Controller {

	use JsonHttpError;

	/**
	 * @var FilesService
	 */
	private $filesService;
	/**
	 * @var ConfigService
	 */
	private $configService;
	/**
	 * @var SearchMediaService
	 */
	private $searchMediaService;
	/**
	 * @var ILogger
	 */
	private $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param IRequest $request
	 * @param FilesService $filesService
	 * @param ConfigService $configService
	 * @param SearchMediaService $searchMediaService
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		IRequest $request,
		FilesService $filesService,
		ConfigService $configService,
		SearchMediaService $searchMediaService,
		ILogger $logger
	) {
		parent::__construct($appName, $request);

		$this->filesService = $filesService;
		$this->configService = $configService;
		$this->searchMediaService = $searchMediaService;
		$this->logger = $logger;
	}

	/**
	 * @NoAdminRequired
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
	 *
	 * @return array <string,array<string,string|int>>|Http\JSONResponse
	 */
	public function getFiles($location, $features) {
		$features = explode(',', $features);
		$mediaTypesArray = explode(';', $this->request->getParam('mediatypes'));
		try {
			/** @var Folder $folderNode */
			list($folderPathFromRoot, $folderNode, $locationHasChanged) =
				$this->filesService->getCurrentFolder(rawurldecode($location), $features);
			$albumInfo =
				$this->configService->getAlbumInfo($folderNode, $folderPathFromRoot, $features);
			$files =
				$this->searchMediaService->getMediaFiles($folderNode, $mediaTypesArray, $features);

			return $this->formatResults($files, $albumInfo, $locationHasChanged);
		} catch (\Exception $exception) {
			return $this->error($exception);
		}
	}

	/**
	 * Simply builds and returns an array containing the list of files, the album information and
	 * whether the location has changed or not
	 *
	 * @param $files
	 * @param $albumInfo
	 * @param $locationHasChanged
	 *
	 * @return array
	 */
	private function formatResults($files, $albumInfo, $locationHasChanged) {
		return [
			'files'              => $files,
			'albuminfo'          => $albumInfo,
			'locationhaschanged' => $locationHasChanged
		];
	}

}
