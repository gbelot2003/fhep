<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;
use Application\Model\Exception;

class Afiliados extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_afiliacion', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function afiNuevo($data = array()) {
        return $this->insert($data);
    }

    public function actualizar($id, $data = array()) {
        return $this->update($data, array('id' => $id));
    }

    public function borrar($id) {
        return $this->delete(array('id' => $id));
    }

    public function existePorIdentidad($identidad) {

        $SQL = "SELECT a.* "
                . "FROM tbl_afiliacion AS a, tbl_personas AS p "
                . "WHERE p.dni = '$identidad' "
                . "AND a.id_persona = p.id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getNombreAfiliado($dni) {

        $SQL = "SELECT concat(p.nombres,' ',p.apellidos) as Nombre_Completo, p.parentesco,  tpa.nombre_ta, a.id "
                . "FROM tbl_afiliacion AS a, tbl_personas AS p, tbl_tip_afiliado AS tpa "
                . "WHERE p.dni = '$dni' "
                . "AND a.id_persona = p.id "
                . "AND a.id_tipafiliado = tpa.id "
                . "AND a.id_status = 1 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getNumAfiliados() {

        $SQL = "SELECT count(*) as num_afiliados "
                . "FROM tbl_afiliacion "
                . "WHERE tbl_afiliacion.id_tipafiliado = 1 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getNumBeneficiarios() {

        $SQL = "SELECT count(*) as num_beneficiarios "
                . "FROM tbl_afiliacion "
                . "WHERE tbl_afiliacion.id_tipafiliado = 2 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function get($dni) {

        $SQL = "SELECT concat(p.nombres,' ',p.apellidos) as Nombre_Completo, tpa.nombre_ta, tpa.id "
                . "FROM tbl_afiliacion AS a, tbl_personas AS p, tbl_tip_afiliado AS tpa "
                . "WHERE p.dni = '$dni' "
                . "AND a.id_persona = p.id "
                . "AND a.id_tipafiliado = tpa.id";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getNombreMedico($id_medico) {


        $SQL = "SELECT CONCAT( tbl_personas.nombres, tbl_personas.apellidos ) AS nombre_medico "
                . "FROM tbl_personas "
                . "INNER JOIN tbl_medico ON tbl_personas.id = tbl_medico.id_persona "
                . "WHERE tbl_medico.id = $id_medico ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getCitamedica() {


        $SQL = "SELECT tbl_cit_medicas.id "
                . "FROM tbl_cit_medicas ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getPacientesinfo($id) {

        $SQL = "SELECT cm.id AS id_cita, p.genero, p.dni AS num_expediente, p.fecha_nacimiento, "
                . "CONCAT( p.nombres, ' ',p.apellidos ) AS nombre_completo, "
                . "a.id AS id_afiliado, ecm.nombre_ecm AS estado_cita, cm.motivo_consulta, "
                . "e.nombre_e AS nombre_especialidad, "
                . "tpa.nombre_ta AS TIPO_DE_AFILIADO, "
                . "cm.fecha_cita, cm.id_medico, "
                . ""
                . "(SELECT CONCAT( p2.nombres, ' ',p2.apellidos ) "
                . "FROM tbl_personas AS p2 "
                . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                . "WHERE m2.id = cm.id_medico ) AS nombre_medico "
                . ""
                . "FROM tbl_personas AS p "
                . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                . "INNER JOIN tbl_cit_medicas AS cm ON a.id = cm.id_afiliado "
                . "INNER JOIN tbl_sta_cit_med AS ecm ON cm.id_estado_cita = ecm.id "
                . "INNER JOIN tbl_tipo_cita AS tc ON cm.id_tipo_cita = tc.id "
                . "INNER JOIN tbl_especialidades AS e ON cm.id_especialidad = e.id "
                . "INNER JOIN tbl_tip_afiliado AS tpa ON a.id_tipafiliado = tpa.id "
                . "WHERE cm.id = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getTblAfiliados() {

        $SQL = "SELECT a.id AS id_afiliado, CONCAT( p.nombres, ' ',p.apellidos ) AS nombre_completo, p.dni, "
                . "t.nombre_ta AS tipo_de_afiliado, p.id AS id_persona, a.id_tipafiliado AS tipo, s.nombre_ea "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON a.id_persona = p.id "
                . "INNER JOIN tbl_tip_afiliado AS t ON a.id_tipafiliado = t.id "
                . "INNER JOIN tbl_sta_afiliado AS s ON a.id_status = s.id  "
                . "WHERE a.id_tipafiliado = 1 "
                . "ORDER BY a.id DESC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }
    
    
    public function getTblExpedientes() {

        $SQL = "SELECT a.id AS id_afiliado, CONCAT( p.nombres, ' ',p.apellidos ) AS nombre_completo, p.dni, "
                . "t.nombre_ta AS tipo_de_afiliado, p.id AS id_persona, a.id_tipafiliado AS tipo "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON a.id_persona = p.id "
                . "INNER JOIN tbl_tip_afiliado AS t ON a.id_tipafiliado = t.id "
                . "ORDER BY a.id DESC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getAfiliadoExiste($id) {

        $SQL = "SELECT p.id AS id_persona, CONCAT( p.nombres, ' ',p.apellidos ) AS nombre_completo , "
                . "est.nombre_ea AS estado "
                . "FROM tbl_personas AS p  "
                . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                . "INNER JOIN tbl_sta_afiliado AS est ON a.id_status = est.id "
                . "WHERE p.id = $id "
                . "AND a.id_status = 1 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);
//if (!$rs ){
//    throw new \Exception("No hay registros asociados al valor $id");
//}
        return $rs->current();
    }

    public function getAfiliadoInfo($id) {

        $SQL = "SELECT p.id AS id_persona, p.nombres, p.apellidos, p.genero, dep.nombre_d AS dep_origen, p.id_tipo_sangre, "
                . "p.dni, p.domicilio_actual, p.telefono,date_format(p.fecha_nacimiento, '%d/%m/%Y') AS fecha_nacimiento, c.nombre_es AS sta_civlil, "
                . "s.nombre_ts AS sangre, p.Nom_contacto_emergencia, pr.n_placa_policial AS placa, "
                . "p.telefono_contacto_emergencia, p.celular, p.correo, tp.nombre_ta AS tipo_afi, ra.nombre_r AS rango, "
                . "ca.nombre_c AS categoria, dp.nombre_dp AS dependencia, "
                . "(SELECT sec2.nombre_d "
                . "FROM tbl_datos_profecionales AS sec1 "
                . "INNER JOIN tbl_departamentos AS sec2 ON sec1.id_departamento = sec2.id "
                . "WHERE sec1.id_persona = $id ) AS asiganacion, "
                . "p.id_estado_civil, p.id_tipo_sangre, p.departamento AS id_dep, "
                . "pr.id_cod_dependencia, pr.id_rango, pr.id_categoria, pr.id_departamento AS id_asignacion "
                . "FROM tbl_personas AS p "
                . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                . "INNER JOIN tbl_departamentos AS dep ON dep.id = p.departamento "
                . "INNER JOIN tbl_tip_afiliado AS tp ON a.id_tipafiliado = tp.id "
                . "INNER JOIN tbl_estado_civil AS c ON p.id_estado_civil = c.id "
                . "INNER JOIN tbl_tipo_sangre AS s ON p.id_tipo_sangre = s.id "
                . "INNER JOIN tbl_datos_profecionales AS pr ON p.id = pr.id_persona "
                . "INNER JOIN tbl_rangos AS ra ON pr.id_rango = ra.id "
                . "INNER JOIN tbl_categoria AS ca ON pr.id_categoria = ca.id "
                . "INNER JOIN tbl_dependencias AS dp ON pr.id_cod_dependencia = dp.id "
                . "WHERE p.id = $id "
                . "AND a.id_status = 1 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);
        
        return $rs->current();
    }

    public function getInfoBeneficiario($id) {

        $SQL = "SELECT p.*, a.id_status, s.nombre_ts AS sangre, ec.nombre_es AS civil, d.nombre_d  "
                . "FROM tbl_personas AS p INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                . "INNER JOIN tbl_tipo_sangre AS s ON p.id_tipo_sangre = s.id "
                . "INNER JOIN tbl_estado_civil AS ec ON p.id_estado_civil = ec.id "
                . "INNER JOIN tbl_departamentos AS d ON p.departamento = d.id   "
                . "WHERE p.id = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getTodosBeneficiarios() {

        $SQL = "SELECT  p.id_padre, t.nombre_ta, CONCAT( p.nombres, ' ',p.apellidos ) AS nombre_completo, p.dni, p.genero "
                . "FROM tbl_personas AS p "
                . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                . "INNER JOIN tbl_tip_afiliado AS t ON a.id_tipafiliado = t.id "
                . "WHERE p.id_padre > 0 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

//    QUERY PARA REPORTE
    public function getInfoAfiliado($id_personas) {

        $SQL = " SELECT	concat( p.nombres, ' ', p.apellidos ) AS Nombre_Completo, "
	."p.genero, dep.nombre_d AS dep_origen, p.dni, p.domicilio_actual, p.telefono, "
	."p.fecha_nacimiento, c.nombre_es AS sta_civlil, s.nombre_ts AS sangre, p.Nom_contacto_emergencia, " 
        ." p.telefono_contacto_emergencia, p.celular, p.correo, tp.nombre_ta AS tipo_afi, ra.nombre_r AS rango, "
	."ca.nombre_c AS categoria, dp.nombre_dp AS dependencia, "
	."(SELECT sec2.nombre_d FROM "
	."tbl_datos_profecionales AS sec1 "
	."INNER JOIN "
	."tbl_departamentos AS sec2 "
	."ON sec1.id_departamento = sec2.id "
	."WHERE sec1.id_persona = $id_personas ) as asiganacion "
	."FROM tbl_personas AS p "
	."INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
	."INNER JOIN tbl_departamentos AS dep ON dep.id = p.departamento "
	."INNER JOIN tbl_tip_afiliado AS tp ON a.id_tipafiliado = tp.id "
	."INNER JOIN tbl_estado_civil AS c ON p.id_estado_civil = c.id "
	."INNER JOIN tbl_tipo_sangre AS s ON p.id_tipo_sangre = s.id "
	."INNER JOIN tbl_datos_profecionales AS pr ON p.id = pr.id_persona "
	."INNER JOIN tbl_rangos AS ra ON pr.id_rango = ra.id "
	."INNER JOIN tbl_categoria AS ca ON pr.id_categoria = ca.id "
	."INNER JOIN tbl_dependencias AS dp ON pr.id_cod_dependencia = dp.id " 
        ."WHERE p.id =  $id_personas "; 

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);
        
        return $rs->current();
    }

    //   FIN QUERY PARA REPORTE
    //    QUERY PARA REPORTE PDF
    public function getPdfAfiliado() {

        $SQL = " SELECT	concat( p.nombres, ' ', p.apellidos ) AS Nombre_Completo,
	p.genero,
	dep.nombre_d AS dep_origen,
	p.dni,
	p.domicilio_actual,
	p.telefono,
	p.fecha_nacimiento,
	c.nombre_es AS sta_civlil,
	s.nombre_ts AS sangre,
	p.Nom_contacto_emergencia,
	p.telefono_contacto_emergencia,
	p.celular,
	p.correo,
	tp.nombre_ta AS tipo_afi,
	ra.nombre_r AS rango,
	ca.nombre_c AS categoria,
	dp.nombre_dp AS dependencia,
	(SELECT sec2.nombre_d FROM
	tbl_datos_profecionales AS sec1
	INNER JOIN
	tbl_departamentos AS sec2
	ON sec1.id_departamento = sec2.id
	WHERE sec1.id_persona = 2) as asiganacion
	FROM tbl_personas AS p
	INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona
	INNER JOIN tbl_departamentos AS dep ON dep.id = p.departamento
	INNER JOIN tbl_tip_afiliado AS tp ON a.id_tipafiliado = tp.id
	INNER JOIN tbl_estado_civil AS c ON p.id_estado_civil = c.id
	INNER JOIN tbl_tipo_sangre AS s ON p.id_tipo_sangre = s.id
	INNER JOIN tbl_datos_profecionales AS pr ON p.id = pr.id_persona
	INNER JOIN tbl_rangos AS ra ON pr.id_rango = ra.id
	INNER JOIN tbl_categoria AS ca ON pr.id_categoria = ca.id
	INNER JOIN tbl_dependencias AS dp ON pr.id_cod_dependencia = dp.id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    //   FIN QUERY PARA REPORTE

    public function getInfoPersona($id) {

        $SQL = "SELECT * "
                . "FROM tbl_personas AS p "
                . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                . "WHERE a.id_persona = $id "
                . "OR p.id_padre = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getTblReporteAfiliados($fi, $ff) {

        $SQL = "SELECT a.id AS id_afiliado, CONCAT( p.nombres, ' ',p.apellidos ) AS nombre_completo, "
                . "t.nombre_ta AS tipo_de_afiliado, p.id AS id_persona, "
                . "a.id_tipafiliado AS tipo,DATE_FORMAT( a.fecha_afiliacion, '%d/%m/%Y' ) AS fecha, "
                . "p.dni AS expediente "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON a.id_persona = p.id "
                . "INNER JOIN tbl_tip_afiliado AS t ON a.id_tipafiliado = t.id "
                . "WHERE a.id_tipafiliado = 1 "
                . "AND DATE_FORMAT( a.fecha_afiliacion, '%Y-%m-%d' ) >= '$fi' "
                . "AND DATE_FORMAT( a.fecha_afiliacion, '%Y-%m-%d' ) <= '$ff' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getInfoAfiliadoB($id) {

        $SQL = "SELECT * "
                . "FROM tbl_afiliacion AS a "
                . "WHERE a.id_persona = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getInfoAfiliadoExpediente($id) {

        $SQL = "SELECT p.*,CONCAT( p.nombres, ' ',p.apellidos ) AS nombre "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON  a.id_persona = p.id "
                . "WHERE a.id = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }
    
     public function getInfoAfiliadoPersona($id) {

        $SQL = "SELECT a.*, a.id AS id_afiliado, p.*,CONCAT( p.nombres, ' ',p.apellidos ) AS nombre "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON  a.id_persona = p.id "
                . "WHERE p.id = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

}
