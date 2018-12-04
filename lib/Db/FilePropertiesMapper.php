<?php
namespace OCA\Gallery\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class FilePropertiesMapper extends Mapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'gallery_file_properties');
    }

    /**
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     */
    public function find($id) {
        $sql = 'SELECT * FROM `*PREFIX*gallery_file_properties` ' .
            'WHERE `id` = ?';
        return $this->findEntity($sql, [$id]);
    }

    public function findAll($limit=null, $offset=null) {
        $sql = 'SELECT * FROM `*PREFIX*gallery_file_properties`';
        return $this->findEntities($sql, $limit, $offset);
    }
}
