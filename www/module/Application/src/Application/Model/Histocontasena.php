<?php

/*

 * @Nombre    : Histocontasena
 * @Author    : Erick R. Rodríguez
 * @Copyright : Erick R. Rodríguez
 * @Email     : eramsses@gmail.com
 * @Creado el : 30-may.-2022, 00:52:47
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Histocontasena extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_ms_histo_contra_usuarios', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function actualizar($id , $data = array()) {
        return $this->update($data, array('id' => $id));
    }

    public function borrar($id_usuario, $fecha) {
        return $this->delete(array('id_usuario' => $id_usuario, 'fecha' => $fecha));
    }

    public function getHitoContraUsuario($id) {


        $SQL = "SELECT H.contrasena, H.fecha "
                . "FROM tbl_ms_histo_contra_usuarios AS H "
                . "WHERE H.id_usuario = $id "
                . "ORDER BY H.fecha ASC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

}
