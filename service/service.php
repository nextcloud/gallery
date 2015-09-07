<?php
/**
 * ownCloud - gallery
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Olivier Paroz <owncloud@interfasys.ch>
 *
 * @copyright Olivier Paroz 2014-2015
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
	 * Returns the node matching the given ID
	 *
	 * @param int $nodeId ID of the resource to locate
	 *
	 * @return Node
	 * @throws NotFoundServiceException
	 */
	public function getResourceFromId($nodeId) {
		try {
			$node = $this->environment->getResourceFromId($nodeId);

			// Making extra sure that we can actually do something with the file
			if (!$node->getMimetype() || !$node->isReadable()) {
				throw new NotFoundServiceException("Can't access the file");
			}

			return $node;
		} catch (\Exception $exception) {
			throw new NotFoundServiceException($exception->getMessage());
		}
	}

}
