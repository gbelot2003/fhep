<?php

/*

 * @Nombre    : RolesxObjetos
 * @Author    : Oscar A. Reyes
 * @Copyright : Oscar A. Reyes
 * @Email     : oscareyes3019@gmail.com
 * @Creado el : 25-mar-2022, 08:38:07 PM
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class RolesxObjetos extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_ms_roles_objetos', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function actualizar($id, $id2, $data = array()) {
        return $this->update($data, array('id_rol' => $id, 'id_objeto' => $id2));
    }

    public function borrar($id) {
        return $this->delete(array('id_rol' => $id));
    }

    public function borrarObjeto($id) {
        return $this->delete(array('id_objeto' => $id));
    }

    public function existeRolObjeto($id_rol, $id_objeto) {
        $rowset = $this->select(array('id_rol' => $id_rol, 'id_objeto' => $id_objeto));
        $fila = $rowset->current();
        return $fila;
    }

//    public function getPorIdRol($id_rol) {
//        $idT = (int) $id_rol;
//        $rowset = $this->select(array('id_rol' => $idT));
//        $fila = $rowset->toArray();
//
//        if (!$fila) {
//            //throw new \Exception("No hay registros asociados al valor $id");
//        }
//
//        return $fila;
//    }

    public function getRolesxobjetos() {

        $SQL = "SELECT *, r.nombre_rol, o.objeto, ro.permiso_insercion, ro.permiso_eliminacion, "
                . "ro.permiso_actualizacion, ro.permiso_consultar, ro.fecha_creacion, ro.creado_por, "
                . "ro.id_rol, ro.id_objeto "
                . "FROM tbl_ms_roles_objetos AS ro "
                . "INNER JOIN tbl_ms_roles AS r ON ro.id_rol = r.id "
                . "INNER JOIN tbl_ms_objetos AS o ON ro.id_objeto = o.id ";
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

//    public function getPorIdRol($id_rol) {
//
//        $SQL = "SELECT o.objeto, ro.id_rol, ro.id_objeto, ro.permiso_insercion,  ro.permiso_eliminacion, "
//                . "ro.permiso_actualizacion, ro.permiso_consultar, ro.id, ro.estado  "
//                . "FROM tbl_ms_roles_objetos AS ro INNER JOIN tbl_ms_objetos AS o "
//                . "ON ro.id_objeto = o.id INNER JOIN tbl_ms_roles AS r ON ro.id_rol = r.id  "
//                . "WHERE ro.id_rol = $id_rol "
//                . "AND r.estado = 'Activo'";
//        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);
//
//        return $rs->toArray();
//    }

    public function getPorIdRol($id_rol) {

        $SQL = "SELECT o.objeto, ro.* "
                . "FROM tbl_ms_roles_objetos AS ro INNER JOIN tbl_ms_objetos AS o "
                . "ON ro.id_objeto = o.id INNER JOIN tbl_ms_roles AS r ON ro.id_rol = r.id  "
                . "WHERE ro.id_rol = $id_rol "
                . "AND r.estado = 'Activo'";
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }
    
    
    public function getexisteRol($id) {

        $SQL = "SELECT * "
                . "FROM tbl_ms_roles_objetos AS ro "
                . "WHERE ro.id_rol = $id ";
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getExisteObjeto($id) {

        $SQL = "SELECT *, "
                . "tbl_ms_objetos.objeto AS nombre "
                . "FROM tbl_ms_roles_objetos AS ro "
                . "INNER JOIN tbl_ms_objetos ON ro.id_objeto = tbl_ms_objetos.id  "
                . "WHERE ro.id_objeto = $id ";
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

}
