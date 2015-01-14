<?php
/**
 * ownCloud - galleryplus
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @copyright Olivier Paroz 2015
 * @copyright Jordi Boggiano 2014-2015
 */

namespace OCA\GalleryPlus\Utility;

/**
 * Lets us call the main logger without having to add the context at every
 * request
 *
 * @package OCA\GalleryPlus\Utility
 */
class Normalizer {

	/**
	 * Converts Objects, Arrays and Exceptions to String
	 *
	 * @param $data
	 * @param int $depth
	 *
	 * @return string|array
	 */
	public function normalize($data, $depth = 0) {
		$scalar = $this->normalizeScalar($data);
		if (!is_array($scalar)) {
			return $scalar;
		}
		$decisionArray = [
			$this->normalizeTraversable($data, $depth),
			$this->normalizeObject($data, $depth),
			$this->normalizeResource($data),
		];

		foreach ($decisionArray as $dataType) {
			if ($dataType !== null) {
				return $dataType;
			}
		}

		return '[unknown(' . gettype($data) . ')]';
	}

	/**
	 * Returns various, filtered, scalar elements
	 *
	 * We're returning an array here to detect failure because null is a scalar and so is false
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	private function normalizeScalar($data) {
		if (null === $data || is_scalar($data)) {
			/*// utf8_encode only works for Latin1 so we rely on mbstring
			if (is_string($data)) {
				$data = mb_convert_encoding($data, "UTF-8");
			}*/

			return $data;
		}

		return [];
	}

	/**
	 * Returns an array containing normalized elements
	 *
	 * @param $data
	 * @param int $depth
	 *
	 * @return array|null
	 */
	private function normalizeTraversable($data, $depth = 0) {
		if (is_array($data) || $data instanceof \Traversable) {
			return $this->normalizeTraversableElement($data, $depth);
		}

		return null;
	}

	/**
	 * Converts each element of a traversable variable to String
	 *
	 * @param $data
	 * @param int $depth
	 *
	 * @return array
	 */
	private function normalizeTraversableElement($data, $depth) {
		$maxArrayRecursion = 20;
		$count = 1;
		$normalized = [];
		foreach ($data as $key => $value) {
			if ($count++ >= $maxArrayRecursion) {
				$normalized['...'] =
					'Over ' . $maxArrayRecursion . ' items, aborting normalization';
				break;
			}
			$normalized[$key] = $this->normalize($value, $depth);
		}

		return $normalized;
	}

	/**
	 * Converts an Object to String
	 *
	 * @param mixed $data
	 * @param int $depth
	 *
	 * @return array|null
	 */
	private function normalizeObject($data, $depth) {
		if (is_object($data)) {
			if ($data instanceof \Exception) {
				return $this->normalizeException($data);
			}
			// We don't need to go too deep in the recursion
			$maxObjectRecursion = 2;
			$response = $data;
			$arrayObject = new \ArrayObject($data);
			$serializedObject = $arrayObject->getArrayCopy();
			if ($depth < $maxObjectRecursion) {
				$depth++;
				$response = $this->normalize($serializedObject, $depth);
			}

			// Don't convert to json here as we would double encode
			return [sprintf("[object] (%s)", get_class($data)), $response];
		}

		return null;
	}

	/**
	 * Converts an Exception to String
	 *
	 * @param \Exception $exception
	 *
	 * @return string[]
	 */
	private function normalizeException(\Exception $exception) {
		$data = [
			'class'   => get_class($exception),
			'message' => $exception->getMessage(),
			'code'    => $exception->getCode(),
			'file'    => $exception->getFile() . ':' . $exception->getLine(),
		];
		$trace = $exception->getTraceAsString();
		$data['trace'][] = $trace;

		$previous = $exception->getPrevious();
		if ($previous) {
			$data['previous'] = $this->normalizeException($previous);
		}

		return $data;
	}

	/**
	 * Converts a resource to a String
	 *
	 * @param $data
	 *
	 * @return string|null
	 */
	private function normalizeResource($data) {
		if (is_resource($data)) {
			return "[resource] " . substr((string)$data, 0, 40);
		}

		return null;
	}

}