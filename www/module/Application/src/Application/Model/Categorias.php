<?php



namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Categorias extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_categoria', $adapter, $databaseSchema, $selectResultPrototype);
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

     public function getCategoria(){

        $SQL = "SELECT * "
      . "FROM tbl_categoria AS c "
      . "ORDER BY c.nombre_c ASC";

      $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

      return $rs->toArray();
      } 
      
       public function getExisteCategoria($id){

        $SQL = "SELECT * "
                . "FROM tbl_datos_profecionales AS c "
                . "WHERE c.id_categoria = $id ";

      $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

      return $rs->toArray();
      } 
   
   }
