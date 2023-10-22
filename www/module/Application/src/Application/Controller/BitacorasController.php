<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Bitacoras;
use Application\Extras\Utilidades\Bitacora;
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Texto;

/* use Application\Model\Mimodelo; */

class BitacorasController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function BitacorasAction() {

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

    public function validarintegridadAction() {
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
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $utl_bitacora = new Bitacora($this->dbAdapter);

            $resultado = $utl_bitacora->validarIntegridad();

            if ($resultado['resultado'] == "error") {
                $datos = $resultado['fila'];

                $_SESSION['mnsWar'] = array('titulo' => 'ERROR!', 'texto' => "Bitacora alterada en el reguistro con fecha {$datos['Fecha']} "
                    . "y descripcion: {$datos['descripcion']} ");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/bitacoras");
            } else {
                $_SESSION['mnsAutoOK'] = array('titulo' => 'OK!', 'texto' => "Bitacora Segura");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/bitacoras");
            }
        }
    }

    public function gettablabitacorafAction() {
//        if (!isset($_SESSION['auth'])) {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['conf_cargos'] != '1') {            
//            exit;
//        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $bitacoras_model = new Bitacoras($this->dbAdapter);

        $ff = $this->params()->fromRoute("ff", null);
        $fi = $this->params()->fromRoute("fi", null);

        $bitacoras = $bitacoras_model->gettblBitacorasf($fi, $ff);

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('usuario', 'Usuario', 'string', NULL, false);
        $grid->addColumn('objeto', 'Objeto', 'varchar', NULL, false); //ocupo nombre completo
        $grid->addColumn('fecha_forma', 'Fecha y Hora', 'varchar', NULL, false);
        $grid->addColumn('Accion', 'Evento', 'varchar', NULL, false);
        $grid->addColumn('descripcion', 'Descripción', 'varchar', NULL, false);
        //        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
        //$grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id');

        $grid->renderXML($bitacoras);
        exit;
    }

    public function exportarexcelbitacoraAction() {
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
            //AQUI EL EXCEL
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $bitacoras_model = new Bitacoras($this->dbAdapter);
            $datos_excel = $bitacoras_model->gettblBitacoras();

//            echo "<pre>";
//            print_r($datos_excel);
//            exit;
            //AQUI PEGA EL EXCEL
            $total_filas = \count($datos_excel);

            // FIN PASO 1
            //PASO 2 -> VARIABLES PARA LA CREACION Y CONTROL DEL EXCEL
            $txt_fecha = \date('d-m-Y_H-i-s');
            $titulo = "BITACORA DEL SISTEMA $txt_fecha";
            $nombre_archivo = "BITACORA DEL SISTEMA $txt_fecha.xlsx"; //NOMBRE DEL ARCHIVO
            //Encabezados de las columnas
            $titulos_columnas = ['N°', 'Usuario', 'Fecha', 'Accion', 'descripcion'];

            //INFORMACIÓN DEL ENCABEZADO
            $encabezado_linea_1 = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $encabezado_linea_2 = "FHEP";
            $encabezado_linea_3 = "";
            $encabezado_linea_4 = "BITACORA DEL SISTEMA";

            $ultima_columna = "E";
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
            $excel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 2);

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
                $Accion = $utilidad_texto->fixTexto($fila['Accion']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);
                $usuario = $utilidad_texto->fixTexto($fila['usuario']);
                $fecha = $utilidad_texto->fixTexto($fila['fecha_forma']);

                //Insertar la información el la celda correspondiente
                $excel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $f, $cont)
                        ->setCellValueExplicit('B' . $f, $usuario, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('C' . $f, $fecha, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('D' . $f, $Accion, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('E' . $f, $descripcion, \PHPExcel_Cell_DataType::TYPE_STRING);

                $f++;
                $cont++;
            }



            //Dar formato de fecha 
//            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
//                $excel->getSheet(0)->getStyle('H' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
//                        ->getNumberFormat()->setFormatCode($formato);
//            }
//            echo "<pre>";
//            print_r($datos_excel);
//            exit;
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

    public function exportarpdfbitacoraAction() {
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
            $bitacoras_model = new Bitacoras($this->dbAdapter);
            $bitacoras = $bitacoras_model->gettblBitacoras();

            //PDF   BITACORA
//             echo "<pre>";
//     print_r($bitacoras);
//       exit;
//            
//            CATEGORIAS PDF

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            //Tamaños personalizados
            $legal = array(216, 355);
            $oficio = array(216, 330);
            $carta = 'Letter';

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, $oficio, true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Categorias');
            $pdf->SetSubject('Categorias');
            $pdf->SetKeywords('Categorias');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Bitacora";
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
            $header = ['N°', 'Usuario', 'Pantalla', 'Fecha', 'Descripción'];
            $w = array($m * .05, $m * .08, $m * .12, $m * .12, $m * .63);

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

            $pdf->SetFont('dejavusans', '', 8, '', true);

            $fill = 0;
            $cont = 1;

            foreach ($bitacoras as $fila) {

                //Corregir textos

                $nom_objeto = $utilidad_texto->fixTexto($fila['objeto']);
                $usuario = $utilidad_texto->fixTexto($fila['usuario']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);
                $fecha = $utilidad_texto->fixTexto($fila['fecha_forma']);

                $max_desc = 125;
                if (\strlen($descripcion) > $max_desc) {
                    $descripcion = \substr($descripcion, 0, $max_desc) . "...";
                }

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $usuario, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[2], 4.2, $nom_objeto, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[3], 4.2, $fecha, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[4], 4.2, $descripcion, 'LR', 0, 'L', $fill);

//              ['N°', 'Dependecnia', 'Descripción'];

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
            $pdf->Output("BITACORA" . '.pdf', 'I');
            exit;
            //FIN PDF
        }
    }

    public function gettablabitacoraAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $bitacoras_model = new Bitacoras($this->dbAdapter);
        $bitacoras = $bitacoras_model->gettblBitacoras();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('usuario', 'Usuario', 'string', NULL, false);
        $grid->addColumn('objeto', 'Objeto', 'varchar', NULL, false); //ocupo nombre completo
        $grid->addColumn('fecha_forma', 'Fecha y Hora', 'date', NULL, false);
        $grid->addColumn('Accion', 'Evento', 'varchar', NULL, false);
        $grid->addColumn('descripcion', 'Descripción', 'varchar', NULL, false);
        //        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
        //$grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id');

        $grid->renderXML($bitacoras);
        exit;
    }

}
