<?php

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;

class Citas extends TableGateway {

    private $dbAdapter;

    public function __construct(Adapter $adapter = null, $databaseSchema = null, ResultSet $selectResultPrototype = null) {
        $this->dbAdapter = $adapter;
        return parent::__construct('tbl_cit_medicas', $adapter, $databaseSchema, $selectResultPrototype);
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

    public function getNumCitas($fecha) {

        $SQL = "SELECT count(*) as num_citas "
                . "FROM tbl_cit_medicas "
                . "WHERE tbl_cit_medicas.fecha_cita LIKE '$fecha' "
                . "AND tbl_cit_medicas.id_estado_cita = 1 ";

//        $SQL = "SELECT count(*) "
//                . "FROM tbl_cit_medicas AS c "
//                . "WHERE tbl_cit_medicas.fecha_cita LIKE '$fecha' "
//                . "AND c.id_estado_cita = 1";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getTblPacientes($fecha, $id_sucursal, $id_rol) {


        if ($id_rol == 1 || $id_rol == 5) {
            $SQL = "SELECT cm.id AS id_cita, p.dni AS num_expediente, "
                    . "CONCAT( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                    . "a.id AS id_afiliado, ecm.nombre_ecm AS estado_cita, "
                    . "e.nombre_e AS nombre_especialidad, "
                    . "tpa.nombre_ta AS TIPO_DE_AFILIADO, "
                    . "cm.fecha_cita, cm.id_medico, "
                    . "DATE_FORMAT(cm.fecha_cita, '%d/%m/%Y %h:%i %p') AS fecha_cita_txt, "
                    . "(SELECT CONCAT( p2.nombres, ' ',p2.apellidos ) "
                    . "FROM tbl_personas AS p2 "
                    . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                    . "WHERE m2.id = cm.id_medico ) AS nombre_medico , su.nombre_sucursal "
                    . ""
                    . "FROM tbl_personas AS p "
                    . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                    . "INNER JOIN tbl_cit_medicas AS cm ON a.id = cm.id_afiliado "
                    . "INNER JOIN tbl_sta_cit_med AS ecm ON cm.id_estado_cita = ecm.id "
                    . "INNER JOIN tbl_tipo_cita AS c ON cm.id_tipo_cita = c.id "
                    . "INNER JOIN tbl_especialidades AS e ON cm.id_especialidad = e.id "
                    . "INNER JOIN tbl_tip_afiliado AS tpa ON a.id_tipafiliado = tpa.id "
                    . "INNER JOIN tbl_sucursales AS su ON cm.id_sucursal = su.Id "
                    . "WHERE cm.fecha_cita LIKE '$fecha' "
                    . "AND cm.id_estado_cita = 1 "
                    . "OR cm.id_estado_cita = 4 "
                    . "AND fecha_cita LIKE '$fecha' "
                    . "ORDER BY su.nombre_sucursal ASC ";
        } else {
            $SQL = "SELECT cm.id AS id_cita, p.dni AS num_expediente, "
                    . "CONCAT( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                    . "a.id AS id_afiliado, ecm.nombre_ecm AS estado_cita, "
                    . "e.nombre_e AS nombre_especialidad, "
                    . "tpa.nombre_ta AS TIPO_DE_AFILIADO, "
                    . "cm.fecha_cita, cm.id_medico, "
                    . "DATE_FORMAT(cm.fecha_cita, '%d/%m/%Y %h:%i %p') AS fecha_cita_txt, "
                    . "(SELECT CONCAT( p2.nombres, ' ',p2.apellidos ) "
                    . "FROM tbl_personas AS p2 "
                    . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                    . "WHERE m2.id = cm.id_medico ) AS nombre_medico "
                    . ""
                    . "FROM tbl_personas AS p "
                    . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                    . "INNER JOIN tbl_cit_medicas AS cm ON a.id = cm.id_afiliado "
                    . "INNER JOIN tbl_sta_cit_med AS ecm ON cm.id_estado_cita = ecm.id "
                    . "INNER JOIN tbl_tipo_cita AS c ON cm.id_tipo_cita = c.id "
                    . "INNER JOIN tbl_especialidades AS e ON cm.id_especialidad = e.id "
                    . "INNER JOIN tbl_tip_afiliado AS tpa ON a.id_tipafiliado = tpa.id "
                    . "WHERE cm.id_sucursal = $id_sucursal "
                    . "AND cm.fecha_cita LIKE '$fecha' "
                    . "AND cm.id_estado_cita = 1 "
                    . "OR cm.id_estado_cita = 4 "
                    . "AND fecha_cita LIKE '$fecha' "
                    . "AND cm.id_sucursal = $id_sucursal ";
        }

//        echo "<pre>";
//        print_r($SQL);
//        exit;

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getTblHistorico($id_suscursal, $id_rol) {



        if ($id_rol == 1 || $id_rol == 5) {

            $SQL = "SELECT c.id AS id_cita, a.id AS id_afiliado, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                    . "DATE_FORMAT( c.fecha_cita, '%d/%m/%Y %h:%i %p' ) AS fecha_cita, "
                    . "c.motivo_consulta, p.id AS id_persona, tbl_sta_cit_med.nombre_ecm AS estado, p.dni, "
                    . "e.nombre_e AS especialidad,  "
                    . "( SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                    . "FROM tbl_personas AS p2 "
                    . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                    . "WHERE m2.id = c.id_medico  ) AS nombre_medico, su.nombre_sucursal  "
                    . "FROM tbl_personas AS p "
                    . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                    . "INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado "
                    . "INNER JOIN tbl_sta_cit_med ON c.id_estado_cita = tbl_sta_cit_med.id 	"
                    . "INNER JOIN tbl_especialidades AS e ON c.id_especialidad = e.id "
                    . "INNER JOIN tbl_sucursales AS su ON c.id_sucursal = su.Id  "
                    . "ORDER BY c.fecha_cita DESC ";
        } else {
            $SQL = "SELECT c.id AS id_cita, a.id AS id_afiliado, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                    . "DATE_FORMAT( c.fecha_cita, '%d/%m/%Y %h:%i %p' ) AS fecha_cita, "
                    . "c.motivo_consulta, p.id AS id_persona, tbl_sta_cit_med.nombre_ecm AS estado, p.dni, "
                    . "e.nombre_e AS especialidad,  "
                    . "( SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                    . "FROM tbl_personas AS p2 "
                    . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                    . "WHERE m2.id = c.id_medico  ) AS nombre_medico "
                    . "FROM tbl_personas AS p "
                    . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                    . "INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado "
                    . "INNER JOIN tbl_sta_cit_med ON c.id_estado_cita = tbl_sta_cit_med.id 	"
                    . "INNER JOIN tbl_especialidades AS e ON c.id_especialidad = e.id "
                    . "WHERE c.id_sucursal = $id_suscursal "
                    . "ORDER BY c.fecha_cita DESC ";
        }
        
        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getCitasCalendario($id_medico = null) {

        $filtro = "";
        if ($id_medico != null) {
            $filtro = "WHERE c.id_medico = $id_medico";
        }

        $SQL = "SELECT c.id, a.id AS id_afiliado, concat( p.nombres, ' ', p.apellidos ) AS title, "
                . "DATE_FORMAT( c.fecha_cita, '%d/%m/%Y %h:%i %p' ) AS fecha, c.tipo_cita AS tipo, "
                . "c.fecha_cita AS start, date_add(c.fecha_cita, interval 30 MINUTE) AS end, "
                . "c.motivo_consulta AS detalle, p.id AS id_persona, tbl_sta_cit_med.nombre_ecm AS estado, p.dni, tbl_sucursales.nombre_sucursal, "
                . "e.nombre_e AS especialidad,  "
                . "( SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                . "FROM tbl_personas AS p2 "
                . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                . "WHERE m2.id = c.id_medico  ) AS nombre_medico "
                . "FROM tbl_personas AS p "
                . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                . "INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado "
                . "INNER JOIN tbl_sta_cit_med ON c.id_estado_cita = tbl_sta_cit_med.id 	"
                . "INNER JOIN tbl_especialidades AS e ON c.id_especialidad = e.id "
                . "INNER JOIN tbl_sucursales ON c.id_sucursal = tbl_sucursales.Id "
                . "$filtro ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getTipocita($id) {

        $SQL = "SELECT * "
                . "FROM tbl_cit_medicas "
                . "WHERE tbl_cit_medicas.id_afiliado = $id "
                . "AND tbl_cit_medicas.id_tipo_cita = 1";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getIdReciente($where = []) {

        $rowset = $this->select($where);
        $fila = $rowset->current();

        return $fila['id'];
    }

    public function getInfoExpdiente($id) {

        $SQL = "SELECT tbl_cit_medicas.id AS id_cita, CONCAT( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                . "tbl_cit_medicas.fecha_cita, tbl_cit_medicas.motivo_consulta, "
                . "tbl_preclinica.saturacion_oxigeno, "
                . "tbl_preclinica.temperatura, tbl_preclinica.presion_arterial, tbl_preclinica.imc_categoria, "
                . "tbl_preclinica.glucometria, tbl_preclinica.talla_cuello, s.nombre_ts, "
                . "tbl_preclinica.talla_abdominal, tbl_preclinica.estatura, tbl_preclinica.peso, "
                . "tbl_preclinica.frecuencia_cardiaca, tbl_preclinica.frecuencia_respiratoria, tbl_preclinica.observaciones, "
                . "tbl_preclinica.imc, p.dni AS num_expediente, p.genero, es.nombre_e AS especialidad, p.fecha_nacimiento, "
                . "( SELECT "
                . "CONCAT( p2.nombres, ' ', p2.apellidos ) "
                . "FROM tbl_medico AS md "
                . "INNER JOIN tbl_cit_medicas AS cm ON md.id = cm.id_medico "
                . "INNER JOIN tbl_personas AS p2 ON md.id_persona = p2.id  "
                . "WHERE cm.id = $id ) AS medico, "
                . "tbl_afiliacion.id AS id_afiliado, "
                . "tbl_preclinica.Id AS id_preclinica, tbl_cit_medicas.id_medico AS id_medico "
                . "FROM tbl_cit_medicas "
                . "INNER JOIN tbl_afiliacion ON tbl_cit_medicas.id_afiliado = tbl_afiliacion.id "
                . "INNER JOIN tbl_personas AS p ON p.id = tbl_afiliacion.id_persona "
                . "INNER JOIN tbl_preclinica ON tbl_afiliacion.id = tbl_preclinica.id_afiliado "
                . "AND tbl_cit_medicas.id = tbl_preclinica.id_cita "
                . "INNER JOIN tbl_especialidades AS es ON tbl_cit_medicas.id_especialidad = es.id "
                . "INNER JOIN tbl_tipo_sangre AS s ON p.id_tipo_sangre = s.id  "
                . "WHERE tbl_cit_medicas.id = $id  "
                . "AND tbl_afiliacion.id_status = 1 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getCitasinfo($id_cita) {



        $SQL = "SELECT
p.id AS id_persona,
c.id_estado_cita,
c.id_afiliado AS id_afiliado,
CONCAT( p.nombres, ' ', p.apellidos ) AS nombre,
p.dni,
tp.nombre_ta AS tipo_afi,
e.nombre_e AS especialidad,
tc.nombre_tc AS tipo_consulta,
c.motivo_consulta,
DATE_FORMAT( c.fecha_cita, '%d/%m/%Y %h:%i %p' ) AS fecha_cita,(
SELECT
CONCAT( p2.nombres, ' ', p2.apellidos )
FROM tbl_medico AS md
INNER JOIN tbl_cit_medicas AS cm ON md.id = cm.id_medico
INNER JOIN tbl_personas AS p2 ON md.id_persona = p2.id
WHERE cm.id = $id_cita) AS nombre_medico
FROM tbl_personas AS p
INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona
INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado
INNER JOIN tbl_especialidades AS e ON c.id_especialidad = e.id
INNER JOIN tbl_tipo_cita AS tc ON c.id_tipo_cita = tc.id
INNER JOIN tbl_tip_afiliado AS tp ON a.id_tipafiliado = tp.id
WHERE c.id = $id_cita";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getinficita($id) {

        $SQL = "SELECT c.* "
                . "FROM tbl_cit_medicas AS c "
                . "WHERE c.id = $id";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function gettblHisrociopaciente() {

        $SQL = "SELECT a.id AS id_afiliado, p.dni AS expediente, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                . "pc.observaciones, ct.nombre_ecm AS estado_cita, c.fecha_cita AS fecha, c.motivo_consulta AS motivo, ex.diagnostico, "
                . "ex.comentario_medico, ex.prescripcion, ex.edad, ex.id_medico, ex.id_cita "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON a.id_persona = p.id "
                . "INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado "
                . "INNER JOIN tbl_preclinica AS pc ON a.id = pc.id_afiliado "
                . "AND c.id = pc.id_cita "
                . "INNER JOIN tbl_sta_cit_med AS ct ON c.id_estado_cita = ct.id "
                . "INNER JOIN tbl_exp_clinico AS ex ON a.id = ex.id_afiliado "
                . "AND c.id = ex.id_cita "
                . "AND pc.Id = ex.id_preclinica "
                . "WHERE ex.id_afiliado = 1 ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    // get para la tabla del medico en la que mira las citas que tiene para hoy 
    public function gettblPacientesMedicos($fecha, $id_medico) {


        if ($id_medico == 0) {

            $SQL = "SELECT c.id AS id_cita, a.id AS id_afiliado, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                    . "DATE_FORMAT( c.fecha_cita, '%d/%m/%Y %h:%i %p' ) AS fecha, c.motivo_consulta, p.id AS id_persona, tbl_sta_cit_med.nombre_ecm AS estado, "
                    . "p.dni, (SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                    . "FROM tbl_personas AS p2 INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                    . "WHERE m2.id = c.id_medico ) AS nombre_medico, "
                    . "c.id_estado_cita  "
                    . "FROM tbl_personas AS p INNER JOIN tbl_afiliacion AS a ON  p.id = a.id_persona "
                    . "INNER JOIN tbl_cit_medicas AS c ON  a.id = c.id_afiliado "
                    . "INNER JOIN tbl_sta_cit_med ON c.id_estado_cita = tbl_sta_cit_med.id "
                    . "WHERE fecha_cita LIKE '$fecha' "
                    . "AND c.id_estado_cita = 1 "
                    . "OR c.id_estado_cita = 2 "
                    . "AND fecha_cita LIKE '$fecha' "
                    . "OR c.id_estado_cita = 4 ";

            $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

            return $rs->toArray();
        } else {
            $SQL = "SELECT c.id AS id_cita, a.id AS id_afiliado, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                    . "DATE_FORMAT( c.fecha_cita, '%d/%m/%Y %h:%i %p' ) AS fecha, c.motivo_consulta, p.id AS id_persona, tbl_sta_cit_med.nombre_ecm AS estado, "
                    . "p.dni, (SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                    . "FROM tbl_personas AS p2 INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                    . "WHERE m2.id = c.id_medico ) AS nombre_medico, "
                    . "c.id_estado_cita  "
                    . "FROM tbl_personas AS p INNER JOIN tbl_afiliacion AS a ON  p.id = a.id_persona "
                    . "INNER JOIN tbl_cit_medicas AS c ON  a.id = c.id_afiliado "
                    . "INNER JOIN tbl_sta_cit_med ON c.id_estado_cita = tbl_sta_cit_med.id "
                    . "WHERE c.id_medico = $id_medico "
                    . "AND fecha_cita LIKE '$fecha' "
                    . "AND c.id_estado_cita = 1 "
                    . "OR c.id_medico = $id_medico "
                    . "AND c.id_estado_cita = 2 "
                    . "AND fecha_cita LIKE '$fecha' "
                    . "OR c.id_estado_cita = 4 "
                    . "AND c.id_medico = $id_medico ";

            $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

            return $rs->toArray();
        }
    }

    public function getTblExpedientePaciente($id) {

        $SQL = "SELECT a.id AS id_afiliado, p.dni AS expediente, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                . "pc.observaciones, ct.nombre_ecm AS estado_cita, DATE_FORMAT( c.fecha_cita, '%d/%m/%Y %h:%i %p' ) AS fecha, c.motivo_consulta AS motivo, ex.diagnostico, "
                . "ex.comentario_medico, ex.prescripcion, ex.edad, ex.id_medico, ex.id_cita, es.nombre_e AS especialidad, "
                . "pc.saturacion_oxigeno, pc.temperatura, pc.presion_arterial, pc.glucometria, pc.talla_cuello, pc.talla_abdominal, pc.estatura, "
                . "pc.peso, pc.frecuencia_cardiaca, pc.frecuencia_respiratoria, pc.imc, pc.imc_categoria, tbl_tipo_sangre.nombre_ts AS tipo_sangre, "
                . "TIMESTAMPDIFF(DAY, dias_reposoi, dias_reposof) AS dias_transcurridos, "
                . "(SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                . "FROM tbl_personas AS p2 "
                . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                . "WHERE m2.id = c.id_medico ) AS nombre_medico "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON a.id_persona = p.id "
                . "INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado "
                . "INNER JOIN tbl_preclinica AS pc ON a.id = pc.id_afiliado "
                . "AND c.id = pc.id_cita "
                . "INNER JOIN tbl_sta_cit_med AS ct ON c.id_estado_cita = ct.id "
                . "INNER JOIN tbl_exp_clinico AS ex ON a.id = ex.id_afiliado "
                . "AND c.id = ex.id_cita "
                . "AND pc.Id = ex.id_preclinica "
                . "INNER JOIN tbl_especialidades AS es ON c.id_especialidad = es.id "
                . "INNER JOIN tbl_tipo_sangre ON p.id_tipo_sangre = tbl_tipo_sangre.id "
                . "WHERE ex.id_afiliado = $id "
                . "ORDER BY ex.fec_consulta DESC ";

//        echo "<pre>";
//        print_r($SQL);
//        exit;

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getTblExpedientePacientePdf($id) {

        $SQL = "SELECT a.id AS id_afiliado, p.dni AS expediente, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                . "pc.observaciones, ct.nombre_ecm AS estado_cita, DATE_FORMAT( ex.fec_consulta, '%d/%m/%Y %h:%i %p' ) AS fecha, c.motivo_consulta AS motivo, ex.diagnostico, "
                . "ex.comentario_medico, ex.prescripcion, ex.edad, ex.id_medico, ex.id_cita, es.nombre_e AS especialidad, "
                . "pc.saturacion_oxigeno, pc.temperatura, pc.presion_arterial, pc.glucometria, pc.talla_cuello, pc.talla_abdominal, pc.estatura, "
                . "pc.peso, pc.frecuencia_cardiaca, pc.frecuencia_respiratoria, pc.imc, pc.imc_categoria, tbl_tipo_sangre.nombre_ts AS tipo_sangre, "
                . "DATE_FORMAT( ex.dias_reposoi, '%d/%m/%Y' ) AS fechai, DATE_FORMAT( ex.dias_reposof, '%d/%m/%Y' ) AS fechaf, "
                . "ex.examenes_lab, ex.examenes_esp, ex.referencias, "
                . "(SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                . "FROM tbl_personas AS p2 "
                . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                . "WHERE m2.id = c.id_medico ) AS nombre_medico "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON a.id_persona = p.id "
                . "INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado "
                . "INNER JOIN tbl_preclinica AS pc ON a.id = pc.id_afiliado "
                . "AND c.id = pc.id_cita "
                . "INNER JOIN tbl_sta_cit_med AS ct ON c.id_estado_cita = ct.id "
                . "INNER JOIN tbl_exp_clinico AS ex ON a.id = ex.id_afiliado "
                . "AND c.id = ex.id_cita "
                . "AND pc.Id = ex.id_preclinica "
                . "INNER JOIN tbl_especialidades AS es ON c.id_especialidad = es.id "
                . "INNER JOIN tbl_tipo_sangre ON p.id_tipo_sangre = tbl_tipo_sangre.id "
                . "WHERE ex.id_afiliado = $id "
                . "ORDER BY ex.fec_consulta ASC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getTblExpedientePacientePdfFecha($id, $fi, $ff) {

        $SQL = "SELECT a.id AS id_afiliado, p.dni AS expediente, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                . "pc.observaciones, ct.nombre_ecm AS estado_cita, DATE_FORMAT( ex.fec_consulta, '%d/%m/%Y %h:%i %p' ) AS fecha, c.motivo_consulta AS motivo, ex.diagnostico, "
                . "ex.comentario_medico, ex.prescripcion, ex.edad, ex.id_medico, ex.id_cita, es.nombre_e AS especialidad, "
                . "pc.saturacion_oxigeno, pc.temperatura, pc.presion_arterial, pc.glucometria, pc.talla_cuello, pc.talla_abdominal, pc.estatura, "
                . "pc.peso, pc.frecuencia_cardiaca, pc.frecuencia_respiratoria, pc.imc, pc.imc_categoria, tbl_tipo_sangre.nombre_ts AS tipo_sangre, "
                . "DATE_FORMAT( ex.dias_reposoi, '%d/%m/%Y' ) AS fechai, DATE_FORMAT( ex.dias_reposof, '%d/%m/%Y' ) AS fechaf, "
                . "ex.examenes_esp, ex.referencias, ex.examenes_lab, "
                . "(SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                . "FROM tbl_personas AS p2 "
                . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                . "WHERE m2.id = c.id_medico ) AS nombre_medico "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON a.id_persona = p.id "
                . "INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado "
                . "INNER JOIN tbl_preclinica AS pc ON a.id = pc.id_afiliado "
                . "AND c.id = pc.id_cita "
                . "INNER JOIN tbl_sta_cit_med AS ct ON c.id_estado_cita = ct.id "
                . "INNER JOIN tbl_exp_clinico AS ex ON a.id = ex.id_afiliado "
                . "AND c.id = ex.id_cita "
                . "AND pc.Id = ex.id_preclinica "
                . "INNER JOIN tbl_especialidades AS es ON c.id_especialidad = es.id "
                . "INNER JOIN tbl_tipo_sangre ON p.id_tipo_sangre = tbl_tipo_sangre.id "
                . "WHERE ex.id_afiliado = $id "
                . "AND DATE_FORMAT( ex.fec_consulta, '%Y-%m-%d' ) >= '$fi' "
                . "AND DATE_FORMAT( ex.fec_consulta, '%Y-%m-%d' ) <= '$ff' "
                . "ORDER BY ex.fec_consulta ASC ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getExpedientePorCita($id) {

        $SQL = "SELECT a.id AS id_afiliado, p.dni AS expediente, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                . "pc.observaciones, ct.nombre_ecm AS estado_cita, c.fecha_cita AS fecha, c.motivo_consulta AS motivo, ex.diagnostico, "
                . "ex.comentario_medico, ex.prescripcion, ex.edad, ex.id_medico, ex.id_cita, es.nombre_e AS especialidad, p.genero,  "
                . "pc.saturacion_oxigeno, pc.temperatura, pc.presion_arterial, pc.glucometria, pc.talla_cuello, pc.talla_abdominal, pc.estatura, "
                . "pc.peso, pc.frecuencia_cardiaca, pc.frecuencia_respiratoria, pc.imc, pc.imc_categoria, tbl_tipo_sangre.nombre_ts AS tipo_sangre, "
                . "ex.dias_reposoi, ex.dias_reposof, ex.examenes_lab, ex.examenes_esp, ex.referencias, "
                . "(SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                . "FROM tbl_personas AS p2 "
                . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                . "WHERE m2.id = c.id_medico ) AS nombre_medico "
                . "FROM tbl_afiliacion AS a "
                . "INNER JOIN tbl_personas AS p ON a.id_persona = p.id "
                . "INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado "
                . "INNER JOIN tbl_preclinica AS pc ON a.id = pc.id_afiliado "
                . "AND c.id = pc.id_cita "
                . "INNER JOIN tbl_sta_cit_med AS ct ON c.id_estado_cita = ct.id "
                . "INNER JOIN tbl_exp_clinico AS ex ON a.id = ex.id_afiliado "
                . "AND c.id = ex.id_cita "
                . "AND pc.Id = ex.id_preclinica "
                . "INNER JOIN tbl_especialidades AS es ON c.id_especialidad = es.id "
                . "INNER JOIN tbl_tipo_sangre ON p.id_tipo_sangre = tbl_tipo_sangre.id "
                . "WHERE ex.id_cita = $id ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getTblReporteCitas($fi, $ff) {

        $SQL = "SELECT c.id AS id_cita, a.id AS id_afiliado, concat( p.nombres, ' ', p.apellidos ) AS nombre_completo, "
                . "DATE_FORMAT( c.fecha_cita, '%d/%m/%Y %h:%i %p' ) AS fecha_cita, "
                . "c.motivo_consulta, p.id AS id_persona, tbl_sta_cit_med.nombre_ecm AS estado, p.dni, "
                . "e.nombre_e AS especialidad,  "
                . "( SELECT CONCAT( p2.nombres, ' ', p2.apellidos ) "
                . "FROM tbl_personas AS p2 "
                . "INNER JOIN tbl_medico AS m2 ON m2.id_persona = p2.id "
                . "WHERE m2.id = c.id_medico  ) AS nombre_medico "
                . "FROM tbl_personas AS p "
                . "INNER JOIN tbl_afiliacion AS a ON p.id = a.id_persona "
                . "INNER JOIN tbl_cit_medicas AS c ON a.id = c.id_afiliado "
                . "INNER JOIN tbl_sta_cit_med ON c.id_estado_cita = tbl_sta_cit_med.id 	"
                . "INNER JOIN tbl_especialidades AS e ON c.id_especialidad = e.id  "
                . "WHERE DATE_FORMAT( c.fecha_cita, '%Y-%m-%d' ) >= '$fi' "
                . "AND DATE_FORMAT( c.fecha_cita, '%Y-%m-%d' ) <= '$ff' ";
//. "WHERE cm.id_sucursal = $id_suscursal "

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getValidarCitas($id) {

        $SQL = "SELECT * "
                . "FROM tbl_cit_medicas AS c "
                . "WHERE c.id_afiliado = $id";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->toArray();
    }

    public function getCamtCitas($id, $fecha) {

        $SQL = "SELECT COUNT(id_medico) AS cant_citas "
                . "FROM tbl_cit_medicas AS c "
                . "WHERE c.id_medico = $id  "
                . "AND c.fecha_cita LIKE '$fecha%' ";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

    public function getPermisoHoy($id_afiliado, $id_medico, $fecha) {

        $SQL = "SELECT * FROM tbl_cit_medicas AS c "
                . "WHERE c.id_afiliado = $id_afiliado "
                . "AND c.id_estado_cita = 2 "
                . "AND c.fecha_cita LIKE '$fecha%' "
                . "AND c.id_medico = $id_medico";

        $rs = $this->dbAdapter->query($SQL, Adapter::QUERY_MODE_EXECUTE);

        return $rs->current();
    }

}
