<?php
/**
 * Nextcloud - Gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <galleryapps@oparoz.com>
 *
 * @copyright Olivier Paroz 2017
 */

namespace OCA\Gallery\Service;

use OCP\Files\Folder;
use OCP\IPreview;
use OCP\ILogger;

use OCA\Gallery\Config\ConfigParser;
use OCA\Gallery\Config\ConfigException;
use OCA\Gallery\Environment\Environment;

/**
 * Finds configurations files and returns a configuration array
 *
 * Checks the current and parent folders for configuration files and to see if we're allowed to
 * look for media file
 * Supports explicit inheritance
 *
 * @package OCA\Gallery\Service
 */
class ConfigService extends FilesService {

	/** @var string */
	private $configName = 'gallery.cnf';
	/** @var array <string,bool> */
	private $completionStatus = ['design' => false, 'information' => false, 'sorting' => false];
	/** @var ConfigParser */
	private $configParser;
	/** @var IPreview */
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
		'image/heic',
		'image/heif',
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
	 * @param ConfigParser $configParser
	 * @param IPreview $previewManager
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		Environment $environment,
		ConfigParser $configParser,
		IPreview $previewManager,
		ILogger $logger
	) {
		parent::__construct($appName, $environment, $logger);

		$this->configParser = $configParser;
		$this->previewManager = $previewManager;
	}

	/**
	 * Returns a list of supported features
	 *
	 * @return string[]
	 */
	public function getFeaturesList() {
		$featuresList = [];
		/** @var Folder $rootFolder */
		$rootFolder = $this->environment->getVirtualRootFolder();
		if ($this->isAllowedAndAvailable($rootFolder) && $this->configExists($rootFolder)) {
			try {
				$featuresList =
					$this->configParser->getFeaturesList($rootFolder, $this->configName);
			} catch (ConfigException $exception) {
				$featuresList = $this->buildErrorMessage($exception, $rootFolder);
			}
		}

		return $featuresList;
	}

	/**
	 * This builds and returns a list of all supported media types
	 *
	 * @todo Native SVG could be disabled via admin settings
	 *
	 * @param bool $extraMediaTypes
	 * @param bool $nativeSvgSupport
	 *
	 * @return string[] all supported media types
	 */
	public function getSupportedMediaTypes($extraMediaTypes, $nativeSvgSupport) {
		$supportedMimes = [];
		$wantedMimes = $this->baseMimeTypes;
		if ($extraMediaTypes) {
			$wantedMimes = array_merge($wantedMimes, $this->slideshowMimeTypes);
		}
		foreach ($wantedMimes as $wantedMime) {
			// Let's see if a preview of files of that media type can be generated
			if ($this->isMimeSupported($wantedMime)) {
				// We store the media type
				$supportedMimes[] = $wantedMime;
			}
		}
		$supportedMimes = $this->addSvgSupport($supportedMimes, $nativeSvgSupport);

		//$this->logger->debug("Supported Mimes: {mimes}", ['mimes' => $supportedMimes]);

		return $supportedMimes;
	}

	/**
	 * Returns the configuration of the currently selected folder
	 *
	 *    * information (description, copyright)
	 *    * sorting (date, name, inheritance)
	 *    * design (colour)
	 *    * if the album should be ignored
	 *
	 * @param Folder $folderNode the current folder
	 * @param array $features the list of features retrieved fro the configuration file
	 *
	 * @return array|null
	 * @throws ForbiddenServiceException
	 */
	public function getConfig($folderNode, $features) {
		$this->features = $features;
		list ($albumConfig, $ignored) =
			$this->collectConfig($folderNode, $this->ignoreAlbumStrings, $this->configName);
		if ($ignored) {
			throw new ForbiddenServiceException(
				'The owner has placed a restriction or the storage location is unavailable'
			);
		}

		return $albumConfig;
	}

	/**
	 * Throws an exception if the media type of the file is not part of what the app allows
	 *
	 * @param $mimeType
	 *
	 * @throws ForbiddenServiceException
	 */
	public function validateMimeType($mimeType) {
		if (!in_array($mimeType, $this->getSupportedMediaTypes(true, true))) {
			throw new ForbiddenServiceException('Media type not allowed');
		}
	}

	/**
	 * Determines if we have a configuration file to work with
	 *
	 * @param Folder $rootFolder the virtual root folder
	 *
	 * @return bool
	 */
	private function configExists($rootFolder) {
		return $rootFolder && $rootFolder->nodeExists($this->configName);
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
	 * @return string[]
	 */
	private function addSvgSupport($supportedMimes, $nativeSvgSupport) {
		if (!in_array('image/svg+xml', $supportedMimes) && $nativeSvgSupport) {
			$supportedMimes[] = 'image/svg+xml';
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
	 * Returns an album configuration array
	 *
	 * Goes through all the parent folders until either we're told the album is private or we've
	 * reached the root folder
	 *
	 * @param Folder $folder the current folder
	 * @param array $ignoreAlbumStrings names of the files which blacklist folders
	 * @param string $configName name of the configuration file
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
	 * @param array $configSoFar the configuration collected so far
	 *
	 * @return array <null|array,bool>
	 */
	private function collectConfig(
		$folder, $ignoreAlbumStrings, $configName, $level = 0, $configSoFar = []
	) {
		foreach ($ignoreAlbumStrings as $ignoreAlbum) {
			if ($folder->nodeExists($ignoreAlbum)) {
				// Cancel as soon as we find out that the folder is private or external
				return [null, true];
			}
		}
		$isRootFolder = $this->isRootFolder($folder, $level);
		if ($folder->nodeExists($configName)) {
			$configSoFar = $this->buildFolderConfig($folder, $configName, $configSoFar, $level);
		}
		if (!$isRootFolder) {
			return $this->getParentConfig($folder, $ignoreAlbumStrings, $configName, $level, $configSoFar);
		}
		$configSoFar = $this->validatesInfoConfig($configSoFar);

		// We have reached the root folder
		return [$configSoFar, false];
	}

	/**
	 * Returns a parsed configuration if one was found in the current folder or generates an error
	 * message to send back
	 *
	 * @param Folder $folder the current folder
	 * @param string $configName name of the configuration file
	 * @param array $collectedConfig the configuration collected so far
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
	 *
	 * @return array
	 */
	private function buildFolderConfig($folder, $configName, $collectedConfig, $level) {
		try {
			list($collectedConfig, $completionStatus) = $this->configParser->getFolderConfig(
				$folder, $configName, $collectedConfig, $this->completionStatus, $level
			);
			$this->completionStatus = $completionStatus;
		} catch (ConfigException $exception) {
			$collectedConfig = $this->buildErrorMessage($exception, $folder);
		}

		return $collectedConfig;
	}

	/**
	 * Builds the error message to send back when there is an error
	 *
	 * @fixme Missing translation
	 *
	 * @param ConfigException $exception
	 * @param Folder $folder the current folder
	 *
	 * @return array<array<string,string>,bool>
	 */
	private function buildErrorMessage($exception, $folder) {
		$configPath = $this->environment->getPathFromVirtualRoot($folder);
		$errorMessage = $exception->getMessage() . ". Config location: /$configPath";
		$this->logger->error($errorMessage);
		$config = ['error' => ['message' => $errorMessage]];

		$completionStatus = $this->completionStatus;
		foreach ($completionStatus as $key) {
			$completionStatus[$key] = true;
		}
		$this->completionStatus = $completionStatus;

		return [$config];
	}

	/**
	 * Removes links if they were collected outside of the virtual root
	 *
	 * This is for shared folders which have a virtual root
	 *
	 * @param array $albumConfig
	 *
	 * @return array
	 */
	private function validatesInfoConfig($albumConfig) {
		$this->virtualRootLevel;
		if (array_key_exists('information', $albumConfig)) {
			$info = $albumConfig['information'];
			if (array_key_exists('level', $info)) {
				$level = $info['level'];
				if ($level > $this->virtualRootLevel) {
					$albumConfig['information']['description_link'] = null;
					$albumConfig['information']['copyright_link'] = null;
				}
			}
		}

		return $albumConfig;
	}

	/**
	 * Looks for an album configuration in the parent folder
	 *
	 * We will look up to the virtual root of a shared folder, for privacy reasons
	 *
	 * @param Folder $folder the current folder
	 * @param string $privacyChecker names of the files which blacklist folders
	 * @param string $configName name of the configuration file
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
	 * @param array $collectedConfig the configuration collected so far
	 *
	 * @return array<null|array,bool>
	 */
	private function getParentConfig($folder, $privacyChecker, $configName, $level, $collectedConfig
	) {
		$parentFolder = $folder->getParent();
		$level++;

		return $this->collectConfig(
			$parentFolder, $privacyChecker, $configName, $level, $collectedConfig
		);
	}

}
