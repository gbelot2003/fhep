<?php

/*

 * @Nombre    : Medicos
 * @Author    : Oscar A. Reyes
 * @Copyright : Oscar A. Reyes
 * @Email     : oscareyes3019@gmail.com
 * @Creado el : 16-mar-2022, 12:53:22 PM
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Medicos extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_medico', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function getNumMedicos() {

        $SQL = "SELECT count(*) as num_medicos "
                . "FROM tbl_medico "
                . "WHERE tbl_medico.Estado = 1 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getListMedicos() {

        $SQL = "SELECT concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, p.dni, e.nombre_e, m.Estado, m.id AS id_medico "
                . "FROM tbl_personas AS p "
                . "INNER JOIN tbl_medico AS m ON p.id = m.id_persona "
                . "INNER JOIN tbl_especialidades AS e ON m.id_especialidad = e.id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getInfoMedico($id) {

        $SQL = "SELECT concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, p.dni, e.nombre_e, m.Estado, m.id AS id_medico, m.id_especialidad "
                . "FROM tbl_personas AS p "
                . "INNER JOIN tbl_medico AS m ON p.id = m.id_persona "
                . "INNER JOIN tbl_especialidades AS e ON m.id_especialidad = e.id "
                . "WHERE m.id = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getMedicosGeneral() {

        $SQL = "SELECT  concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, m.* "
                . "FROM tbl_medico AS m INNER JOIN tbl_personas AS p ON m.id_persona = p.id "
                . "WHERE m.id_especialidad = 4 "
                . "AND m.Estado = 1 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getValidarMedicoRepetido($id_persona) {

        $SQL = "SELECT * "
                . "FROM tbl_medico AS m "
                . "WHERE m.id_persona = $id_persona ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

}
