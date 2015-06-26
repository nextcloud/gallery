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
use OCP\ILogger;

use OCA\GalleryPlus\Environment\Environment;
use OCA\GalleryPlus\Preview\Preview;

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
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		Environment $environment,
		Preview $previewManager,
		ILogger $logger
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
	 * @param bool $nativeSvgSupport
	 *
	 * @return \string[] all supported media types
	 */
	public function getSupportedMediaTypes($slideshow, $nativeSvgSupport) {
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
		$supportedMimes = $this->addSvgSupport($supportedMimes, $nativeSvgSupport);
		//$this->logger->debug("Supported Mimes: {mimes}", ['mimes' => $supportedMimes]);

		return $supportedMimes;
	}

	/**
	 * Decides if we should download the file instead of generating a preview
	 *
	 * @param File $file
	 * @param bool $animatedPreview
	 *
	 * @return bool
	 */
	public function isPreviewRequired($file, $animatedPreview) {
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
	 *    * fileid:  the file's ID
	 *    * mimetype: the file's media type
	 *    * preview: the preview's content
	 *
	 * Example logger
	 * $this->logger->debug(
	 * "[PreviewService] Path : {path} / mime: {mimetype} / fileid: {fileid}",
	 * [
	 * 'path'     => $preview['data']['path'],
	 * 'mimetype' => $preview['data']['mimetype'],
	 * 'fileid'   => $preview['fileid']
	 * ]
	 * );
	 *
	 * @todo Get the max size from the settings
	 *
	 * @param File $file
	 * @param int $maxX asked width for the preview
	 * @param int $maxY asked height for the preview
	 * @param bool $keepAspect
	 * @param bool $base64Encode
	 *
	 * @return array<string,\OC_Image|string>|false preview data
	 */
	public function createPreview(
		$file, $maxX = 0, $maxY = 0, $keepAspect = true, $base64Encode = false
	) {
		$userId = $this->environment->getUserId();
		$imagePathFromFolder = $this->environment->getPathFromUserFolder($file);

		$this->previewManager->setupView($userId, $file, $imagePathFromFolder);

		$preview = $this->previewManager->preparePreview($maxX, $maxY, $keepAspect);
		if ($preview) {
			if ($base64Encode) {
				$preview['preview'] = $this->encode($preview['preview']);
			}
		}

		return $preview;
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
	 * @param bool $nativeSvgSupport
	 *
	 * @return \string[]
	 */
	private function addSvgSupport($supportedMimes, $nativeSvgSupport) {
		if (!in_array('image/svg+xml', $supportedMimes) && $nativeSvgSupport) {
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
