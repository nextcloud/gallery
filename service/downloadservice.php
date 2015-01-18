<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\GalleryPlus\Service;

use OCP\Files\File;

use OCA\GalleryPlus\Environment\Environment;
use OCA\GalleryPlus\Environment\NotFoundEnvException;
use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Prepares the file to download
 *
 * @package OCA\GalleryPlus\Service
 */
class DownloadService extends Service {

	use Base64Encode;

	/**
	 * @type Environment
	 */
	private $environment;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param Environment $environment
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		$appName,
		Environment $environment,
		SmarterLogger $logger
	) {
		parent::__construct($appName, $logger);

		$this->environment = $environment;
	}

	/**
	 * Downloads the requested file
	 *
	 * @param string $image
	 * @param bool $base64Encode
	 *
	 * @return array
	 */
	public function downloadFile($image, $base64Encode = false) {
		$file = null;
		try {
			/** @type File $file */
			$file = $this->environment->getResourceFromPath($image);
		} catch (NotFoundEnvException $exception) {
			$this->logAndThrowNotFound($exception->getMessage());
		}
		$this->logger->debug("[DownloadService] File to Download: {file}");
		$download = [
			'path'     => $image,
			'preview'  => $file->getContent(),
			'mimetype' => $file->getMimeType()
		];

		if ($base64Encode) {
			$download['preview'] = $this->encode($download['preview']);
		}

		return $download;
	}

}