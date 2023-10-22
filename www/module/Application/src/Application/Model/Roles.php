<?php

/*

 * @Nombre    : Roles
 * @Author    : Erick Rodriguez
 * @Copyright : Erick Rodriguez
 * @Creado el : 11-mar-2022, 11:54:58 PM
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Roles extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_ms_roles', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function getRoles() {

        $SQL = "SELECT * "
                . "FROM tbl_ms_roles AS r "
                . "WHERE r.estado = 'Activo' "
                . "ORDER BY "
                . "r.nombre_rol ASC ";
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getblRoles() {

        $SQL = "SELECT * "
                . "FROM tbl_ms_roles AS r ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getRolExiste($id) {

        $SQL = "SELECT * "
                . "FROM  tbl_ms_roles AS r "
                . "WHERE r.estado = 'activo' AND r.id = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function tipoDeinser($id_ro, $id_objeto) {

        $SQL = "SELECT * "
                . "FROM tbl_ms_roles_objetos AS ro "
                . "WHERE ro.id_rol = $id_ro "
                . "AND ro.id_objeto = $id_objeto ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }
    
    public function getInfoRol($id_rol) {

        $SQL = "SELECT * "
                . "FROM tbl_ms_roles "
                . "WHERE tbl_ms_roles.id = $id_rol ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

}
