<?php

/*

 * @Nombre    : Respuestausuario
 * @Author    : Erick R. Rodríguez
 * @Copyright : Erick R. Rodríguez
 * @Email     : eramsses@gmail.com
 * @Creado el : 19-mar-2022, 11:53:11 PM
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Respuestausuario extends TableGateway {

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
    
    public function getRespuestasUsuario($id_usuario) {
        
        $SQL = "SELECT p.id AS id_pregunta, pu.id_usuario, p.pregunta, pu.respuesta "
                . "FROM tbl_ms_preguntas AS p, tbl_ms_preguntas_x_usuario pu "
                . "WHERE pu.Id_pregunta = p.id "
                . "AND pu.id_usuario = $id_usuario ";
        
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function agregarNuevo($data = array()) {
        return $this->insert($data);
    }

    public function actualizar($id, $data = array()) {
        return $this->update($data, array('id' => $id));
    }

    public function borrar($id_pregunta, $id_usuario) {
        return $this->delete(array('Id_pregunta' => $id_pregunta, 'id_usuario' => $id_usuario));
    }

}
