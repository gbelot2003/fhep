<?php



namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Tiposoli;

/* use Application\Model\Mimodelo; */

class TiposoliController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function tiposoliAction() {
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

    public function nuevoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $tiposoli_model = new Tiposoli($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

//            echo "<pre>";
//            print_r($datosFormularios);
//            print_r($array_prueba);
//            exit;

            if ($tiposoli_model->agregarNuevo($datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de solicitud guardada!', 'texto' => "El tipo de solicitud fua creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/tiposoli");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/tiposoli");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettabladependenciasAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $dependencias_model = new Dependencias($this->dbAdapter);
        $dp = $dependencias_model->getTblDependencia();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object

        $grid = new \EditableGrid();
        if (!$dp) {
            $grid->addColumn('Nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('action', '', 'html', NULL, false, 'id');
           
        } else {

           
            $grid->addColumn('nombre_dp', 'Nombre', 'string', NULL, false);
            $grid->addColumn('descripcion', 'Descripcion', 'string', NULL, false);
            

            $grid->addColumn('action', 'Acción', 'html', NULL, false, 'id');
        }
         $grid->renderXML($dp);
            exit;
    }
//    /    NICIO PDF VOUCHER DEPENDENCIAS POLICIALES
    public function pdffichaDependenciasAction() {
// if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
// return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
// }
        if ($this->getRequest()->isXmlHttpRequest()) {
//para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            
        } else {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                    $Dependencia_model = new Dependencias($this->dbAdapter);
            $id_dependencia = $this->params()->fromRoute("id", null);

//RECUPERAR LA INFORMACION DEL REPORTE
            $info_dependencia = $Dependencia_model->getDependenciainfo($id_dependencia);

// echo "<pre>";
// print_r($info_citas);
// exit;



            if (!$info_dependencia) {
                $_SESSION['mnsAutoOK'] = array('titulo' => 'Dependencia Guardada!', 'texto' => "Dependencia no encontrado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas");
            }
            $nombre_dependencia = $info_dependencia[''];
            $descripcion_dependencia = $info_dependencia[''];
            $estado_dependencia = $info_dependencia[''];
           

// Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

// create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('P', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema FHEP');
            $pdf->SetTitle('Ficha de Dependencias');
            $pdf->SetSubject('Dependencias');
            $pdf->SetKeywords('ficha');

// Configurar los datos de la imagen
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "FHEP";
            $linea_auxiliar = " ";
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
            
            
            $tituloHTML = "<br><div style=\"text-align: center; font-family: lucida console,monospace; font-size: 18px; line-height: 30px;\">"
                    . "<b>Dependecnias</b>"
                    . "</div>";

            $contenidoHTML = "<br><div style=\"text-align: justify; font-family: lucida consola; font-size: 14px; line-height: 25px;\">"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Nombre Dependecnia:<b> $nombre_dependencia </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Descripción Dependecnia:<b> $descripcion_dependencia </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Estado Dependecnia: <b> $estado_dependencia </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "<div style=\"text-align: justify; font-family: arial; font-size: 16px; line-height: 30px;\"><p>"
                    . ""
                    . "</p></div>";
            $firmasHTML = "<div style=\"text-align: center; font-family: arial; font-size: 14px; line-height: 20px; margin-top: 100px;\"><p>"
// . "__________________________________________<br>"
// . "$tituloFirmante $nombreFirmante<br>"
// . "$cargoFirmante<br>"
                    . "</p></div>";

// output the HTML content
            $pdf->writeHTML($tituloHTML, true, false, true, false, '');
            $pdf->writeHTML($contenidoHTML, true, false, true, false, '');

            $pdf->Ln();
            $pdf->Cell($m, 0, '', '');
            $pdf->Ln();

            $pdf->writeHTML($firmasHTML, true, false, true, false, '');

            \ob_clean();
// Cerrar enviar documento PDF , la I (i)= lo muestra en pantalla D = lo descarga a la maquina del cliente
            $pdf->Output("FICHA DEPENDENCIAS POLICIALES " . '.pdf', 'I');
            exit;
        }
    }
    

}

