<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Personas extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_personas', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function getIdReciente($where = []) {

        $rowset = $this->select($where);
        $fila = $rowset->current();
        return $fila['id'];
    }

    public function agregarpNuevo($data = array()) {
        return $this->insert($data);
    }

    public function actualizar($id, $data = array()) {
        return $this->update($data, array('id' => $id));
    }

    public function borrar($id) {
        return $this->delete(array('id' => $id));
    }

    public function getMedicoPorespe($id,$id_sucursal) {

        $SQL = "SELECT m.id AS id_medico, concat( p.nombres, ' ', p.apellidos )as nombre_completo, "
                . "m.id_especialidad AS id_especialidad "
                . "FROM tbl_medico AS m "
                . "INNER JOIN tbl_personas AS p ON m.id_persona = p.id "
                . "INNER JOIN tbl_ms_usuarios AS u ON p.id = u.id_persona  "
                . "WHERE m.id_especialidad = $id "
                . "AND m.Estado = 1  "
                . "AND u.id_sucursal = $id_sucursal "
                . "ORDER BY p.nombres ASC ";
//      . "WHERE tbl_especialidades.estado = 'activo' ";

         $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }
    
    
     public function getInfoPersona($id) {

        $SQL = "SELECT * "
                . "FROM tbl_personas "
                . "WHERE tbl_personas.id = $id "
                . "OR tbl_personas.id_padre = $id ";
//      . "WHERE tbl_especialidades.estado = 'activo' ";

         $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getIdPersona($correo) {

        $SQL = "SELECT * "
                . "FROM tbl_personas "
                . "WHERE tbl_personas.dni = '$correo' ";

         $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }
    
     public function getCantBeneficiarios($id) {

        $SQL = "SELECT *, CONCAT( p.nombres, ' ', p.apellidos ) AS nombre_completo "
                . "FROM tbl_personas AS p "
                . "WHERE p.id_padre = $id ";

         $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }
    
      public function getBeneficiarios($id) {

        $SQL = "SELECT *, a.id AS id_afiliado, CONCAT( p.nombres, ' ', p.apellidos ) AS nombre_completo "
                . "FROM tbl_personas AS p "
                . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                . "WHERE p.id_padre = $id ";

         $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }
    
}
