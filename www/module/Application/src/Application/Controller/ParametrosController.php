<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Parametros;
use Application\Extras\Utilidades\Texto;
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Bitacora;
use Application\Model\Objetos;

/* use Application\Model\Mimodelo; */

class ParametrosController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function ParametrosAction() {

//        $objeto = 'Seguridad';
//        $permiso = 'permiso_consultar';
//        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
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

    public function gettablaparametrosAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['conf_cargos'] != '1') {            
//            exit;
//        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $parametros_model = new Parametros($this->dbAdapter);
        $parametros = $parametros_model->getTblParametros();
        $utilidades_texto = new Texto();

//         echo "<pre>";
//        print_r($parametros);
//        
        foreach ($parametros as $k => $v) {

            $parametros[$k]['parametro'] = $utilidades_texto->fixTexto($v['parametro']);

            if ($parametros[$k]['parametro'] == 'mail_pass') {

                $pass = hash('sha256', $parametros[$k]['valor']);
                
                $pass_corto = \substr($pass, 0,- 50);

                $parametros[$k]['valor']= $pass_corto;
            
            }
        }

//        echo "<pre>";
//        print_r($parametros);
//        exit;

//       print_r($parametros);
//        exit;
        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('descripcion', 'Nombre', 'string', NULL, false);
        $grid->addColumn('valor', 'Valor', 'string', NULL, false);
//        $grid->addColumn('estado', 'Estado', 'string', NULL, false);

        $grid->addColumn('action', 'Acción', 'html', NULL, false, 'id');

        $grid->renderXML($parametros);
        exit;
    }

    public function exportarpdfparametrosAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $parametros_model = new Parametros($this->dbAdapter);
            $parametros = $parametros_model->getTblParametros();
            $utilidades_texto = new Texto();

            foreach ($parametros as $k => $v) {

                $parametros[$k]['parametro'] = $utilidades_texto->fixTexto($v['parametro']);
            }

            //PDF PARAMETROS
//            echo "<pre>";
//            print_r($parametros);
//           exit;
//            PERMISOS PDF

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Parametros');
            $pdf->SetSubject('Parametros');
            $pdf->SetKeywords('Parametros');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Parametros";
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
            $header = ['N°', 'Nombre', 'Valor', 'Estado'];
            $w = array($m * .02, $m * .45, $m * .38, $m * .15);

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

            foreach ($parametros as $fila) {

                //Corregir textos
//Nombre', 'Valor', 'Estado'
                $nombre = $utilidad_texto->fixTexto($fila['descripcion']);
                $valor = $utilidad_texto->fixTexto($fila['valor']);
                $estado = $utilidad_texto->fixTexto($fila['estado']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nombre, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[2], 4.2, $valor, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[3], 4.2, $estado, 'LR', 0, 'L', $fill);

//             

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
            $pdf->Output("PARAMETROS" . '.pdf', 'I');
            exit;
        }
    }

    public function nuevoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $parametros_model = new Parametros($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            if ($parametros_model->agregarNuevo($datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoSeguridad();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion De Parametro',
                    'descripcion' => "Se creo un parametro con nombre {$datosFormularios['descripcion']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Parametro Guardado!', 'texto' => "El Parametro fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/parametros");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el parametro, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $parametros_model = new Parametros($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $id = $this->params()->fromRoute("id", null);

            $datosFormularios = $this->request->getPost()->toArray();

            if ($parametros_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoCitas();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Parametro',
                    'descripcion' => "Se actualizo el parametro con nombre {$datosFormularios['descripcion']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Parametro Actualizado!', 'texto' => "El Parametro fue Actualizado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/parametros");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al Actualizar el parametro, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/parametros");
            }
        } else {
            //Normal GET

            $id = $this->params()->fromRoute("id", null);

            $info_paramtro = $parametros_model->getPorId($id);

            $vista = new ViewModel(array('info' => $info_paramtro)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $parametros_model = new Parametros($this->dbAdapter);

            $id = $this->params()->fromRoute("id", null);

            if ($parametros_model->borrar($id)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoCitas();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Parametro',
                    'descripcion' => "Se elimino el parametro con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Parametro Eliminado!', 'texto' => "El Parametro fue eliminado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/parametros");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al eliminar el parametro, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/parametros");
            }


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

}
