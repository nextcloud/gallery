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

use OCP\Files\Node;
use OCP\ILogger;

use OCA\Gallery\Environment\Environment;

/**
 * Contains methods which all services will need
 *
 * @package OCA\Gallery\Service
 */
abstract class Service {

	/**
	 * @var string
	 */
	protected $appName;
	/**
	 * @var Environment
	 */
	protected $environment;
	/**
	 * @var ILogger
	 */
	protected $logger;

	/**
	 * Constructor
	 *
	 * @param string $appName
	 * @param Environment $environment
	 * @param ILogger $logger
	 */
	public function __construct(
		$appName,
		Environment $environment,
		ILogger $logger
	) {
		$this->appName = $appName;
		$this->environment = $environment;
		$this->logger = $logger;
	}

	/**
	 * Returns the file matching the given ID
	 *
	 * @param int $nodeId ID of the resource to locate
	 *
	 * @return Node
	 * @throws NotFoundServiceException
	 */
	public function getFile($nodeId) {
		$node = $this->getNode($nodeId);

		if ($node->getType() === 'file') {
			$this->validateNode($node);

			return $node;
		} else {
			throw new NotFoundServiceException("Cannot find a file with this ID");
		}
	}

	/**
	 * Returns the node matching the given ID
	 *
	 * @param int $nodeId ID of the resource to locate
	 *
	 * @return Node
	 * @throws NotFoundServiceException
	 */
	private function getNode($nodeId) {
		try {
			$node = $this->environment->getResourceFromId($nodeId);

			return $node;
		} catch (\Exception $exception) {
			throw new NotFoundServiceException($exception->getMessage());
		}
	}

	/**
	 * Makes extra sure that we can actually do something with the file
	 *
	 * @param Node $node
	 *
	 * @throws NotFoundServiceException
	 */
	private function validateNode($node) {
		if (!$node->getMimetype() || !$node->isReadable()) {
			throw new NotFoundServiceException("Can't access the file");
		}
	}

}
