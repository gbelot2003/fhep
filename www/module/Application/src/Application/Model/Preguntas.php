<?php

/*

 * @Nombre    : Preguntas
 * @Author    : Oscar A. Reyes
 * @Copyright : Oscar A. Reyes
 * @Email     : oscareyes3019@gmail.com
 * @Creado el : 19-mar-2022, 08:11:55 AM
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Preguntas extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_ms_preguntas', $adapter, $databaseSchema, $selectResultPrototype);
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
        return $this->update($data, array('id_usuario' => $id));
    }

    public function borrar($id) {
        return $this->delete(array('id' => $id));
    }

    public function getPreguntaRespondidas($id_usuario) {

        $SQL = "SELECT p.id AS id_respondida "
                . "FROM tbl_ms_preguntas AS p, tbl_ms_preguntas_x_usuario pu "
                . "WHERE pu.Id_pregunta = p.id "
                . "AND pu.id_usuario = $id_usuario ";
        
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }
    
    public function getPreguntasActivas() {
        
        $SQL = "SELECT * "
                . "FROM tbl_ms_preguntas AS p "
                . "WHERE p.estado = 1 "
                . "ORDER BY p.pregunta ASC ";
        
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

}
