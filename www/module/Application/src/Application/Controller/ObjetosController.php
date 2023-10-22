<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Objetos;
use Application\Model\Bitacoras;
use Application\Extras\Utilidades\Texto;
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Bitacora;

class ObjetosController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function objetosAction() {
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

    public function nuevoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['plan_mensual'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $objetos_model = new Objetos($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            $datosFormularios = $this->request->getPost()->toArray();

            unset($datosFormularios['chk3']);

            if ($objetos_model->agregarNuevo($datosFormularios)) {

//                $id_objeto = $objetos_model->getIdReciente($datosFormularios);
//                $array_prueba['id_objeto'] = $id_objeto;
//                $prueba_model->agregarNuevo($array_prueba);
//                
                //                     GUARDAR A BITACORA


                $id_objetos = $objetos_model->getIdobjetoSeguridad();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'creacion de objeto',
                    'descripcion' => "Se creo un objeto con el nombre {$datosFormularios['objeto']}"];
                 $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Objeto guardado!', 'texto' => "El objeto fua creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el objeto, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablaobjetosAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $objetos_model = new Objetos($this->dbAdapter);
        $objetos = $objetos_model->gettblObjetos();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('objeto', 'Nombre', 'string', NULL, false);
        $grid->addColumn('tipo_objeto', 'Tipo', 'varchar', NULL, false); //ocupo nombre completo
        $grid->addColumn('descripcion', 'Descripcion', 'varchar', NULL, false);
        //        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
        $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id');

        $grid->renderXML($objetos);
        exit;
    }

    public function exportarpdfobjetosAction() {
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
            $objetos_model = new Objetos($this->dbAdapter);
            $objetos = $objetos_model->gettblObjetos();
            
            //PDF OBJETOS
            
//             echo "<pre>";
//           print_r($objetos);
//          exit;

//            PERMISOS PDF

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Objetos');
            $pdf->SetSubject('Objetos');
            $pdf->SetKeywords('Objetos');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Objetos";
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
            $header = ['N°', 'Nombre', 'Tipo', 'Desripción', 'Estado'];
            $w = array($m * .02, $m * .15, $m * .21, $m * .48, $m * .14);

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

            foreach ($objetos as $fila) {

                //Corregir textos
//'Nombre', 'Tipo', 'Desripción', 'Estado'
                $nombre = $utilidad_texto->fixTexto($fila['objeto']);
                $tipo= $utilidad_texto->fixTexto($fila['tipo_objeto']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);
                $estado = $utilidad_texto->fixTexto($fila['estado']);
                
              

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nombre, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[2], 4.2, $tipo, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[3], 4.2, $descripcion, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[4], 4.2, $estado, 'LR', 0, 'L', $fill);
                

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
            $pdf->Output("OBJETOS" . '.pdf', 'I');
            exit;
        }
    }

    public function eliminarAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $objetos_model = new Objetos($this->dbAdapter);
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {



            $datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $utl_bitacora = new Bitacora($this->dbAdapter);
            $id = $this->params()->fromRoute("id", null);

            $validar_objeto = $objetos_model->getExisteObjeto($id);

            if ($validar_objeto) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede borrar el objeto, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            } else {

                $objetos_model->borrar($id);

//                     GUARDAR A BITACORA

                $id_objetos = $objetos_model->getIdobjetoSeguridad();
                  
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Elimino un objeto',
                    'descripcion' => "Se elimino el objeto con id  {$id}"];

                 $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Objeto Eliminado!', 'texto' => "El objeto fue eliminado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }



        $id = $this->params()->fromRoute("id", null);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $objetos_model = new Objetos($this->dbAdapter);
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();


            $id_objeto = ['id' => $id];

            if ($objetos_model->actualizar($id_objeto, $datosFormularios)) {

//                  GUARDAR A BITACORA
                $DateAndTime = date('Y-m-d H:i:s');
                $id_objetos = $objetos_model->getIdobjetoSeguridad();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Modificacion de objeto',
                    'descripcion' => "Se modifico la infiromación del objeto {$datosFormularios['objeto']}"];
                 $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Objeto Actualizado!', 'texto' => "La informacion se cambio exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar la informacion.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            }
        } else {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $objetos_model = new Objetos($this->dbAdapter);
            //Normal GET
            $info_objeto = $objetos_model->getInfobjeto($id);

            //balidacion de la existencias del afiliado       
            if (!$info_objeto) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'El objeto no se encuentra.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            }



            $vista = new ViewModel(array('ob' => $info_objeto)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

}
