<?php

/*

 * @Nombre    : Preguntasusuarios
 * @Author    : Oscar A. Reyes
 * @Copyright : Oscar A. Reyes
 * @Email     : oscareyes3019@gmail.com
 * @Creado el : 27-mar-2022, 01:08:55 AM
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Preguntasusuarios extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_ms_preguntas_x_usuario', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function actualizar($id, $idpregunta, $data = array()) {
        return $this->update($data, array('id_usuario' => $id, 'Id_pregunta' => $idpregunta));
    }

    public function borrar($id) {
        return $this->delete(array('id' => $id));
    }

    public function getValidarRespuesta($id, $id_pregunta, $respuesta) {

        $SQL = "SELECT pu.respuesta, p.pregunta, pu.id_usuario "
                . "FROM tbl_ms_preguntas_x_usuario AS pu "
                . "INNER JOIN tbl_ms_preguntas AS p ON pu.Id_pregunta = p.id "
                . "WHERE pu.respuesta = '$respuesta' "
                . "AND pu.id_usuario = $id "
                . "AND pu.Id_pregunta = $id_pregunta ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

}
