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

namespace OCA\GalleryPlus\Service;

use OCP\Files\File;
use OCP\Template;

use OCA\GalleryPlus\Environment\Environment;
use OCA\GalleryPlus\Environment\NotFoundEnvException;
use OCA\GalleryPlus\Preview\Preview;
use OCA\GalleryPlus\Utility\SmarterLogger;

/**
 * Generates previews
 *
 * @package OCA\GalleryPlus\Service
 */
class PreviewService extends Service {

	use Base64Encode;

	/**
	 * @var Preview
	 */
	private $previewManager;
	/**
	 * @todo This hard-coded array could be replaced by admin settings
	 *
	 * @var string[]
	 */
	private $baseMimeTypes = [
		'image/png',
		'image/jpeg',
		'image/gif',
		'image/x-xbitmap',
		'image/bmp',
		'image/tiff',
		'image/x-dcraw',
		'application/x-photoshop',
		'application/illustrator',
		'application/postscript',
	];
	/**
	 * These types are useful for files preview in the files app, but
	 * not for the gallery side
	 *
	 * @var string[]
	 */
	private $slideshowMimeTypes = [
		'application/font-sfnt',
		'application/x-font',
	];

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param Environment $environment
	 * @param Preview $previewManager
	 * @param SmarterLogger $logger
	 */
	public function __construct(
		$appName,
		Environment $environment,
		Preview $previewManager,
		SmarterLogger $logger
	) {
		parent::__construct($appName, $environment, $logger);

		$this->previewManager = $previewManager;
	}

	/**
	 * This builds and returns a list of all supported media types
	 *
	 * @todo Native SVG could be disabled via admin settings
	 *
	 * @param bool $slideshow
	 *
	 * @return string[] all supported media types
	 */
	public function getSupportedMediaTypes($slideshow) {
		$supportedMimes = [];
		$wantedMimes = $this->baseMimeTypes;
		if ($slideshow) {
			$wantedMimes = array_merge($wantedMimes, $this->slideshowMimeTypes);
		}
		foreach ($wantedMimes as $wantedMime) {
			// Let's see if a preview of files of that media type can be generated
			if ($this->isMimeSupported($wantedMime)) {
				$pathToIcon = Template::mimetype_icon($wantedMime);
				$supportedMimes[$wantedMime] =
					$pathToIcon; // We add it to the list of supported media types
			}
		}
		$supportedMimes = $this->addSvgSupport($supportedMimes);
		//$this->logger->debug("Supported Mimes: {mimes}", ['mimes' => $supportedMimes]);

		return $supportedMimes;
	}

	/**
	 * Decides if we should download the file instead of generating a preview
	 *
	 * @param string $image
	 * @param bool $animatedPreview
	 *
	 * @return bool
	 *
	 * @throws NotFoundServiceException
	 */
	public function isPreviewRequired($image, $animatedPreview) {
		$file = null;
		try {
			/** @var File $file */
			$file = $this->environment->getResourceFromPath($image);

		} catch (NotFoundEnvException $exception) {
			$this->logAndThrowNotFound($exception->getMessage());
		}
		$mime = $file->getMimeType();
		if ($mime === 'image/svg+xml') {
			return $this->isSvgPreviewRequired();
		}
		if ($mime === 'image/gif') {
			return $this->isGifPreviewRequired($file, $animatedPreview);
		}

		return true;
	}

	/**
	 * Returns an array containing everything needed by the client to be able to display a preview
	 *
	 *    * path: the given path to the file
	 *    * mimetype: the file's media type
	 *    * preview: the preview's content
	 *    * status: a code indicating whether the conversion process was successful or not
	 *
	 * Example logger
	 * $this->logger->debug(
	 * "[PreviewService] Path : {path} / size: {size} / mime: {mimetype} / status: {status}",
	 * [
	 * 'path'     => $perfectPreview['data']['path'],
	 * 'mimetype' => $perfectPreview['data']['mimetype'],
	 * 'status'   => $perfectPreview['status']
	 * ]
	 * );
	 *
	 * @todo Get the max size from the settings
	 *
	 * @param string $image path to the image, relative to the user folder
	 * @param int $maxX asked width for the preview
	 * @param int $maxY asked height for the preview
	 * @param bool $keepAspect
	 * @param bool $base64Encode
	 *
	 * @return array<string,\OC_Image|string> preview data
	 * @throws NotFoundServiceException
	 */
	public function createPreview(
		$image, $maxX = 0, $maxY = 0, $keepAspect = true, $base64Encode = false
	) {
		$file = null;
		try {
			/** @var File $file */
			$file = $this->environment->getResourceFromPath($image);
		} catch (NotFoundEnvException $exception) {
			$this->logAndThrowNotFound($exception->getMessage());
		}
		$userId = $this->environment->getUserId();
		$imagePathFromFolder = $this->environment->getImagePathFromFolder($image);
		$this->previewManager->setupView($userId, $file, $imagePathFromFolder);

		$preview = $this->previewManager->preparePreview($maxX, $maxY, $keepAspect);
		if ($base64Encode) {
			$preview['preview'] = $this->encode($preview['preview']);
		}
		$preview['path'] = $image;

		return $preview;
	}

	/**
	 * Returns true if the preview was successfully generated
	 *
	 * @return bool
	 */
	public function isPreviewValid() {
		return $this->previewManager->isPreviewValid();
	}

	/**
	 * Makes sure we return previews of the asked dimensions and fix the cache
	 * if necessary
	 *
	 * @param bool $square
	 * @param bool $base64Encode
	 *
	 * @return \OC_Image|string
	 */
	public function previewValidator($square, $base64Encode) {
		$preview = $this->previewManager->previewValidator($square);
		if ($base64Encode) {
			$preview = $this->encode($preview);
		}

		return $preview;
	}

	/**
	 * Adds the SVG media type if it's not already there
	 *
	 * If it's enabled, but doesn't work, an exception will be raised when trying to generate a
	 * preview. If it's disabled, we support it via the browser's native support
	 *
	 * @param string[] $supportedMimes
	 *
	 * @return string[]
	 */
	private function addSvgSupport($supportedMimes) {

		if (!in_array('image/svg+xml', $supportedMimes)) {
			$supportedMimes['image/svg+xml'] = Template::mimetype_icon('image/svg+xml');
		}

		return $supportedMimes;
	}

	/**
	 * Returns true if the passed mime type is supported
	 *
	 * In case of a failure, we just return that the media type is not supported
	 *
	 * @param string $mimeType
	 *
	 * @return boolean
	 */
	private function isMimeSupported($mimeType = '*') {
		try {
			return $this->previewManager->isMimeSupported($mimeType);
		} catch (\Exception $exception) {
			unset($exception);

			return false;
		}
	}

	/**
	 * Decides if we should download the SVG or generate a preview
	 *
	 * SVGs are downloaded if the SVG converter is disabled
	 * Files of any media type are downloaded if requested by the client
	 *
	 * @return bool
	 */
	private function isSvgPreviewRequired() {
		if (!$this->isMimeSupported('image/svg+xml')) {
			return false;
		}

		return true;
	}

	/**
	 * Decides if we should download the GIF or generate a preview
	 *
	 * GIFs are downloaded if they're animated and we want to show
	 * animations
	 *
	 * @param File $file
	 * @param bool $animatedPreview
	 *
	 * @return bool
	 */
	private function isGifPreviewRequired($file, $animatedPreview) {
		$animatedGif = $this->isGifAnimated($file);

		if ($animatedPreview && $animatedGif) {
			return false;
		}

		return true;
	}

	/**
	 * Tests if a GIF is animated
	 *
	 * An animated gif contains multiple "frames", with each frame having a
	 * header made up of:
	 *    * a static 4-byte sequence (\x00\x21\xF9\x04)
	 *    * 4 variable bytes
	 *    * a static 2-byte sequence (\x00\x2C) (Photoshop uses \x00\x21)
	 *
	 * We read through the file until we reach the end of the file, or we've
	 * found at least 2 frame headers
	 *
	 * @link http://php.net/manual/en/function.imagecreatefromgif.php#104473
	 *
	 * @param File $file
	 *
	 * @return bool
	 */
	private function isGifAnimated($file) {
		$fileHandle = $file->fopen('rb');
		$count = 0;
		while (!feof($fileHandle) && $count < 2) {
			$chunk = fread($fileHandle, 1024 * 100); //read 100kb at a time
			$count += preg_match_all(
				'#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches
			);
		}

		fclose($fileHandle);

		return $count > 1;
	}

}
