<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Tiposoli extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_tiposolicitud', $adapter, $databaseSchema, $selectResultPrototype);
    }

    public function getAll() {
        $r = $this->select();
        return $r->toArray();
    }

    public function getPorId($id) {
        $idT = (int) $id;
        $rowset = $this->select(array('id' => $idT));
        $fila = $rowset->current();

        if (!$fila) {
            //throw new \Exception("No hay registros asociados al valor $id");
        }

        return $fila;
    }

    public function agregarNuevo($data = array()) {
        return $this->insert($data);
    }

    public function actualizar($id, $data = array()) {
        return $this->update($data, array('id' => $id));
    }

    public function borrar($id) {
        return $this->delete(array('id' => $id));
    }

    public function getTiposoli() {

        $SQL = "SELECT * "
                . "FROM tbl_tiposolicitud AS d "
                . "ORDER BY d.nombre_ts ASC";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getTblTiposoli() {

        $SQL = "SELECT * "
                . "FROM tbl_tiposolicitud "
                . "ORDER BY nombre_ts ASC";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getExisteTiposoli($id) {

        $SQL = "SELECT * "
                . "FROM tbl_datos_profecionales AS d "
                . "WHERE d.id = $id";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }
       public function getTiposoliInfo($id) {

        $SQL = "select nombre_ts 
                  
                from tbl_tiposolicitud;

           = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
     }
    
    
}

