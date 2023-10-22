<?php

/*

 * @Nombre    : Bitacoras
 * @Author    : Erick Rodriguez
 * @Copyright : Erick Rodriguez
 * @Creado el : 11-mar-2022, 08:49:51 PM
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Bitacoras extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_ms_bitacora', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function gettblBitacoras() {


        $SQL = "SELECT o.objeto,DATE_FORMAT( b.Fecha, '%d/%m/%Y %h:%i %p' ) AS fecha_forma, b.* "
                . "FROM tbl_ms_bitacora AS b "
                . "INNER JOIN tbl_ms_objetos AS o ON b.id_objeto = o.id "
                . "ORDER BY b.id DESC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function gettblBitacorasf($fi, $ff) {


        $SQL = "SELECT o.objeto, b.*, DATE_FORMAT( b.Fecha, '%d/%m/%Y %h:%i %p' ) AS fecha_forma "
                . "FROM tbl_ms_bitacora AS b "
                . "INNER JOIN tbl_ms_objetos AS o ON b.id_objeto = o.id "
                . "WHERE DATE_FORMAT( b.Fecha, '%Y-%m-%d' ) >= '$fi' "
                . "AND DATE_FORMAT( b.Fecha, '%Y-%m-%d' ) <= '$ff' ";
        

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getInfoUsuarrio($id) {


        $SQL = "SELECT * "
                . "FROM tbl_ms_bitacora "
                . "WHERE tbl_ms_bitacora.id_usuario = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }
    
    public function getUltimoHash() {

        $SQL = "SELECT b.hash_data "
                . "FROM tbl_ms_bitacora AS b "
                . "ORDER BY b.id desc "
                . "LIMIT 1";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current()['hash_data'];
    }

}
