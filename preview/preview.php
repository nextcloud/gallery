<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2016
 */

namespace OCA\Gallery\Preview;

use OCP\IConfig;
use OCP\Image;
use OCP\Files\File;
use OCP\IPreview;
use OCP\ILogger;

/**
 * Generates previews
 *
 * @todo On OC9, replace \OC\Preview with IPreview if methods we need have been added
 *
 * @package OCA\Gallery\Preview
 */
class Preview {

	/**
	 * @var string
	 */
	private $dataDir;
	/**
	 * @var mixed
	 */
	private $previewManager;
	/**
	 * @var ILogger
	 */
	private $logger;
	/**
	 * @var string
	 */
	private $userId;
	/**
	 * @var \OC\Preview
	 */
	private $preview;
	/**
	 * @var File
	 */
	private $file;
	/**
	 * @var int[]
	 */
	private $dims;

	/**
	 * Constructor
	 *
	 * @param IConfig $config
	 * @param IPreview $previewManager
	 * @param ILogger $logger
	 */
	public function __construct(
		IConfig $config,
		IPreview $previewManager,
		ILogger $logger
	) {
		$this->dataDir = $config->getSystemValue('datadirectory');
		$this->previewManager = $previewManager;
		$this->logger = $logger;
	}

	/**
	 * Returns true if the passed mime type is supported
	 *
	 * @param string $mimeType
	 *
	 * @return boolean
	 */
	public function isMimeSupported($mimeType = '*') {
		return $this->previewManager->isMimeSupported($mimeType);
	}

	/**
	 * Initialises the view which will be used to access files and generate previews
	 *
	 * @fixme Private API, but can't use the PreviewManager yet as it's incomplete
	 *
	 * @param string $userId
	 * @param File $file
	 * @param string $imagePathFromFolder
	 */
	public function setupView($userId, $file, $imagePathFromFolder) {
		$this->userId = $userId;
		$this->file = $file;
		$imagePathFromFolder = ltrim($imagePathFromFolder, '/');
		$this->preview = new \OC\Preview($userId, 'files', $imagePathFromFolder);
	}

	/**
	 * Returns a preview based on OC's preview class and our custom methods
	 *
	 * We check that the preview returned by the Preview class can be used by
	 * the browser. If not, we send "false" to the controller
	 *
	 * @fixme setKeepAspect is missing from public interface.
	 *     https://github.com/owncloud/core/issues/12772
	 *
	 * @param int $maxWidth
	 * @param int $maxHeight
	 * @param bool $keepAspect
	 *
	 * @return array<string,string|\OC_Image>|false
	 */
	public function preparePreview($maxWidth, $maxHeight, $keepAspect) {
		$this->dims = [$maxWidth, $maxHeight];

		$previewData = $this->getPreviewFromCore($keepAspect);

		if ($previewData && $previewData->valid()) {
			$preview = [
				'preview'  => $previewData,
				'mimetype' => $previewData->mimeType()
			];
		} else {
			$preview = false;
		}

		return $preview;
	}

	/**
	 * Asks core for a preview based on our criteria
	 *
	 * @todo Need to read scaling setting from settings
	 *
	 * @param bool $keepAspect
	 *
	 * @return \OC_Image
	 */
	private function getPreviewFromCore($keepAspect) {
		list($maxX, $maxY) = $this->dims;

		$this->preview->setMaxX($maxX);
		$this->preview->setMaxY($maxY);
		$this->preview->setScalingUp(false);
		$this->preview->setKeepAspect($keepAspect);

		//$this->logger->debug("[PreviewService] preview {preview}", ['preview' => $this->preview]);

		$previewData = $this->preview->getPreview();

		return $previewData;
	}

}
