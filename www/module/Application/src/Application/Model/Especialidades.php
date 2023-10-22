<?php

/*

 * @Nombre    : Especialidades
 * @Author    : Oscar A. Reyes
 * @Copyright : Oscar A. Reyes
 * @Email     : oscareyes3019@gmail.com
 * @Creado el : 22-mar-2022, 12:44:24 AM
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Especialidades extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_especialidades', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function getEspecialidad() {

        $SQL = "SELECT * "
                . "FROM tbl_especialidades AS e "
                . "WHERE e.estado = 'Activo' "
                . "ORDER BY e.nombre_e ASC ";
//                . "WHERE tbl_especialidades.estado = 'Activo' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getExisteEspecialidad($id) {

        $SQL = "SELECT * "
                . "FROM tbl_medico AS m "
                . "WHERE m.id_especialidad = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

}
