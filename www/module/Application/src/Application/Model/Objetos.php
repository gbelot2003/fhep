<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Objetos extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_ms_objetos', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function agregarNuevo($data = array()) {
        return $this->insert($data);
    }

    public function actualizar($id, $data = array()) {
        return $this->update($data, array('id' => $id));
    }

    public function borrar($id) {
        return $this->delete(array('id' => $id));
    }

    public function getObjetos() {

        $SQL = "SELECT * "
                . "FROM tbl_ms_objetos AS o "
                . "ORDER BY o.objeto ASC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function gettblObjetos() {

        $SQL = "SELECT * "
                . "FROM tbl_ms_objetos ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getIdobjetoMantenimiento() {

        $SQL = "SELECT o.id, o.objeto, o.tipo_objeto "
                . "FROM tbl_ms_objetos AS o "
                . "WHERE o.objeto = 'Mantenimientos' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }
    
     public function getIdobjetoSeguridad() {

        $SQL = "SELECT o.id, o.objeto, o.tipo_objeto "
                . "FROM tbl_ms_objetos AS o "
                . "WHERE o.objeto = 'Seguridad' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getIdobjetoAfiliados() {

        $SQL = "SELECT o.id, o.objeto, o.tipo_objeto "
                . "FROM tbl_ms_objetos AS o "
                . "WHERE o.objeto = 'Afiliados' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getIdobjetoCitas() {

        $SQL = "SELECT o.id, o.objeto, o.tipo_objeto "
                . "FROM tbl_ms_objetos AS o "
                . "WHERE o.objeto = 'Citas' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }
    
     public function getIdobjetoMedicos() {

        $SQL = "SELECT o.id, o.objeto, o.tipo_objeto "
                . "FROM tbl_ms_objetos AS o "
                . "WHERE o.objeto = 'Medicos' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getIdObjetoLogin() {

        $SQL = "SELECT o.id, o.objeto, o.tipo_objeto "
                . "FROM tbl_ms_objetos AS o "
                . "WHERE o.objeto = 'Login' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getIdObjetoCerrar() {

        $SQL = "SELECT o.id, o.objeto, o.tipo_objeto "
                . "FROM tbl_ms_objetos AS o "
                . "WHERE o.objeto = 'Cerrar sesion' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getIdObjtoUsuarios() {

        $SQL = "SELECT o.id, o.objeto, o.tipo_objeto "
                . "FROM tbl_ms_objetos AS o "
                . "WHERE o.objeto = 'Usuarios' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getInfobjeto($id) {

        $SQL = "SELECT o.id, o.objeto, o.descripcion, o.estado "
                . "FROM tbl_ms_objetos AS o "
                . "WHERE o.id = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getExisteObjeto($id) {

        $SQL = "SELECT * "
                . "FROM tbl_ms_roles_objetos AS ro "
                . "WHERE ro.id_objeto = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

}
