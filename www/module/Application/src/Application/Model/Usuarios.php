<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Usuarios extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_ms_usuarios', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function getPorUsuario($usuario) {
        $SQL = "SELECT p.correo, p.nombres, p.apellidos, u.usuario, u.id AS id_usuario, u.intentos,"
                . " u.reseteo_clave, u.primer_contra "
                . "FROM tbl_ms_usuarios AS u "
                . "INNER JOIN tbl_personas AS p ON p.id = u.id_persona "
                . "WHERE u.usuario = '$usuario' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function validarPass($usuario, $pass) {
// SELECT DATEDIFF('2017-1-15', '2016-12-31');
        $SQL = "SELECT u.*, DATEDIFF(CURDATE(), u.fecha_vencimiento) AS vencimiento, tbl_ms_roles.estado "
                . "FROM tbl_ms_usuarios AS u "
                . "INNER JOIN tbl_ms_roles ON u.id_rol = tbl_ms_roles.id "
                . "WHERE u.usuario = '$usuario' "
                . "AND u.contrasenia = '$pass' ";

       

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getIdMedico($id) {
        $SQL = "SELECT tbl_medico.id AS id_medico "
                . "FROM tbl_medico "
                . "WHERE tbl_medico.id_persona = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
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

    public function getTblUsuarios() {

        $SQL = "SELECT concat( p.nombres, ' ', p.apellidos ) AS Nombre_Completo, s.nombre_sucursal, tbl_estado_usuarios.nombre, "
                . "p.id AS id_persona, p.correo, u.id AS "
                . "id_usuario, u.usuario, u.estado_u, s.nombre_sucursal "
                . "FROM tbl_ms_usuarios AS u "
                . "INNER JOIN tbl_personas AS p ON u.id_persona = p.id 	"
                . "INNER JOIN tbl_sucursales AS s ON u.id_sucursal = s.Id "
                . "INNER JOIN tbl_estado_usuarios ON u.estado_u = tbl_estado_usuarios.id "
                . "ORDER BY s.nombre_sucursal ASC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getInfousuario($id) {

        $SQL = "SELECT * "
                . "FROM tbl_ms_usuarios "
                . "WHERE tbl_ms_usuarios.id = $id ";
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getExisteRol($id) {

        $SQL = "SELECT * "
                . "FROM tbl_ms_usuarios AS u "
                . "WHERE u.id_rol = $id "
                . "AND u.estado_u = 1  ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getPorEmail($correo) {

        $SQL = "SELECT p.correo, CONCAT(p.nombres, ' ', p.apellidos) AS nombres, u.usuario, u.id AS id_usuario  "
                . "FROM tbl_ms_usuarios AS u "
                . "INNER JOIN tbl_personas AS p ON p.id = u.id_persona "
                . "WHERE p.correo = '$correo' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getEstadosUsuarios() {

        $SQL = "SELECT * "
                . "FROM tbl_estado_usuarios AS e "
                . "WHERE e.estado = 'Activo' "
                . "ORDER BY e.nombre ASC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getExisteCorreoUsuario($usuario, $correo) {

        $SQL = "SELECT * "
                . "FROM tbl_ms_usuarios AS u "
                . "INNER JOIN tbl_personas AS p ON  p.id = u.id_persona "
                . "WHERE p.correo = '$correo' "
                . "OR u.usuario = '$usuario' "
                . "OR p.dni ='$correo' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getInfousuarioEditar($id) {

        $SQL = "SELECT r.nombre_rol, es.nombre, u.estado_u AS id_estado, u.id_rol, p.correo, "
                . "u.usuario, u.contrasenia, p.id AS id_persona, u.id AS id_usuario, s.nombre_sucursal, u.id_sucursal "
                . "FROM tbl_ms_roles AS r "
                . "INNER JOIN tbl_ms_usuarios AS u ON r.id = u.id_rol "
                . "INNER JOIN tbl_estado_usuarios AS es ON u.estado_u = es.id "
                . "INNER JOIN tbl_personas AS p ON u.id_persona = p.id "
                . "INNER JOIN tbl_sucursales AS s ON u.id_sucursal = s.Id  "
                . "WHERE u.id = $id ";
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getValidarUsuario($id) {

        $SQL = "SELECT * "
                . "FROM tbl_ms_usuarios AS u "
                . "WHERE u.id_persona = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }
  public function getExisteUsuario($usuario) {

        $SQL = "SELECT * "
                . "FROM tbl_ms_usuarios "
                . "WHERE tbl_ms_usuarios.usuario = '$usuario' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

      public function getExisteCorreo($correo) {

        $SQL = "SELECT * "
                . "FROM tbl_personas "
                . "WHERE tbl_personas.correo = '$correo' "
                . "OR tbl_personas.dni = '$correo' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }
}
