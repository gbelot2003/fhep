<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Afiliados;
use Application\Model\Citas;
use Application\Model\Preclinicas;
use Application\Model\Expedientes;
use Application\Model\Estadoscitas;
use Application\Model\Bitacoras;
use Application\Model\Objetos;
use Application\Extras\Utilidades\Bitacora;
//Para el Excel
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Texto;

/* use Application\Model\Mimodelo; */

class PacientesController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function pacientesAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function preclinicaAction() {

        $objeto = 'Medicos';
        $permiso = 'permiso_triaje';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/triaje");
        }


        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $idc = $this->params()->fromRoute("id", null);
        $preclinicas_model = new Preclinicas($this->dbAdapter);
        $afiliados_model = new Afiliados($this->dbAdapter);
        $citas_model = new Citas($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            $datosFormularios = $this->request->getPost()->toArray();
            $info_cita = $afiliados_model->getPacientesinfo($idc);
            $info_cita['fecha_cita'] = date("d-m-Y", strtotime($info_cita['fecha_cita']));

            $datos_preclinica = ['id_afiliado' => $info_cita['id_afiliado'], 'id_cita' => $info_cita['id_cita']
                , 'presion_arterial' => $datosFormularios['presion_arterial'], 'temperatura' => $datosFormularios['temperatura']
                , 'glucometria' => $datosFormularios['glucometria'], 'frecuencia_respiratoria' => $datosFormularios['frecuencia_respiratoria'], 'saturacion_oxigeno' => $datosFormularios['saturacion_oxigeno']
                , 'frecuencia_cardiaca' => $datosFormularios['frecuencia_cardiaca'], 'peso' => $datosFormularios['peso']
                , 'estatura' => $datosFormularios['estatura'], 'imc' => $datosFormularios['imc'], 'talla_abdominal' => $datosFormularios['talla_abdominal']
                , 'talla_cuello' => $datosFormularios['talla_cuello'], 'observaciones' => $datosFormularios['observaciones'],
                'imc_categoria' => $datosFormularios['imc_categoria']];

            $id_cita = ['id' => $info_cita['id_cita']];
            $estado = ['id_estado_cita' => $datosFormularios['id_estado_cita_']];

            unset($datosFormularios);

            if ($preclinicas_model->agregarNuevo($datos_preclinica)) {

                $citas_model->actualizar($id_cita, $estado);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMedicos();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Preclina',
                    'descripcion' => "Se realizo la preclinica del paciente {$info_cita['nombre_completo']}"
                    . " con fecha de cita {$info_cita['fecha_cita']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Preclinica Guardada!', 'texto' => "La  preclinica fue creado exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/triaje");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar la Preclinica, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/triaje");
            }
        } else {
            //Normal GET
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $idc = $this->params()->fromRoute("id", null);
            $afiliados_model = new Afiliados($this->dbAdapter);

            $info_cita = $afiliados_model->getPacientesinfo($idc);

            if (!$info_cita) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Cita no encontrada, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/triaje");
            }
            $fecha_nacimiento = $info_cita['fecha_nacimiento'];

            $array_edad = $this->obtenerEdadDetallada($fecha_nacimiento);

            $info_cita['edad'] = "{$array_edad['anios']} años con {$array_edad['meses']} meses";

            $vista = new ViewModel(array('citapersona' => $info_cita)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarpreclinicaAction() {
        $objeto = 'Medicos';
        $permiso = 'permiso_triaje';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/triaje");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $id = $this->params()->fromRoute("id", null);
        $preclinicas_model = new Preclinicas($this->dbAdapter);
        $afiliados_model = new Afiliados($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            $datosFormularios = $this->request->getPost()->toArray();

            $id_preclinica = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($preclinicas_model->actualizar($id_preclinica, $datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Preclinica Editada!', 'texto' => "La  preclinica fue editada exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al editar la Preclinica, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            }
        } else {
            //Normal GET

            $info_cita = $afiliados_model->getPacientesinfo($id);

            $info_preclinica = $preclinicas_model->getInfoPreclinica($id);

            $fecha_nacimiento = $info_cita['fecha_nacimiento'];

            $array_edad = $this->obtenerEdadDetallada($fecha_nacimiento);

            $info_cita['edad'] = "{$array_edad['anios']} años con {$array_edad['meses']} meses";

//            echo "<pre>";
//            print_r($info_cita);
//            exit;

            $vista = new ViewModel(array('citapersona' => $info_cita, 'preclinica' => $info_preclinica)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function triajeAction() {

        $objeto = 'Medicos';
        $permiso = 'permiso_triaje';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $estadosc_model = new Estadoscitas($this->dbAdapter);
        $citas_model = new Citas($this->dbAdapter);
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            $datosFormularios = $this->request->getPost()->toArray();

            $id = ['id' => $datosFormularios['id_cita']];

//            echo "<pre>";
//            print_r($datosFormularios);
//            exit;


            unset($datosFormularios['id_cita']);

            if ($datosFormularios['id_estado_cita'] != 4) {

                unset($datosFormularios['fecha_cita']);

                $citas_model->actualizar($id, $datosFormularios);
                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita actualizada!', 'texto' => "La cita el estado cambio exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/triaje");
            } else {
                $citas_model->actualizar($id, $datosFormularios);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita actualizada!', 'texto' => "La cita el estado cambio exitosamente.");

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/triaje");
            }
        } else {
            //Normal GET

            $estados = $estadosc_model->getEstadoscitas();

            $vista = new ViewModel(array('estado' => $estados)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablapacientesAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $citas_model = new Citas($this->dbAdapter);

        $id_suscursal = $_SESSION['auth']['id_sucursal'];
        
        $id_rol = $_SESSION['auth']['id_rol'];

        $fecha_actual = date("Y-m-d%");

        $pacientes = $citas_model->getTblPacientes($fecha_actual, $id_suscursal, $id_rol);

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        $grid = new \EditableGrid();

        if (!$pacientes) {
            $grid->addColumn('nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('opciones', '', 'html', NULL, false, 'id');
        
            
        } elseif ($id_rol == 1 || $id_rol == 5 ) {
            // create a new EditableGrid object
            $grid->addColumn('id_afiliado', 'Cód. Afiliado', 'string', NULL, false);
            $grid->addColumn('num_expediente', 'N° Expediente', 'string', NULL, false);
            $grid->addColumn('nombre_completo', 'Nombre Completo', 'string', NULL, false);
            $grid->addColumn('nombre_especialidad', 'Especialidad', 'string', NULL, false);
            $grid->addColumn('nombre_medico', 'Médico', 'string', NULL, false);
            $grid->addColumn('estado_cita', 'Estado Cita', 'string', NULL, false);
            $grid->addColumn('fecha_cita_txt', 'Fecha de consulta', 'date', NULL, false);
            $grid->addColumn('nombre_sucursal', 'Sucursal', 'string', NULL, false);

            $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_cita');
        }else{
            $grid->addColumn('id_afiliado', 'Cód. Afiliado', 'string', NULL, false);
            $grid->addColumn('num_expediente', 'N° Expediente', 'string', NULL, false);
            $grid->addColumn('nombre_completo', 'Nombre Completo', 'string', NULL, false);
            $grid->addColumn('nombre_especialidad', 'Especialidad', 'string', NULL, false);
            $grid->addColumn('nombre_medico', 'Médico', 'string', NULL, false);
            $grid->addColumn('estado_cita', 'Estado Cita', 'string', NULL, false);
            $grid->addColumn('fecha_cita_txt', 'Fecha de consulta', 'date', NULL, false);

            $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_cita');
            
        }
            
        $grid->renderXML($pacientes);
        exit;
    }

    public function histoexpedientepdfAction() {

        $objeto = 'Medicos';
        $permiso = 'permiso_reportes';

        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $citas_model = new Citas($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id_afiliado = $datosFormularios['id_afiliado'];

            $todo = isset($datosFormularios['todo']);
            //RECUPERAR LA INFORMACION DEL REPORTE


            if ($todo) {

                $datos_pdf = $citas_model->getTblExpedientePacientePdf($id_afiliado);
            } else {

                $fi = $datosFormularios['fi'];
                $ff = $datosFormularios['ff'];

                $datos_pdf = $citas_model->getTblExpedientePacientePdfFecha($id_afiliado, $fi, $ff);
            }

            foreach ($datos_pdf as $fila) {

                $nombre = $fila['nombre_completo'];
                $expediente = $fila['expediente'];
                $sangre = $fila['tipo_sangre'];
            }

            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('P', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Expediente De Afiliado');
            $pdf->SetSubject('ficha');
            $pdf->SetKeywords('ficha');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "FHEP";
            $linea_auxiliar = "  ";
            $subtitulo1 = "";

            $pdf->SetHeaderData($nombreLogo, $ancho_logo, $nombreLogoderecho, $ancho_logo_derecho, "$titulo_encabezado", "$subtitulo\n$linea_auxiliar", array(0, 0, 0), array(0, 64, 128));
            $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

// set header y footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margenes se puede cambiar por enteros recomendado 15 en los lados y 30 en la parte de arriba
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks habilita la creacion de nueva pagina de forma automatica
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
            if (file_exists(dirname(__FILE__) . '/../Extras/Pdf/lang/spa.php')) {
                require_once(dirname(__FILE__) . '/../Extras/Pdf/lang/spa.php');
                $fechaPiePaginaPDF = date('d/m/Y H:i:s');

                $pdf->setLanguageArray($l);
            }

// ---------------------------------------------------------
// set default font subsetting mode
            $pdf->setFontSubsetting(true);

// Set font dejavusans helvetica courier times
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
            $pdf->SetFont('dejavusans', '', 7, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
            $pdf->AddPage();
            $margen = $pdf->getMargins();
            $m = $pdf->getPageWidth() - ($margen['left'] + $margen['right']);

            //Contenido
            $pdf->SetFont('dejavusans', 'B', 2, '', true);
            $pdf->Cell($m, 0, "", 0, 0, 'C', 0);
            $pdf->Ln();

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetDrawColor(0, 0, 128);
            $pdf->SetLineWidth(0);
            $pdf->SetFont('', 'B');

            $pdf->SetFont('dejavusans', 'BI', 14, '', true);

            $pdf->SetFont('dejavusans', '', 12, '', false);

            $fs = "13px";
            $fst = "14px";
            $lh = "12px";
            $lht = "7px";

            $tituloHTML = "<br><div style=\"text-align: center; font-family:Helvetica ; font-size: 20px; line-height: 6px;\">"
                    . "<b>Expediente Medico</b>"
                    . "</div>";

            $contenidoHTML = "<div style=\"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                    . "<hr>"
                    . "<div style=\"text-align: center; font-family:Helvetica; font-size: {$fst}; line-height: {$lh};\">"
                    . "<b>Información General</b>"
                    . "</div>"
                    . "<hr>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                    . "Nombre Completo:<b> $nombre </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                    . "Identidad:<b> $expediente </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                    . "Tipo Sangre:<b> $sangre </b>"
                    . "</div>"
                    . "<hr>";

            $pdf->writeHTML($tituloHTML, true, false, true, false, '');
            $pdf->writeHTML($contenidoHTML, true, false, true, false, '');

            $c = 1;
            foreach ($datos_pdf as $ben) {
                $historial = "<br><div style=\"text-align: justify; font-family:Helvetica; font-size: {$fst}; line-height: {$lh};\">"
                        . "{$c}-) Fecha De La Cita: <b>{$ben['fecha']}</b>"
                        . "<br><br>"
                        . "Nombre Del Medico: <b>{$ben['nombre_medico']}</b>"
                        . "<br><br>"
                        . "Especialidad: <b>{$ben['especialidad']}</b>"
                        . "</div>"
                        . "<hr>"
                        . "<div style=\"text-align:center; font-family:Helvetica; font-size: {$fst}; line-height: {$lht};\">"
                        . "<b>Datos De Preclinica </b>"
                        . "</div>"
                        . "<div style=\"text-align:justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                        . "Presion Arterial: <b>{$ben['presion_arterial']} </b>"
                        . "Temperatura: <b>{$ben['temperatura']} </b>"
                        . "Glucometria: <b>{$ben['Glucometria']} </b>"
                        . "Frec Respiratoria: <b>{$ben['frecuencia_respiratoria']} </b>"
                        . "Saturación O2: <b>{$ben['saturacion_oxigeno']} </b>"
                        . "Frec Cardiaca: <b>{$ben['frecuencia_cardiaca']} </b>"
                        . "Peso: <b>{$ben['peso']} </b>"
                        . "Estatura: <b>{$ben['estatura']} </b>"
                        . "Imc: <b>{$ben['imc']} </b>"
                        . "Categoria Imc: <b>{$ben['imc_categoria']} </b>"
                        . "Talla abdominal: <b>{$ben['talla_abdominal']} </b>"
                        . "Talla cuello: <b>{$ben['talla_cuello']} </b>"
                        . "</div>"
                        . "<hr>"
                        . "<div style=\"text-align:center; font-family:Helvetica; font-size: {$fst}; line-height: {$lht};\">"
                        . "<b>Comentario De Preclinica </b>"
                        . "</div>"
                        . "<div style = \"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                        . "{$ben['observaciones']}"
                        . "</div>"
                        . "<hr>"
                        . "<div style=\"text-align:center; font-family:Helvetica; font-size: {$fst}; line-height: {$lht};\">"
                        . "<b>Comentario Medico </b>"
                        . "</div>"
                        . "<div style = \"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                        . "{$ben['comentario_medico']}"
                        . "</div>"
                        . "<hr>"
                        . "<div style=\"text-align:center; font-family:Helvetica; font-size: {$fst}; line-height: {$lht};\">"
                        . "<b>Diagnostico </b>"
                        . "</div>"
                        . "<div style = \"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                        . "{$ben['diagnostico']}"
                        . "</div>"
                        . "<hr>"
                        . "<div style=\"text-align:center; font-family:Helvetica; font-size: {$fst}; line-height: {$lht};\">"
                        . "<b>Tratamiento</b>"
                        . "</div>"
                        . "<div style = \"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                        . "{$ben['prescripcion']}"
                        . "</div>"
                        . "<hr>"
                        . "<div style=\"text-align:center; font-family:Helvetica; font-size: {$fst}; line-height: {$lht};\">"
                        . "<b>Dias De Incapacidad</b>"
                        . "</div>"
                        . "<div style = \"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                        . "Desde: <b>{$ben['fechai']} </b>"
                        . "Hasta: <b>{$ben['fechaf']} </b>"
                        . "</div>"
                        . "<hr>"
                        . "<div style=\"text-align:center; font-family:Helvetica; font-size: {$fst}; line-height: {$lht};\">"
                        . "<b>Examenes Laboratoriales</b>"
                        . "</div>"
                        . "<div style = \"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                        . "{$ben['examenes_lab']}"
                        . "</div>"
                        . "<hr>"
                        . "<div style=\"text-align:center; font-family:Helvetica; font-size: {$fst}; line-height: {$lht};\">"
                        . "<b>Examenes Especiales</b>"
                        . "</div>"
                        . "<div style = \"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                        . "{$ben['examenes_esp']}"
                        . "</div>"
                        . "<hr>"
                        . "<div style=\"text-align:center; font-family:Helvetica; font-size: {$fst}; line-height: {$lht};\">"
                        . "<b>Referencias/Citas</b>"
                        . "</div>"
                        . "<div style = \"text-align: justify; font-family:Helvetica; font-size: {$fs}; line-height: {$lh};\">"
                        . "{$ben['referencias']}"
                        . "</div>"
                        . "<hr>";

                if ($c >= 2) {
                    $pdf->AddPage();
                }
                $pdf->writeHTML($historial, true, false, true, false, '');

                $c++;
            }


            $contenidoHTML = "</div>";
            $firmasHTML = "<div style=\"text-align: center; font-family: helvetica; font-size: {$fs}; line-height: 10px;  margin-top: 100px;\"><p>"
//                    . "__________________________________________<br>"
//                    . "$tituloFirmante $nombreFirmante<br>"
//                    . "$cargoFirmante<br>"
                    . "</p></div>";

            // output the HTML content

            $pdf->writeHTML($contenidoHTML, true, false, true, false, '');

            $pdf->Ln();
            $pdf->Cell($m, 0, '', '');
            $pdf->Ln();

            $pdf->writeHTML($firmasHTML, true, false, true, false, '');

            \ob_clean();
            // Cerrar enviar documento PDF , la  I (i)= lo muestra en pantalla D = lo descarga a la maquina del cliente
            $pdf->Output("Expediente Medico " . '.pdf', 'I');

            //                     GUARDAR A BITACORA

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $id_objetos = $objetos_model->getIdobjetoMedicos();
            $DateAndTime = date('Y-m-d H:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Descarga De PDF Del Historial Medico ',
                'descripcion' => "Se descargo un pdf con la informacion del historial medico del paciente {$nombre} con "
                . "numero de expediente {$expediente}"];

            $utl_bitacora->guardarBitacora($bitacora);
            exit;
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function histoexpedienteExcelAction() {

        $objeto = 'Medicos';
        $permiso = 'permiso_reportes';

        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $citas_model = new Citas($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id_afiliado = $datosFormularios['id_afiliado'];

            $todo = isset($datosFormularios['todo']);
            //RECUPERAR LA INFORMACION DEL REPORTE
            $datos_excel = [];

            if ($todo) {

                $datos_excel = $citas_model->getTblExpedientePacientePdf($id_afiliado);
            } else {

                $fi = $datosFormularios['fi'];
                $ff = $datosFormularios['ff'];

                $datos_excel = $citas_model->getTblExpedientePacientePdfFecha($id_afiliado, $fi, $ff);
            }

            foreach ($datos_excel as $fila) {

                $nombre = $fila['nombre_completo'];
                $expediente = $fila['expediente'];
            }


//            echo "<pre>";
//            print_r($datos_excel);
//            exit;

            $total_filas = \count($datos_excel);

            // FIN PASO 1
            //PASO 2 -> VARIABLES PARA LA CREACION Y CONTROL DEL EXCEL
            $txt_fecha = \date('d-m-Y_H-i-s');
            $titulo = "Fecha de creacion $txt_fecha";
            $nombre_archivo = "expediente N° $expediente $txt_fecha.xlsx"; //NOMBRE DEL ARCHIVO
            //Encabezados de las columnas
            $titulos_columnas = ['N°', 'Fecha de consulta', 'Medico', 'Especialidad', 'Saturacion De Oxigeno', 'Temperatura', 'Precion Arterial',
                'Glucometria', 'Talla Cuello', 'Talla Abdominal', 'Estatura', 'peso', 'Frecuencia Cardiaca', 'Frecuencia Respiratoria', 'IMC',
                'Categoria IMC', 'Tipo De Sangre', 'Diagnostico', 'Comentario', 'Prescripcion', 'Dias De Incapacidad I', 'Dias De Incapacidad F', 
                'Examenes Laboratoriales', 'Examenes Especiales', 'Referencias/Citas'];

            //INFORMACIÓN DEL ENCABEZADO
            $encabezado_linea_1 = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $encabezado_linea_2 = "FHEP";
            $encabezado_linea_3 = "EXPEDIENTE MEDICO DEL PACIENTE $nombre";
            $encabezado_linea_4 = "#numero de expediente $expediente";

            $ultima_columna = "Y";
            $num_fila_titulos = 5;
            $num_fila_inicio_info = 6;  //Numero de fila donde se va a comenzar a rellenar la información
            // FIN PASO 2
            //INSTANCIAR LA LIBRERÍA DEL EXCEL
            //Instanciar el excel
            require_once(dirname(__FILE__) . '/../Extras/Excel/PHPExcel.php');
            $excel = new \PHPExcel();

            //Estilos para usar
            $estilo = new EstilosExcel("Arial", "AC"); // HAY 3 TEMAS AC, CH y PRE
            //$estiloTotales = $estilo->getEstiloExcel("totales");
            $estilo_info_light = $estilo->getEstiloExcel("informacionImpar");
            $estilo_info_dark = $estilo->getEstiloExcel("informacionPar");
            $estiloTotalesTxt = $estilo->getEstiloExcel("totalesTxt");
            $estiloTitulo = $estilo->getEstiloExcel("encabezados");
            $estiloTituloColumnas = $estilo->getEstiloExcel("tituloColumnas");
            $estiloFirmas = $estilo->getEstiloExcel("firmasSinLinea");

            $utilidad_texto = new Texto();

            // Se asignan las propiedades del libro
            $excel->getProperties()->setCreator("Sistema FHEP") // Nombre del autor
                    ->setLastModifiedBy("Sistema FHEP") //Ultimo usuario que lo modificó
                    ->setTitle("$titulo") // Titulo
                    ->setSubject("$titulo") //Asunto
                    ->setDescription("$titulo") //Descripción
                    ->setKeywords("$titulo") //Etiquetas
                    ->setCategory("Reportes excel"); //Categorias
            //Pie de pagina numero y texto a la derecha
            $excel->getActiveSheet()->getHeaderFooter()->setOddFooter("&P/&N&R $titulo ");
            //Al imprimir son las filas que se repiten como encabezado
            $excel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 19);

            $letrasCols = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            $cols = \count($titulos_columnas);

            \ini_set('memory_limit', '-1');
            // Se combinan las celdas A1 hasta $letraFinal, para colocar ahí el titulo del reporte
            $excel->setActiveSheetIndex(0)
                    ->mergeCells('A1:' . $ultima_columna . '1')
                    ->mergeCells('A2:' . $ultima_columna . '2')
                    ->mergeCells('A3:' . $ultima_columna . '3')
                    ->mergeCells('A4:' . $ultima_columna . '4');

            $excel->setActiveSheetIndex(0)
                    ->setCellValue('A1', $encabezado_linea_1)
                    ->setCellValue('A2', $encabezado_linea_2)
                    ->setCellValue('A3', $encabezado_linea_3)
                    ->setCellValue('A4', $encabezado_linea_4);

            //Aplicar estilo a titulos
            $excel->getActiveSheet()->getStyle("A1:A4")->applyFromArray($estiloTitulo);

            //Insertar imagen del logo $letraFinal
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Logo');
            $objDrawing->setDescription('Logo');
            $objDrawing->setPath($_SESSION['directorioBase'] . "/imagenespdf/logo.png");

            $objDrawing->setCoordinates('A1');
            $objDrawing->setHeight(80);
            $objDrawing->setWidth(80);
            $objDrawing->setWorksheet($excel->getActiveSheet());

            $objDrawing2 = new \PHPExcel_Worksheet_Drawing();
            $objDrawing2->setName('Logo');
            $objDrawing2->setDescription('Logo');
            $objDrawing2->setPath($_SESSION['directorioBase'] . "/imagenespdf/_blank.png");

            $objDrawing2->setCoordinates($ultima_columna . '1');
            $objDrawing2->setHeight(55);
            $objDrawing2->setWidth(55);
            $objDrawing2->setWorksheet($excel->getActiveSheet());

            //Agregar los titulos de las columnas
            $i = 0;
            $k = 0;
            $pref = "";
            while ($i < $cols) {
                for ($j = 0; $j < 26; $j++) {
                    \set_time_limit(3000);
                    if ($i < $cols) {
                        $letra = $pref . $letrasCols[$j];
                        $excel->setActiveSheetIndex(0)
                                ->setCellValue($letra . "$num_fila_titulos", $titulos_columnas[$i]);
                        //Setear ancho automatico
                        $excel->setActiveSheetIndex(0)->getColumnDimension($letra)->setAutoSize(TRUE);
                    }
                    $i++;
                }
                $pref = $letrasCols[$k];
                $k++;
            }

//            echo "<pre>";
//            print_r($datos_excel);
//            exit;
            //PASO 3 -> LLENAR LA INFORMACIÓN DEL EXCEL
            $f = $num_fila_inicio_info; //Numero de fila donde se va a comenzar a rellenar
            $filaInicio = $f;
            $cont = 1;

            //Formato para fecha en casos necesarios
            $formato = "dd/mm/yyyy hh:mm AM/PM";
            //$formato = "dd/mm/yyyy";
            foreach ($datos_excel as $fila) {

                //Corregir textos
//                $nombre_completo = $utilidad_texto->fixTexto($fila['nombre_completo']);
//                $num_expediente = $fila['num_expediente'];


                $especialidad = $utilidad_texto->fixTexto($fila['especialidad']);
                $nombre_medico = $utilidad_texto->fixTexto($fila['nombre_medico']);
                $disagnostico = $utilidad_texto->fixTexto($fila['diagnostico']);
                $prescripcion = $utilidad_texto->fixTexto($fila['prescripcion']);
                $comentario = $utilidad_texto->fixTexto($fila['comentario_medico']);
                $examenes_lab = $utilidad_texto->fixTexto($fila['examenes_lab']);
                $examenes_esp = $utilidad_texto->fixTexto($fila['examenes_esp']);
                $referencias = $utilidad_texto->fixTexto($fila['referencias']);
                $fecha_cita = $fila['fecha'];
                $fechai = $fila['fechai'];
                $fechaf = $fila['fechaf'];

                $saturacion_oxigeno = $fila['saturacion_oxigeno'];
                $temperatura = $fila['temperatura'];
                $presion_arterial = $fila['presion_arterial'];
                $glucometria = $fila['glucometria'];
                $talla_cuello = $fila['talla_cuello'];
                $talla_abdominal = $fila['talla_abdominal'];
                $estatura = $fila['estatura'];
                $peso = $fila['peso'];
                $frecuencia_cardiaca = $fila['frecuencia_cardiaca'];
                $frecuencia_respiratoria = $fila['frecuencia_respiratoria'];
                $imc = $fila['imc'];
                $imc_categoria = $fila['imc_categoria'];
                $tipo_sangre = $fila['tipo_sangre'];

//                $preclinica = 'preclinica';
//                $saturacion_oxigenot = 'Saturacion De Oxigeno';
//                $temperaturat = 'Teperatura';
//                $presion_arterialt ='Precion Arterial';
//                $glucometriat ='Glucometria';
//                $talla_cuellot = 'Talla de Cuello';
//                $talla_abdominalt = 'Talla Abdominal'; 
                //Insertar la información el la celda correspondiente

                $excel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $f, $cont)
                        ->setCellValueExplicit('B' . $f, $fecha_cita, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('C' . $f, $nombre_medico, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('D' . $f, $especialidad, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('E' . $f, $saturacion_oxigeno, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('F' . $f, $temperatura, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('G' . $f, $presion_arterial, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('H' . $f, $glucometria, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('I' . $f, $talla_cuello, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('J' . $f, $talla_abdominal, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('K' . $f, $estatura, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('L' . $f, $peso, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('M' . $f, $frecuencia_cardiaca, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('N' . $f, $frecuencia_respiratoria, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('O' . $f, $imc, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('P' . $f, $imc_categoria, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('Q' . $f, $tipo_sangre, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('R' . $f, $disagnostico, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('S' . $f, $comentario, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('T' . $f, $prescripcion, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('U' . $f, $fechai, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('V' . $f, $fechaf, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('W' . $f, $examenes_lab, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('X' . $f, $examenes_esp, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('Y' . $f, $referencias, \PHPExcel_Cell_DataType::TYPE_STRING);

                $f++;
                $cont++;

//               
            }

            //Dar formato de fecha 
//            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
//                $excel->getSheet(0)->getStyle('B' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
//                        ->getNumberFormat()->setFormatCode($formato);
//            }
            // FIN PASO 3
            //Aplicar los estilos
            //Estilo de títulos en encabezados
            $excel->getActiveSheet()->getStyle("A$num_fila_titulos:" . $ultima_columna . "$num_fila_titulos")->applyFromArray($estiloTituloColumnas);

            //Colores alternados en las filas de contenido
            $p = $num_fila_inicio_info % 2;

            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                \set_time_limit(3000);
                if ($j % 2 == $p) {
                    $excel->getActiveSheet()->getStyle("A$j:$ultima_columna" . "$j")->applyFromArray($estilo_info_light);
                } else {
                    $excel->getActiveSheet()->getStyle("A$j:$ultima_columna" . "$j")->applyFromArray($estilo_info_dark);
                }
            }

            $excel->setActiveSheetIndex(0)->getColumnDimension("A:$ultima_columna")->setAutoSize(TRUE);

            /*
             * ####### PARAMETROS ESPECIALES ########## 
             */
            //Agregar el filtros
            $excel->getActiveSheet()->setAutoFilter("A$num_fila_titulos:$ultima_columna" . "A$num_fila_titulos");

            // Zoom
            $excel->getActiveSheet()->getSheetView()->setZoomScale(70);

            // Inmovilizar paneles de los titulos
            $excel->getActiveSheet(0)->freezePaneByColumnAndRow(4, 7);

            //Poner el nombre a la página
            $excel->getActiveSheet()->setTitle("EXPEDIENTE MEDICO");

            //Seguridad del documento
//            $excel->getActiveSheet()->getProtection()->setPassword('miContrasenia');
//            $excel->getActiveSheet()->getProtection()->setSheet(true);
//            $excel->getActiveSheet()->getProtection()->setSort(true);
//            $excel->getActiveSheet()->getProtection()->setInsertRows(true);
//            $excel->getActiveSheet()->getProtection()->setFormatCells(true);

            $objWriter = new \PHPExcel_Writer_Excel2007($excel);
            $objWriter->setOffice2003Compatibility(true);
            $objWriter->save($nombre_archivo);

            // redirect output to client browser
            \header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            \header("Content-Length: " . filesize($nombre_archivo));
            \header('Content-Disposition: attachment;filename="' . $nombre_archivo . '"');
            \header('Cache-Control: max-age=0');

            \ob_clean();
            \flush();
            \readfile($nombre_archivo);
            if (\file_exists($nombre_archivo)) {
                \unlink($nombre_archivo);
            }

            //                     GUARDAR A BITACORA

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $id_objetos = $objetos_model->getIdobjetoMedicos();
            $DateAndTime = date('Y-m-d H:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Descarga De Excel Del Historial Medico ',
                'descripcion' => "Se descargo un archivo de excel con la informacion del historial medico del paciente {$nombre} con "
                . "numero de expediente {$expediente}"];

            $utl_bitacora->guardarBitacora($bitacora);

            exit;
        } else {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function exportpdftriajeAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);

            $id_suscursal = $_SESSION['auth']['id_sucursal'];
            
            $id_rol = $_SESSION['auth']['id_rol'];

            //RECUPERAR LA INFORMACION DEL REPORTE

            $DateAndTime = date('Y-m-d%');
            
            $pacientes = $citas_model->getTblPacientes($DateAndTime,$id_suscursal,$id_rol);

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de triaje');
            $pdf->SetSubject('Triaje');
            $pdf->SetKeywords('Triaje');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nPACIENTES TRIAJE";
//            $linea_auxiliar = "  ";
//            $subtitulo1 = "";

            $pdf->SetHeaderData($nombreLogo, $ancho_logo, $nombreLogoderecho, $ancho_logo_derecho, "$titulo_encabezado", "$subtitulo\n$linea_auxiliar", array(0, 0, 0), array(0, 64, 128));
            $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

// set header y footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margenes se puede cambiar por enteros recomendado 15 en los lados y 30 en la parte de arriba
            $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks habilita la creacion de nueva pagina de forma automatica
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
            if (file_exists(dirname(__FILE__) . '/../Extras/Pdf/lang/spa.php')) {
                require_once(dirname(__FILE__) . '/../Extras/Pdf/lang/spa.php');
                $fechaPiePaginaPDF = date('d/m/Y H:i:s');

                $pdf->setLanguageArray($l);
            }

// ---------------------------------------------------------
// set default font subsetting mode
            $pdf->setFontSubsetting(true);

// Set font dejavusans helvetica courier times
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
            $pdf->SetFont('dejavusans', '', 7, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
            $pdf->AddPage();
            $margen = $pdf->getMargins();
            $m = $pdf->getPageWidth() - ($margen['left'] + $margen['right']);

            //Contenido
            $pdf->SetFont('dejavusans', 'B', 2, '', true);
            $pdf->Cell($m, 0, "", 0, 0, 'C', 0);
            $pdf->Ln();

            $pdf->SetFillColor(255, 255, 255);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetDrawColor(0, 0, 128);
            $pdf->SetLineWidth(0);
            $pdf->SetFont('', 'B');

            // Header
            $header = ['N°', 'Afiliado', 'Expediente', 'Nombre Completo', 'Especialidad', 'Médico', 'Estado', 'Fecha'];
            $w = array($m * .04, $m * .07, $m * .14, $m * .21, $m * .12, $m * .14, $m * .12, $m * 0.16);

            $pdf->SetFont('dejavusans', 'B', 9, '', true);
            $num_headers = \count($header);
            for ($i = 0; $i < $num_headers; ++$i) {
                $pdf->Cell($w[$i], 4, $header[$i], 1, 0, 'C', 1);
            }
            $pdf->Ln();

            // Color and font restoration
            $pdf->SetFillColor(224, 235, 255); /* Color de relleno de filas */
            $pdf->SetTextColor(0); /* Color de texto */

            $pdf->SetFillColor(224, 235, 255); /* Color de relleno de filas */
            $pdf->SetTextColor(0); /* Color de texto */
            $pdf->SetFont('');

            $h = date("H:i:s");
            $s = strtotime($h);
            $_SESSION['tiempo_sesion'] = $s;

            $pdf->SetFont('dejavusans', '', 9, '', true);

            $fill = 0;
            $cont = 1;

            foreach ($pacientes as $fila) {

                //Corregir textos
                $nombre_completo = $utilidad_texto->fixTexto($fila['nombre_completo']);

                $id_afiliado = $fila['id_afiliado'];
                $num_expediente = $fila['num_expediente'];
                $nombre_especialidad = $utilidad_texto->fixTexto($fila['nombre_especialidad']);
                $nombre_medico = $utilidad_texto->fixTexto($fila['nombre_medico']);
                $estado_cita = $utilidad_texto->fixTexto($fila['estado_cita']);
                $fecha_cita = $fila['fecha_cita_txt'];

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $id_afiliado, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[2], 4.2, $num_expediente, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[3], 4.2, $nombre_completo, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[4], 4.2, $nombre_especialidad, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[5], 4.2, $nombre_medico, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[6], 4.2, $estado_cita, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[7], 4.2, $fecha_cita, 'LR', 0, 'L', $fill);

                $pdf->Ln();
                $fill = !$fill;
                $cont++;
            }

            $pdf->Cell($m, 0, '', 'T');
            $pdf->Ln();

            $pdf->Ln();
            $pdf->Cell($m, 4.2, "==========    FIN DEL REPORTE    ==========", '', 0, 'C', false);
            $pdf->Cell($m, 0, '', '');
            $pdf->Ln();

            \ob_clean();
            // Cerrar enviar documento PDF , la  I (i)= lo muestra en pantalla D = lo descarga a la maquina del cliente
            $pdf->Output("PACIENTES TRIAJE" . '.pdf', 'I');
            exit;
        }
    }

    public function exportexceltriajeAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            //Normal GET
            //PASO 1 -> RECUPERAR LA INFORMACIÓN QUE SE VA A EXPORTAR
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);

             $id_suscursal = $_SESSION['auth']['id_sucursal'];
            
            $id_rol = $_SESSION['auth']['id_rol'];

            //RECUPERAR LA INFORMACION DEL REPORTE

            $DateAndTime = date('Y-m-d%');
            
            $datos_excel = $citas_model->getTblPacientes($DateAndTime,$id_suscursal,$id_rol);

            $total_filas = \count($datos_excel);

            // FIN PASO 1
            //PASO 2 -> VARIABLES PARA LA CREACION Y CONTROL DEL EXCEL
            $txt_fecha = \date('d-m-Y_H-i-s');
            $titulo = "PACIENTES TRIAJE $txt_fecha";
            $nombre_archivo = "PACIENTES TRIAJE $txt_fecha.xlsx"; //NOMBRE DEL ARCHIVO
            //Encabezados de las columnas
            $titulos_columnas = ['N°', 'Cód. Afiliado', 'N° Expediente', 'Nombre Completo', 'Especialidad', 'Médico', 'Estado Cita', 'Fecha de consulta'];

            //INFORMACIÓN DEL ENCABEZADO
            $encabezado_linea_1 = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $encabezado_linea_2 = "FHEP";
            $encabezado_linea_3 = "";
            $encabezado_linea_4 = "PACIENTES EN TRIAJE";

            $ultima_columna = "H";
            $num_fila_titulos = 6;
            $num_fila_inicio_info = 7;  //Numero de fila donde se va a comenzar a rellenar la información
            // FIN PASO 2
            //INSTANCIAR LA LIBRERÍA DEL EXCEL
            //Instanciar el excel
            require_once(dirname(__FILE__) . '/../Extras/Excel/PHPExcel.php');
            $excel = new \PHPExcel();

            //Estilos para usar
            $estilo = new EstilosExcel("Arial", "PRE"); // HAY 3 TEMAS AC, CH y PRE
            //$estiloTotales = $estilo->getEstiloExcel("totales");
            $estilo_info_light = $estilo->getEstiloExcel("informacionImpar");
            $estilo_info_dark = $estilo->getEstiloExcel("informacionPar");
            $estiloTotalesTxt = $estilo->getEstiloExcel("totalesTxt");
            $estiloTitulo = $estilo->getEstiloExcel("encabezados");
            $estiloTituloColumnas = $estilo->getEstiloExcel("tituloColumnas");
            $estiloFirmas = $estilo->getEstiloExcel("firmasSinLinea");

            $utilidad_texto = new Texto();

            // Se asignan las propiedades del libro
            $excel->getProperties()->setCreator("Sistema FHEP") // Nombre del autor
                    ->setLastModifiedBy("Sistema FHEP") //Ultimo usuario que lo modificó
                    ->setTitle("$titulo") // Titulo
                    ->setSubject("$titulo") //Asunto
                    ->setDescription("$titulo") //Descripción
                    ->setKeywords("$titulo") //Etiquetas
                    ->setCategory("Reportes excel"); //Categorias
            //Pie de pagina numero y texto a la derecha
            $excel->getActiveSheet()->getHeaderFooter()->setOddFooter("&P/&N&R $titulo ");
            //Al imprimir son las filas que se repiten como encabezado
            $excel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 7);

            $letrasCols = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');

            $cols = \count($titulos_columnas);

            \ini_set('memory_limit', '-1');
            // Se combinan las celdas A1 hasta $letraFinal, para colocar ahí el titulo del reporte
            $excel->setActiveSheetIndex(0)
                    ->mergeCells('A1:' . $ultima_columna . '1')
                    ->mergeCells('A2:' . $ultima_columna . '2')
                    ->mergeCells('A3:' . $ultima_columna . '3')
                    ->mergeCells('A4:' . $ultima_columna . '4');

            $excel->setActiveSheetIndex(0)
                    ->setCellValue('A1', $encabezado_linea_1)
                    ->setCellValue('A2', $encabezado_linea_2)
                    ->setCellValue('A3', $encabezado_linea_3)
                    ->setCellValue('A4', $encabezado_linea_4);

            //Aplicar estilo a titulos
            $excel->getActiveSheet()->getStyle("A1:A4")->applyFromArray($estiloTitulo);

            //Insertar imagen del logo $letraFinal
            $objDrawing = new \PHPExcel_Worksheet_Drawing();
            $objDrawing->setName('Logo');
            $objDrawing->setDescription('Logo');
            $objDrawing->setPath($_SESSION['directorioBase'] . "/imagenespdf/logo.png");

            $objDrawing->setCoordinates('A1');
            $objDrawing->setHeight(80);
            $objDrawing->setWidth(80);
            $objDrawing->setWorksheet($excel->getActiveSheet());

            $objDrawing2 = new \PHPExcel_Worksheet_Drawing();
            $objDrawing2->setName('Logo');
            $objDrawing2->setDescription('Logo');
            $objDrawing2->setPath($_SESSION['directorioBase'] . "/imagenespdf/_blank.png");

            $objDrawing2->setCoordinates($ultima_columna . '1');
            $objDrawing2->setHeight(55);
            $objDrawing2->setWidth(55);
            $objDrawing2->setWorksheet($excel->getActiveSheet());

            //Agregar los titulos de las columnas
            $i = 0;
            $k = 0;
            $pref = "";
            while ($i < $cols) {
                for ($j = 0; $j < 26; $j++) {
                    \set_time_limit(3000);
                    if ($i < $cols) {
                        $letra = $pref . $letrasCols[$j];
                        $excel->setActiveSheetIndex(0)
                                ->setCellValue($letra . "$num_fila_titulos", $titulos_columnas[$i]);
                        //Setear ancho automatico
                        $excel->setActiveSheetIndex(0)->getColumnDimension($letra)->setAutoSize(TRUE);
                    }
                    $i++;
                }
                $pref = $letrasCols[$k];
                $k++;
            }

            //PASO 3 -> LLENAR LA INFORMACIÓN DEL EXCEL
            $f = $num_fila_inicio_info; //Numero de fila donde se va a comenzar a rellenar
            $filaInicio = $f;
            $cont = 1;

            //Formato para fecha en casos necesarios
            $formato = "dd/mm/yyyy hh:mm AM/PM";
            //$formato = "dd/mm/yyyy";
            foreach ($datos_excel as $fila) {




                //Corregir textos
                $nombre_completo = $utilidad_texto->fixTexto($fila['nombre_completo']);

                $id_afiliado = $fila['id_afiliado'];
                $num_expediente = $fila['num_expediente'];
                $nombre_especialidad = $utilidad_texto->fixTexto($fila['nombre_especialidad']);
                $nombre_medico = $utilidad_texto->fixTexto($fila['nombre_medico']);
                $estado_cita = $utilidad_texto->fixTexto($fila['estado_cita']);
                $fecha_cita = $fila['fecha_cita'];

                //Insertar la información el la celda correspondiente
                $excel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $f, $cont)
                        ->setCellValueExplicit('B' . $f, $id_afiliado, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('C' . $f, $num_expediente, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('D' . $f, $nombre_completo, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('E' . $f, $nombre_especialidad, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('F' . $f, $nombre_medico, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('G' . $f, $estado_cita, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('H' . $f, \PHPExcel_Shared_Date::PHPToExcel(new \DateTime($fecha_cita)));

                $f++;
                $cont++;
            }




            //Dar formato de fecha 
            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                $excel->getSheet(0)->getStyle('H' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
                        ->getNumberFormat()->setFormatCode($formato);
            }



            // FIN PASO 3
            //Aplicar los estilos
            //Estilo de títulos en encabezados
            $excel->getActiveSheet()->getStyle("A$num_fila_titulos:" . $ultima_columna . "$num_fila_titulos")->applyFromArray($estiloTituloColumnas);

            //Colores alternados en las filas de contenido
            $p = $num_fila_inicio_info % 2;

            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                \set_time_limit(3000);
                if ($j % 2 == $p) {
                    $excel->getActiveSheet()->getStyle("A$j:$ultima_columna" . "$j")->applyFromArray($estilo_info_light);
                } else {
                    $excel->getActiveSheet()->getStyle("A$j:$ultima_columna" . "$j")->applyFromArray($estilo_info_dark);
                }
            }

            $excel->setActiveSheetIndex(0)->getColumnDimension("A:$ultima_columna")->setAutoSize(TRUE);

            /*
             * ####### PARAMETROS ESPECIALES ########## 
             */
            //Agregar el filtros
            $excel->getActiveSheet()->setAutoFilter("A$num_fila_titulos:$ultima_columna" . "A$num_fila_titulos");

            // Zoom
            $excel->getActiveSheet()->getSheetView()->setZoomScale(110);

            // Inmovilizar paneles de los titulos
            $excel->getActiveSheet(0)->freezePaneByColumnAndRow(4, 7);

            //Poner el nombre a la página
            $excel->getActiveSheet()->setTitle("PACIENTES TRIAJE");

            //Seguridad del documento
//            $excel->getActiveSheet()->getProtection()->setPassword('miContrasenia');
//            $excel->getActiveSheet()->getProtection()->setSheet(true);
//            $excel->getActiveSheet()->getProtection()->setSort(true);
//            $excel->getActiveSheet()->getProtection()->setInsertRows(true);
//            $excel->getActiveSheet()->getProtection()->setFormatCells(true);

            $objWriter = new \PHPExcel_Writer_Excel2007($excel);
            $objWriter->setOffice2003Compatibility(true);
            $objWriter->save($nombre_archivo);

            // redirect output to client browser
            \header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            \header("Content-Length: " . filesize($nombre_archivo));
            \header('Content-Disposition: attachment;filename="' . $nombre_archivo . '"');
            \header('Cache-Control: max-age=0');

            \ob_clean();
            \flush();
            \readfile($nombre_archivo);
            if (\file_exists($nombre_archivo)) {
                \unlink($nombre_archivo);
            }
            exit;
        }
    }

    public function historicopacientesAction() {

        $objeto = 'Medicos';
        $permiso = 'permiso_expedientes';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function expedientehistoricoaAction() {

        $objeto = 'Medicos';
        $permiso = 'permiso_expedientes';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $afiliados_model = new Afiliados($this->dbAdapter);
            $id = $this->params()->fromRoute("id", null);

            $info_afiliado = $afiliados_model->getInfoAfiliadoExpediente($id);

            $vista = new ViewModel(['id_afiliado' => $id, 'info_afiliado' => $info_afiliado]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function expedientehistoricoAction() {

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $citas_model = new Citas($this->dbAdapter);

        $id = $this->params()->fromRoute("id", null);

        $info_cita = $citas_model->getCitasinfo($id);

        $id_estado = $info_cita['id_estado_cita'];

        if ($id_estado != 2) {

            //                     GUARDAR A BITACORA

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $id_objetos = $objetos_model->getIdobjetoMedicos();
            $DateAndTime = date('Y-m-d H:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Acceso Denegado',
                'descripcion' => "Se intento accder el expediente del afiliado {$info_cita['nombre']} con # de expediente {$info_cita['dni']}"
                . "con fecha de expediente {$info_cita['fecha_cita']}"];

            $utl_bitacora->guardarBitacora($bitacora);

            $_SESSION['mnsError'] = array('titulo' => 'Acceso Denegado !', 'texto' => "No tiene permitido ver el expediente.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $objeto = 'Medicos';
        $permiso = 'permiso_consultar';

        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);

            $id = $this->params()->fromRoute("id", null);

            $info_expediente = $citas_model->getCitasinfo($id);

            $vista = new ViewModel(['id_' => $id, 'info_citas' => $info_expediente]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettblhistoricopacienteaAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
//            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
//          
            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);

            $id = $this->params()->fromRoute("id", null);

            $citas = $citas_model->getTblExpedientePaciente($id);
            require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

            // create a new EditableGrid object
            $grid = new \EditableGrid();
            $grid->addColumn('nombre_completo', 'Nombre Completo', 'varchar', NULL, false); //ocupo nombre completo
            $grid->addColumn('edad', 'Edad', 'string', NULL, false);
            $grid->addColumn('fecha', 'Fecha de Consulta', 'varchar', NULL, false);
            $grid->addColumn('especialidad', 'Especialidad', 'varchar', NULL, false);
            $grid->addColumn('nombre_medico', 'Medico', 'varchar', NULL, false);
            $grid->addColumn('motivo', 'Motivo', 'varchar', NULL, false);
            $grid->addColumn('dias_transcurridos', 'Dias De Incapacidad', 'string', NULL, false);
            //        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
            $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_cita');

            $grid->renderXML($citas);
            exit;

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettblhistoricopacienteAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);

            $id = $this->params()->fromRoute("id", null);

            $info_paciente = $citas_model->getPorId($id);

            $id_afiliado = $info_paciente['id_afiliado'];

            $citas = $citas_model->getTblExpedientePaciente($id_afiliado);
            require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

            // create a new EditableGrid object
            $grid = new \EditableGrid();
            $grid->addColumn('nombre_completo', 'Nombre Completo', 'varchar', NULL, false); //ocupo nombre completo
            $grid->addColumn('edad', 'Edad', 'string', NULL, false);
            $grid->addColumn('fecha', 'Fecha de Consulta', 'date', NULL, false);
            $grid->addColumn('especialidad', 'Especialidad', 'varchar', NULL, false);
            $grid->addColumn('nombre_medico', 'Medico', 'varchar', NULL, false);
            $grid->addColumn('motivo', 'Motivo', 'varchar', NULL, false);
            //        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
            $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_cita');

            $grid->renderXML($citas);
            exit;
        }
    }

    public function gettblallpacientesAction() {

//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['conf_cargos'] != '1') {            
//            exit;
//        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $afiliados_model = new Afiliados($this->dbAdapter);
        $afiliados = $afiliados_model->getTblExpedientes();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        foreach ($afiliados as $f => $c) {

            if ($c['tipo'] == '1') {
                $afiliados[$f]['tipo_de_afiliado'] = "Directo";
            } else if ($c['tipo'] == '2') {
                $afiliados[$f]['tipo_de_afiliado'] = "Beneficiario";
            }
        }

        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('dni', 'Expediente', 'string', NULL, false);
        $grid->addColumn('nombre_completo', 'Nombre Completo', 'varchar', NULL, false); //ocupo nombre completo
        $grid->addColumn('tipo_de_afiliado', 'Tipo de Afiliado', 'varchar', NULL, false);
        //        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
        $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_afiliado');

        $grid->renderXML($afiliados);
        exit;
    }

    public function consultasAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function verexpedientemedicoaAction() {

        $objeto = 'Medicos';
        $permiso = 'permiso_expedientes';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET



            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $citas_model = new Citas($this->dbAdapter);

            $id = $this->params()->fromRoute("id", null);

            $info_expediente = $citas_model->getExpedientePorCita($id);

            //                     GUARDAR A BITACORA

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $objetos_model = new Objetos($this->dbAdapter);
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $id_objetos = $objetos_model->getIdobjetoMedicos();
            $DateAndTime = date('Y-m-d h:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Ver Expediente',
                'descripcion' => $descripcion['descripcion'] = " el usuario {$_SESSION['auth']['usuario']} entro al expediente del afiliado con # de expediente "
                . " {$info_expediente['expediente']}"];

            $utl_bitacora->guardarBitacora($bitacora);

//            
//            echo "<pre>";
//            print_r($info_expediente);
//            exit;
//            

            $vista = new ViewModel(array('citapersona' => $info_expediente)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function verexpedientemedicoAction() {

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $citas_model = new Citas($this->dbAdapter);

        $id = $this->params()->fromRoute("id", null);

        $info_cita = $citas_model->getCitasinfo($id);

        $id_afiliado = $info_cita['id_afiliado'];

        $id_medico = $_SESSION['auth']['id_medico'];

        $DateAndTime = date('Y-m-d');

        $permiso_hoy = $citas_model->getPermisoHoy($id_afiliado, $id_medico, $DateAndTime);

        if (!$permiso_hoy) {

            //                     GUARDAR A BITACORA

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $id_objetos = $objetos_model->getIdobjetoMedicos();
            $DateAndTime = date('Y-m-d H:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Acceso Denegado',
                'descripcion' => "Se intento accder el expediente del afiliado {$info_cita['nombre']} con # de expediente {$info_cita['dni']}"
                . "con fecha de expediente {$info_cita['fecha_cita']}"];

            $utl_bitacora->guardarBitacora($bitacora);

            $_SESSION['mnsError'] = array('titulo' => 'Acceso Denegado !', 'texto' => "No tiene permitido ver el expediente.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $objeto = 'Medicos';
        $permiso = 'permiso_consultar';

        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET



            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $citas_model = new Citas($this->dbAdapter);

            $id = $this->params()->fromRoute("id", null);

            $info_expediente = $citas_model->getExpedientePorCita($id);

            //                     GUARDAR A BITACORA

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $objetos_model = new Objetos($this->dbAdapter);
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $id_objetos = $objetos_model->getIdobjetoMedicos();
            $DateAndTime = date('Y-m-d h:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Ver Expediente',
                'descripcion' => $descripcion['descripcion'] = " el usuario {$_SESSION['auth']['usuario']} entro al expediente del afiliado con # de expediente "
                . " {$info_expediente['expediente']}"];

            $utl_bitacora->guardarBitacora($bitacora);

//            
//            echo "<pre>";
//            print_r($info_expediente);
//            exit;
//            

            $vista = new ViewModel(array('citapersona' => $info_expediente)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablaexpedienteAction() {
        if (!isset($_SESSION['auth'])) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $citas_model = new Citas($this->dbAdapter);

//        $id_suscursal = $_SESSION['auth']['id_sucursal'];

        $fecha_actual = date("Y-m-d%");

        $id_medico = $_SESSION['auth']['id_medico'];

        if (!$id_medico) {

            $id_medico = 0;
        }



        $pacientes = $citas_model->gettblPacientesMedicos($fecha_actual, $id_medico);

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        $grid = new \EditableGrid();
        if (!$pacientes) {
            $grid->addColumn('nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('opciones', '', 'html', NULL, false, 'id');
        } else {
            // create a new EditableGrid object

            $grid->addColumn('dni', 'N° Expediente', 'string', NULL, false);
            $grid->addColumn('nombre_completo', 'Nombre Completo', 'string', NULL, false);
            $grid->addColumn('motivo_consulta', 'Motivo consulta', 'string', NULL, false);
//            $grid->addColumn('observaciones', 'Obs. preclinica', 'string', NULL, false);
            $grid->addColumn('estado', 'Estado Cita', 'string', NULL, false);
            $grid->addColumn('fecha', 'Fecha de consulta', 'string', NULL, false);

            $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_cita');
        }
        $grid->renderXML($pacientes);
        exit;
    }

    public function getdatospacienteAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $idc = $this->params()->fromRoute("id", null);
        $afiliados_model = new Afiliados($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $idc = $this->params()->fromRoute("id", null);
            $afiliados_model = new Afiliados($this->dbAdapter);

            $info_cita = $afiliados_model->getPacientesinfo($idc);

            $vista = new ViewModel(array('citapersona' => $info_cita)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function expedientemedicoAction() {

        $objeto = 'Medicos';
        $permiso = 'permiso_insercion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $expedientes_model = new Expedientes($this->dbAdapter);
        $citas_model = new Citas($this->dbAdapter);
        $id = $this->params()->fromRoute("id", null);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            $datosFormularios = $this->request->getPost()->toArray();

            if ($datosFormularios['dias_reposoi'] == null || $datosFormularios['dias_reposof'] == null) {

                $datosFormularios['dias_reposoi'] = date('Y-m-d');
                $datosFormularios['dias_reposof'] = date('Y-m-d');
            }



            $info_paciente = $citas_model->getInfoExpdiente($id);

            if (!$info_paciente) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Paciente  no encontrado o esta inactivo, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/consultas");
            }

            $id_estado_cita = ['id_estado_cita' => $datosFormularios['id_estado_cita']];

            unset($datosFormularios['id_estado_cita']);

            if ($expedientes_model->agregarNuevo($datosFormularios)) {



                $citas_model->actualizar($id, $id_estado_cita);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Consulta Guardada!', 'texto' => "La  Consuta fue guardada exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/consultas");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'no se pudo guardar la consulta, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/consultas");
            }
        } else {
            //Normal GET

            $info_paciente = $citas_model->getInfoExpdiente($id);

            if (!$info_paciente) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Paciente  no encontrado o esta inactivo, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/consultas");
            }

            $fecha_nacimiento = $info_paciente['fecha_nacimiento'];

            $array_edad = $this->obtenerEdadDetallada($fecha_nacimiento);

            $info_paciente['edad'] = "{$array_edad['anios']} años con {$array_edad['meses']} meses";

            $vista = new ViewModel(array('info' => $info_paciente)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    private function obtenerEdadDetallada($fecha_nacimiento) {

        //año-mes-dia
        $dt_fecha_nacimiento = new \DateTime("$fecha_nacimiento");
        $dt_fecha_actual = new \DateTime(\date('Y-m-d'));
        $diferenciaTotal = $dt_fecha_nacimiento->diff($dt_fecha_actual);

        $r['anios'] = $diferenciaTotal->y;

        $r['meses'] = $diferenciaTotal->m;

        $r['dias'] = $diferenciaTotal->d + 1;

        return $r;
    }

}
