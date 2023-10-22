<?php

/*

 * @Nombre    : EstadoUsuarios
 * @Author    : Oscar A. Reyes
 * @Copyright : Oscar A. Reyes
 * @Email     : oscareyes3019@gmail.com
 * @Creado el : 26-mar-2022, 06:06:41 PM
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class EstadoUsuarios extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('nombre_tabla', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function getEstados() {

        $SQL = "SELECT * "
                . "FROM tbl_estado_usuarios "
                . "ORDER BY tbl_estado_usuarios.nombre ASC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

}
