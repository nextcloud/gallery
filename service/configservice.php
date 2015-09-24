<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2015
 */

namespace OCA\Gallery\Service;

use OCP\Files\Folder;
use OCP\ILogger;

use OCA\Gallery\Config\ConfigParser;
use OCA\Gallery\Config\ConfigException;
use OCA\Gallery\Environment\Environment;
use OCA\Gallery\Preview\Preview;

/**
 * Finds configurations files and returns a configuration array
 *
 * Checks the current and parent folders for configuration files and the privacy flag
 * Supports explicit inheritance
 *
 * @package OCA\Gallery\Service
 */
class ConfigService extends FilesService {

	/**
	 * @var string
	 */
	private $configName = 'gallery.cnf';
	/**
	 * @var string
	 */
	private $privacyChecker = '.nomedia';
	/**
	 * @var array <string,bool>
	 */
	private $completionStatus = ['design' => false, 'information' => false, 'sorting' => false];
	/**
	 * @var ConfigParser
	 */
	private $configParser;
	/** @var Preview */
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
	 * @param ConfigParser $configParser
	 * @param Preview $previewManager
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		Environment $environment,
		ConfigParser $configParser,
		Preview $previewManager,
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
	 * Returns information about the currently selected folder
	 *
	 *    * privacy setting
	 *    * special configuration
	 *    * permissions
	 *    * ID
	 *
	 * @param Folder $folderNode the current folder
	 * @param string $folderPathFromRoot path from the current folder to the virtual root
	 * @param array $features the list of features retrieved fro the configuration file
	 *
	 * @return array|null
	 * @throws ForbiddenServiceException
	 */
	public function getAlbumInfo($folderNode, $folderPathFromRoot, $features) {
		$this->features = $features;
		list ($albumConfig, $privateAlbum) =
			$this->getAlbumConfig($folderNode, $this->privacyChecker, $this->configName);
		if ($privateAlbum) {
			throw new ForbiddenServiceException('Album is private or unavailable');
		}
		$albumInfo = [
			'path'        => $folderPathFromRoot,
			'fileid'      => $folderNode->getID(),
			'permissions' => $folderNode->getPermissions(),
			'etag'        => $folderNode->getEtag()
		];
		// There is always an albumInfo, but the albumConfig may be empty
		$albumConfig = array_merge($albumInfo, $albumConfig);

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
	 * @param string $privacyChecker name of the file which blacklists folders
	 * @param string $configName name of the configuration file
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
	 * @param array $config the configuration collected so far
	 *
	 * @return array<null|array,bool>
	 */
	private function getAlbumConfig(
		$folder, $privacyChecker, $configName, $level = 0, $config = []
	) {
		if ($folder->nodeExists($privacyChecker)) {
			// Cancel as soon as we find out that the folder is private or external
			return [null, true];
		}
		$isRootFolder = $this->isRootFolder($folder, $level);
		if ($folder->nodeExists($configName)) {
			$config = $this->buildFolderConfig($folder, $configName, $config, $level);
		}
		if (!$isRootFolder) {
			return $this->getParentConfig(
				$folder, $privacyChecker, $configName, $level, $config
			);
		}
		$config = $this->validatesInfoConfig($config);

		// We have reached the root folder
		return [$config, false];
	}

	/**
	 * Returns a parsed configuration if one was found in the current folder or generates an error
	 * message to send back
	 *
	 * @param Folder $folder the current folder
	 * @param string $configName name of the configuration file
	 * @param array $config the configuration collected so far
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
	 *
	 * @return array
	 */
	private function buildFolderConfig($folder, $configName, $config, $level) {
		try {
			list($config, $completionStatus) = $this->configParser->getFolderConfig(
				$folder, $configName, $config, $this->completionStatus, $level
			);
			$this->completionStatus = $completionStatus;
		} catch (ConfigException $exception) {
			$config = $this->buildErrorMessage($exception, $folder);
		}

		return $config;
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
	 * @param string $privacyChecker name of the file which blacklists folders
	 * @param string $configName name of the configuration file
	 * @param int $level the starting level is 0 and we add 1 each time we visit a parent folder
	 * @param array $config the configuration collected so far
	 *
	 * @return array<null|array,bool>
	 */
	private function getParentConfig($folder, $privacyChecker, $configName, $level, $config) {
		$parentFolder = $folder->getParent();
		$level++;

		return $this->getAlbumConfig(
			$parentFolder, $privacyChecker, $configName, $level, $config
		);
	}

}
