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

namespace OCA\GalleryPlus\Utility;

use OCP\ILogger;

/**
 * Lets us call the main logger without having to add the context at every
 * request
 *
 * @package OCA\GalleryPlus\Utility
 */
class SmarterLogger implements ILogger {

	/**
	 * @type ILogger
	 */
	private $logger;
	/**
	 * @type Normalizer
	 */
	private $normalizer;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param ILogger $logger
	 * @param Normalizer $normalizer
	 */
	public function __construct(
		$appName,
		ILogger $logger,
		Normalizer $normalizer
	) {
		$this->appName = $appName;
		$this->logger = $logger;
		$this->normalizer = $normalizer;
	}

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return null
	 */
	public function emergency($message, array $context = []) {
		$this->log(\OCP\Util::FATAL, $message, $context);
	}

	/**
	 * Action must be taken immediately.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return null
	 */
	public function alert($message, array $context = []) {
		$this->log(\OCP\Util::ERROR, $message, $context);
	}

	/**
	 * Critical conditions.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return null
	 */
	public function critical($message, array $context = []) {
		$this->log(\OCP\Util::ERROR, $message, $context);
	}

	/**
	 * Runtime errors that do not require immediate action but should typically
	 * be logged and monitored.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return null
	 */
	public function error($message, array $context = []) {
		$this->log(\OCP\Util::ERROR, $message, $context);
	}

	/**
	 * Exceptional occurrences that are not errors.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return null
	 */
	public function warning($message, array $context = []) {
		$this->log(\OCP\Util::WARN, $message, $context);
	}

	/**
	 * Normal but significant events.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return null
	 */
	public function notice($message, array $context = []) {
		$this->log(\OCP\Util::INFO, $message, $context);
	}

	/**
	 * Interesting events.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return null
	 */
	public function info($message, array $context = []) {
		$this->log(\OCP\Util::INFO, $message, $context);
	}

	/**
	 * Detailed debug information.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return null
	 */
	public function debug($message, array $context = []) {
		$this->log(\OCP\Util::DEBUG, $message, $context);
	}

	/**
	 * Converts the received log message to string before sending it to the
	 * ownCloud logger
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 *
	 * @return mixed
	 */
	public function log($level, $message, array $context = []) {
		array_walk($context, [$this, 'contextNormalizer']);

		if (!isset($context['app'])) {
			$context['app'] = $this->appName;
		}

		$this->logger->log($level, $message, $context);
	}

	/**
	 * Normalises the context parameters and JSON encodes and cleans up the
	 * result
	 *
	 * @todo: could maybe do a better job removing slashes
	 *
	 * @param array $data
	 *
	 * @return string|null
	 */
	private function contextNormalizer(&$data) {
		$data = $this->normalizer->normalize($data);
		if (!is_string($data)) {
			$data = @json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			// Removing null byte and double slashes from object properties
			$data = str_replace(['\\u0000', '\\\\'], ["", "\\"], $data);
		}
	}

}
