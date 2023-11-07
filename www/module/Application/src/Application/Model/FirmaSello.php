<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class FirmaSello extends TableGateway {

    private $dbAdapter;
    
    public $id;
    public $ID_Usuario;
    public $Firma;
    public $Sello;


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

    public function exchangeArray($data)
     {
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->id_user = (!empty($data['ID_Usuario'])) ? $data['ID_Usuario'] : null;
         $this->firma  = (!empty($data['firma'])) ? $data['firma'] : null;
         $this->sello  = (!empty($data['sello'])) ? $data['sello'] : null;
     }

     public function getAll() {
        $r = $this->select();
        return $r->toArray();
    }
    
    public function save($data = array()) {
        return $this->insert($data);
    }
}
