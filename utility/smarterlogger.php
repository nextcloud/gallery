<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Bart Visscher <bartv@thisnet.nl>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @copyright Olivier Paroz 2015
 * @copyright Bart Visscher 2013-2015
 * @copyright Jordi Boggiano 2014-2015
 */

namespace OCA\GalleryPlus\Utility;

use OCP\ILogger;

/**
 * Lets us call the main logger without having to add the context at every
 * request
 *
 * @package OCA\GalleryPlus\Middleware
 */
class SmarterLogger implements ILogger {

	/**
	 * @type ILogger
	 */
	private $logger;

	/***
	 * Constructor
	 *
	 * @param string $appName
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		ILogger $logger
	) {
		$this->appName = $appName;
		$this->logger = $logger;
	}

	/**
	 * System is unusable.
	 *
	 * @param string $message
	 * @param array $context
	 *
	 * @return null
	 */
	public function emergency($message, array $context = array()) {
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
	public function alert($message, array $context = array()) {
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
	public function critical($message, array $context = array()) {
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
	public function error($message, array $context = array()) {
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
	public function warning($message, array $context = array()) {
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
	public function notice($message, array $context = array()) {
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
	public function info($message, array $context = array()) {
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
	public function debug($message, array $context = array()) {
		$this->log(\OCP\Util::DEBUG, $message, $context);
	}

	/**
	 * Normalises a message and logs it with an arbitrary level.
	 *
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 *
	 * @return mixed
	 */
	public function log($level, $message, array $context = array()) {
		// interpolate $message as defined in PSR-3
		$replace = array();
		foreach ($context as $key => $val) {
			// Allows us to dump arrays, objects and exceptions to the log
			$val = $this->normalize($val);
			$replace['{' . $key . '}'] = $val;
		}

		// interpolate replacement values into the message and return
		$message = strtr($message, $replace);

		$this->logger->log(
			$level, $message,
			array(
				'app' => $this->appName
			)
		);

	}

	/**
	 * Converts Objects, Arrays and Exceptions to String
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private function normalize($data) {
		if (null === $data || is_scalar($data)) {
			return $data;
		}

		if (is_array($data) || $data instanceof \Traversable) {
			$normalized = array();
			$count = 1;
			foreach ($data as $key => $value) {
				if ($count >= 1000) {
					$normalized['...'] =
						'Over 1000 items, aborting normalization';
					break;
				}
				$normalized[$key] = $this->normalize($value);
			}

			//return $normalized;
			return $this->toJson($normalized);
		}

		if (is_object($data)) {
			if ($data instanceof \Exception) {
				return $this->normalizeException($data);
			}

			$arrayObject = new \ArrayObject($data);
			$serializedObject = $arrayObject->getArrayCopy();

			return sprintf(
				"[object] (%s: %s)", get_class($data),
				$this->toJson($serializedObject)
			);
		}

		if (is_resource($data)) {
			return '[resource]';
		}

		return '[unknown(' . gettype($data) . ')]';
	}

	/**
	 * Normalises exceptions
	 *
	 * @param \Exception $exception
	 *
	 * @return string
	 */
	private function normalizeException(\Exception $exception) {
		$data = array(
			'class'   => get_class($exception),
			'message' => $exception->getMessage(),
			'file'    => $exception->getFile() . ':' . $exception->getLine(),
		);
		$trace = $exception->getTraceAsString();
		$data['trace'][] = $trace;

		$previous = $exception->getPrevious();
		if ($previous) {
			$data['previous'] = $this->normalizeException($previous);
		}

		return $this->toJson($data);
	}

	/**
	 * JSON encodes data
	 *
	 * @param $data
	 *
	 * @return string
	 */
	private function toJson($data) {
		// suppress json_encode errors since it's twitchy with some inputs
		return @json_encode(
			$data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		);
	}
}