<?php
namespace OCA\Gallery\Service;

use OCA\Gallery\Db\FileProperties;
use OCA\Gallery\Db\FilePropertiesMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCA\Gallery\Environment\Environment;
use OCP\ILogger;

class FilePropertiesService extends Service {

	public function __construct(
		$appName,
		Environment $environment,
		ILogger $logger,
		FilePropertiesMapper $mapper
	) {
		parent::__construct($appName, $environment, $logger);
		$this->mapper = $mapper;
	}

	public function getModifications($fileId) {
		try {
			$entity = $this->mapper->find($fileId);
			return $entity->getModifications();
		} catch (DoesNotExistException $ex) {
			return "{}";
		}
	}

	public function setModifications($fileId, $modificationsJson) {
		try {
			$entity = $this->mapper->find($fileId);
			$entity->setModifications($modificationsJson);
			$this->mapper->update($entity);
		} catch (DoesNotExistException $ex) {
			$entity = new FileProperties();
			$entity->setId($fileId);
			$entity->setModifications($modificationsJson);
			$this->mapper->insert($entity);
		}
	}
}
