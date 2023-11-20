<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select; 

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
         $this->Firma  = (!empty($data['Firma'])) ? $data['Firma'] : null;
         $this->Sello  = (!empty($data['Sello'])) ? $data['Sello'] : null;
     }

     public function getAll() 
     {
        $r = $this->select(function (Select $select){
            $select->join('tbl_ms_usuarios', 'tbl_ms_usuarios.id = tbl_lab_firma_sello.ID_Usuario', array('usuario'));
        });

        return $r->toArray();
    }

    public function getById($idT) 
    {
        $id = (int) $idT;
        $rowset = $this->select(array('id' => $id));
        $fila = $rowset->current();

        if (!$fila) {
            //throw new \Exception("No hay registros asociados al valor $id");
        }

        return $fila;
    }
    
    public function updateData($data = array(), $id)
    {
         return $this->update($data, array('id' => $id));
    }

    public function save($data = array()) {
        return $this->insert($data);
    }
}
