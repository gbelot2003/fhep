<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class FirmaSello extends TableGateway {

    private $dbAdapter;

    public function __construct(
        Adapter $adapter = null, 
        $databaseSchema = null, 
        ResultSet $selectResultPrototype = null
    ) {
        $this->dbAdapter = $adapter;
        return parent::__construct(
            'tbl_lab_firma_sello', 
            $adapter, 
            $databaseSchema, 
            $selectResultPrototype
        );
    }

    public function save($data = array()) {
        return $this->insert($data);
    }
}
