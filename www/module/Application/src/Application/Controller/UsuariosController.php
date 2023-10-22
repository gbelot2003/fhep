<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Usuarios;
use Application\Model\Personas;
use Application\Model\Preguntas;
use Application\Model\Esciviles;
use Application\Model\Sangres;
use Application\Model\Rangos;
use Application\Model\Dependencias;
use Application\Model\Asignaciones;
use Application\Model\Categorias;
use Application\Model\Afiliados;
use Application\Model\Datosprofecionales;
use Application\Model\Respuestausuario;
use Application\Model\Roles;
use Application\Model\Histocontasena;
use Application\Model\EstadoUsuarios;
//use Application\Model\Correos;
use Application\Model\Preguntasusuarios;
use Application\Model\RolesxObjetos;
use Application\Model\Bitacoras;
use Application\Model\Objetos;
use Application\Model\Sesiones;
use Application\Extras\Correo\EnvioCorreos;
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Texto;
use Application\Extras\Utilidades\Bitacora;
use Application\Model\Sucursales;

class UsuariosController extends AbstractActionController {

    private $salt = 'The witcher 3 ';

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function usuariosAction() {
        $objeto = 'Seguridad';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
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

    public function nuevoAction() {
        $objeto = 'Seguridad';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $roles_model = new Roles($this->dbAdapter);
        $usuarios_model = new Usuarios($this->dbAdapter);
        $estadousuarios_model = new EstadoUsuarios($this->dbAdapter);
        $personas_model = new Personas($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);
        $sucursales_model = new Sucursales($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            $datosFormularios = $this->request->getPost()->toArray();
            $datosFormularios['dni'] = $datosFormularios['correo'];

            $datos_p = ['nombres' => $datosFormularios['nombres'], 'apellidos' => $datosFormularios['apellidos'],
                'dni' => $datosFormularios['dni'], 'correo' => $datosFormularios['correo']];

            $datosFormularios['usuario'] = \mb_strtoupper($datosFormularios['usuario']);

            $exr_pass = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&#._$($)$-$])[A-Za-z\d$@$!%*?&#._$-$]*$/";
            $exr_usuario = "/^[A-Z0-9]{1,20}$/";

            //Validar por expresion regular los datos por ataques
            $passCorrecto = \preg_match($exr_pass, $datosFormularios['contrasenia']);
            $usurioCorrecto = \preg_match($exr_usuario, $datosFormularios['usuario']);

            if (!$passCorrecto || !$usurioCorrecto) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Datos incorrectos.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/nuevo");
            }


            $usuario = $datosFormularios['usuario'];
            $correo_usuario = $datosFormularios['correo'];

            // VALIDAR CORREO Y USUARIOS NO ESTEN REPETIDOS

            $validar_existenciac = $usuarios_model->getExisteCorreo($correo_usuario);
            $validar_existenciau = $usuarios_model->getExisteUsuario($usuario);

            if ($validar_existenciau || $validar_existenciac) {

                $_SESSION['mnsError'] = array('titulo' => 'Repetidos!', 'texto' => 'Usuario o Correo ya estan registrados.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/nuevo");
            }

            unset($datosFormularios['rpass'], $datosFormularios['confitmacion'], $datosFormularios['apellidos'],
                    $datosFormularios['nombres'], $datosFormularios['dni'], $datosFormularios['correo'],
                    $datosFormularios ['fecha_modificacion']);

            // INSERTAR A LA TABLA PERSONA 

            if ($personas_model->agregarpNuevo($datos_p)) {
                
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al  crear el usuario, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios");
            }


            unset($datos_p['dni']);

            $id_persona = $personas_model->getIdReciente($datos_p);

            $datosFormularios['id_persona'] = $id_persona;

            $pass_hash = \hash('sha256', $datosFormularios['contrasenia'] . $this->salt);

            $pass_correo = \substr($pass_hash, 0, $_SESSION['parametros']['max_len_pass'] - 5);

            $pass_hash_guardar = \hash('sha256', $pass_correo . $this->salt);

            $datosFormularios['contrasenia'] = $pass_hash_guardar;

            $datosFormularios['primer_contra'] = 1;

            // INSERTAR ALA TABLA USUARIOS
            if ($usuarios_model->agregarNuevo($datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdObjtoUsuarios();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de usuario',
                    'descripcion' => $descripcion['descripcion'] = " Se creo el usuario con nombre {$datosFormularios['usuario']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Usuario Creado!', 'texto' => "El ususario se creo exitosamente.");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al  crear el usuario, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios");
            }

            $url = $_SESSION['parametros']['url_pag'];

            $envio_correo = new EnvioCorreos();

//            $fecha = \date('Y-m-d H:i:s');
            //Enviar correo
            $contenido_correo = "<div>"
                    . "<h3>Bienvenido al sistema FHEP </h3>"
                    . "<p>"
                    . "Estimado Afiliado le enviamos su usuario y contraseña para poder ingresar:<br><br>"
                    . "<b>Usuario:</b> $usuario<br>"
                    . "<b>Contraseña:</b> $pass_correo<br><br>"
                    . "<b>Utilice el siguiente enlace para acceder:</b>  $url <br><br>"
                    . "</p>"
                    . "</div>";

            $asunto_correo = "Creacion de usuario " . \date('d/m/Y H:i:s');

            if ($envio_correo->enviarCorreo($contenido_correo, $asunto_correo, $correo_usuario)) {
                //guardar bitacora

                $_SESSION['mnsAutoInfo'] = array('titulo' => 'Correo Enviado!', 'texto' => "se envio el usuario y contraseña al afiliado.");

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Falló al enviar correo.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios");
            }
        } else {
            //Normal GET

            $estados = $estadousuarios_model->getEstados();
            $roles = $roles_model->getRoles();

            $sucursales = $sucursales_model->getSucursales();

            $vista = new ViewModel(array('roles' => $roles, 'estados' => $estados, 'sucursales' => $sucursales)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablausuariosAction() {

//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['conf_cargos'] != '1') {            
//            exit;
//        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $usuarios_model = new Usuarios($this->dbAdapter);
        $usuarios = $usuarios_model->getTblUsuarios();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

//        foreach ($usuarios as $f => $c) {
//
//            if ($c['estado_u'] == '1') {
//                $usuarios[$f]['estado_u'] = "Activo";
//            } else if ($c['estado_u'] == '2') {
//                $usuarios[$f]['estado_u'] = "Inactivo";
//            } else {
//                $usuarios[$f]['estado_u'] = "Desconocido";
//            }
//        }
        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('Nombre_Completo', 'Nombre', 'string', NULL, false);
        $grid->addColumn('usuario', 'Usuario', 'string', NULL, false);
        $grid->addColumn('correo', 'Correo', 'email', NULL, false);
        $grid->addColumn('nombre_sucursal', 'Sucursal', 'string', NULL, false);
        $grid->addColumn('nombre', 'Estado', 'string', NULL, false);
        $grid->addColumn('action', 'Acción', 'html', NULL, false, 'id_usuario');

        $grid->renderXML($usuarios);
        exit;
    }

    public function exportarexcelusuariosAction() {
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
            //AQUI COMIENSAN LOS ARCHIVOS PARA EL EXCEL
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $usuarios_model = new Usuarios($this->dbAdapter);
            $datos_excel = $usuarios_model->getTblUsuarios();

            foreach ($datos_excel as $f => $c) {

                if ($c['estado_u'] == '1') {
                    $datos_excel[$f]['estado_u'] = "Activo";
                } else if ($c['estado_u'] == '2') {
                    $datos_excel[$f]['estado_u'] = "Inactivo";
                } else {
                    $datos_excel[$f]['estado_u'] = "Desconocido";
                }
            }

//            echo "<pre>";
//            print_r($datos_excel);
//            exit;
            //AQUI PEGA EL EXCEL
            $total_filas = \count($datos_excel);

            // FIN PASO 1
            //PASO 2 -> VARIABLES PARA LA CREACION Y CONTROL DEL EXCEL
            $txt_fecha = \date('d-m-Y_H-i-s');
            $titulo = "LISTA DE USUSARIOS $txt_fecha";
            $nombre_archivo = "LISTA DE USUARIOS $txt_fecha.xlsx"; //NOMBRE DEL ARCHIVO
            //Encabezados de las columnas
            $titulos_columnas = ['N°', 'Nombre Completo', 'Correo', 'Usuario', 'Sucursal', 'Estado'];

            //INFORMACIÓN DEL ENCABEZADO
            $encabezado_linea_1 = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $encabezado_linea_2 = "FHEP";
            $encabezado_linea_3 = "";
            $encabezado_linea_4 = "LISTA DE USUARIOS";

            $ultima_columna = "F";
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
            $excel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 4);

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
                $nombre_completo = $utilidad_texto->fixTexto($fila['Nombre_Completo']);
                $correo = $utilidad_texto->fixTexto($fila['correo']);
                $estado_u = $utilidad_texto->fixTexto($fila['nombre']);
                $usuarios = $utilidad_texto->fixTexto($fila['usuario']);
                $sucursal = $utilidad_texto->fixTexto($fila['nombre_sucursal']);

                //Insertar la información el la celda correspondiente
                $excel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $f, $cont)
                        ->setCellValueExplicit('B' . $f, $nombre_completo, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('C' . $f, $correo, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('D' . $f, $usuarios, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('E' . $f, $sucursal, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('F' . $f, $estado_u, \PHPExcel_Cell_DataType::TYPE_STRING);

                $f++;
                $cont++;
            }



            //Dar formato de fecha 
            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                $excel->getSheet(0)->getStyle('H' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
                        ->getNumberFormat()->setFormatCode($formato);
            }


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
            $excel->getActiveSheet()->setTitle("LISTA DE USUSARIOS");

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
            $id_objetos = $objetos_model->getIdobjetoCitas();
            $DateAndTime = date('Y-m-d H:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Descarga De Excel',
                'descripcion' => "Se descargo un archivo de excel con la lista de todos los ususarios"];

            $utl_bitacora->guardarBitacora($bitacora);

            exit;
        }
    }

    public function exportarpdfusuariosAction() {
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
            $usuarios_model = new Usuarios($this->dbAdapter);
            $usuarios = $usuarios_model->getTblUsuarios();

            //PDF USUARIOS

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Usuarios');
            $pdf->SetSubject('Usuarios');
            $pdf->SetKeywords('Usuarios');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Usuarios";
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
            $header = ['N°', 'Nombre', 'Usuario', 'Correo', 'Sucursal', 'Estado'];
            $w = array($m * .08, $m * .23, $m * .20, $m * .24, $m * .17, $m * .08);

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

            foreach ($usuarios as $fila) {

                //Corregir textos

                $nombre_usuario = $utilidad_texto->fixTexto($fila['Nombre_Completo']);
                $usuario = $utilidad_texto->fixTexto($fila['usuario']);
                $correo = $utilidad_texto->fixTexto($fila['correo']);
                $estado = $utilidad_texto->fixTexto($fila['nombre']);
                $Sucursal = $utilidad_texto->fixTexto($fila['nombre_sucursal']);
                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nombre_usuario, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[2], 4.2, $usuario, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[3], 4.2, $correo, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[4], 4.2, $Sucursal, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[5], 4.2, $estado, 'LR', 0, 'L', $fill);

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
            $pdf->Output("USUARIOS" . '.pdf', 'I');

            //                     GUARDAR A BITACORA

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $id_objetos = $objetos_model->getIdobjetoSeguridad();
            $DateAndTime = date('Y-m-d H:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Descarga De PDF',
                'descripcion' => "Se descargo el pdf con toda la lista de ususarios"];

            $utl_bitacora->guardarBitacora($bitacora);

            exit;
        }
    }

    public function loginAction() {

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $usuarios_model = new Usuarios($this->dbAdapter);
            $permisos_objetos_model = new RolesxObjetos($this->dbAdapter);
            $personas_model = new Personas($this->dbAdapter);
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $sesiones_model = new Sesiones($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

            $exr_pass = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&#._$($)$-$])[A-Za-z\d$@$!%*?&#._$-$]*$/";

            $exr_usuario = "/^[A-Z0-9a-zñÑ]{5,20}$/";
            $usuario = $datosFormularios['usuario'];

            $info_usuariov = $usuarios_model->getPorUsuario($usuario);

            if ($info_usuariov['reseteo_clave'] != 1 && $info_usuariov['primer_contra'] != 1) {
                //Validar por expresion regular los datos por ataques
                $passCorrecto = \preg_match($exr_pass, $datosFormularios['contrasenia']);

                $usurioCorrecto = \preg_match($exr_usuario, $datosFormularios['usuario']);

                if (!$passCorrecto || !$usurioCorrecto) {
                    $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Datos incorrectos.');
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
                }
            }


            $pass = hash('sha256', $datosFormularios['contrasenia'] . $this->salt);

            $exepcion = 'ADMIN';

            $info_usuario = $usuarios_model->validarPass($usuario, $pass);

            if (!$info_usuario) {

                //contar el número de intentos
                //recuperar si hay algún usuario con ese nombre de usuario
                $info_usuario = $usuarios_model->getPorUsuario($usuario);

//                echo "<pre>";
//                print_r($info_usuario);
//                exit;
                //evaluar si existe un usuario
                if ($info_usuario && $info_usuario['usuario'] != $exepcion) {
                    $id_usuario = $info_usuario['id_usuario'];

                    $intentos_db = $info_usuario['intentos'] + 1;

                    $array_actulizar = ['intentos' => $intentos_db];
                    if ($intentos_db >= $_SESSION['parametros']['max_error_pass']) {
                        $array_actulizar['estado_u'] = '2';
                        $usuarios_model->actualizar($id_usuario, $array_actulizar);

                        $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => "Usuario o contraseña inválido.");
                        $_SESSION['mnsWar'] = array('titulo' => 'Advertencia!', 'texto' => "Su usuario ha sido bloqueado, comuniquese con el administrador.");
                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
                    } else {
                        if ($intentos_db == ($_SESSION['parametros']['max_error_pass'] - 1)) {
                            $_SESSION['mnsWar'] = array('titulo' => 'Advertencia!', 'texto' => "Su usuario será inactivado en el siguiente intento fallido.");
                        }

                        $usuarios_model->actualizar($id_usuario, $array_actulizar);
                        $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => "Usuario o contraseña inválido.");
                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
                    }
                }

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => "Usuario o contraseña inválido.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            if ($info_usuario['estado_u'] != 1) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => "Cuenta inactiva.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            if ($info_usuario['estado'] == 'Inactivo') {

                $_SESSION['mnsWar'] = array('titulo' => 'Error!', 'texto' => "Su cuenta no cuenta con un rol activo comuniquese un "
                    . "administrador .");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            if ($info_usuario['id_rol'] == 0) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => "Su cuenta no cuenta con un rol comuniquese con un "
                    . "administrador .");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            $info_sesion = $sesiones_model->getsesion($usuario);

            if ($info_sesion) {

                $_SESSION['cerrar_sesion']['usuario'] = $usuario;
                $_SESSION['cerrar_sesion']['id_usuario'] = $info_usuario['id'];
                $_SESSION['mnsError'] = array('titulo' => 'Sesion Activa!', 'texto' => "Ya hay una sesion iniciada con este usuario cierre sesion y vuelva"
                    . " a intentar.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/cerrarsesione");
            }



            $id_persona = $info_usuario['id_persona'];

            $id_medico = $usuarios_model->getIdMedico($id_persona)['id_medico'];

            if ($id_medico == null) {

                $id_medico = 0;
            }
            $info_perosnas = $personas_model->getPorId($id_persona);

            $nombre = $info_usuario['usuario'];

//          PRIMER  LOGIN CAMBIO DE CONTRASEÑA
            if ($info_usuario['primer_contra'] == 1) {

                $_SESSION['reset_pass']['id_usuario'] = $info_usuario['id'];
                $_SESSION['reset_pass']['nombres'] = $info_perosnas['nombres'];
                $_SESSION['reset_pass']['apellidos'] = $info_perosnas['apellidos'];
                $_SESSION['reset_pass']['pass_min'] = $_SESSION['parametros']['min_len_pass'];
                $_SESSION['reset_pass']['pass_max'] = $_SESSION['parametros']['max_len_pass'];

                $_SESSION['mnsAutoInfo'] = array('titulo' => 'Cambio de Contraseña!', 'texto' => "Cambie su contraseña por una de su elección.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/cambiocontrasena");
            }

            //si restableció la contraseña por correo enviarlo a cambio de contraseña
            if ($info_usuario['reseteo_clave'] == 1) {
                $_SESSION['reset_pass']['id_usuario'] = $info_usuario['id'];
                $_SESSION['reset_pass']['nombres'] = $info_perosnas['nombres'];
                $_SESSION['reset_pass']['apellidos'] = $info_perosnas['apellidos'];
                $_SESSION['reset_pass']['pass_min'] = $_SESSION['parametros']['min_len_pass'];
                $_SESSION['reset_pass']['pass_max'] = $_SESSION['parametros']['max_len_pass'];

                $t_max_time_recu_pass = $_SESSION['parametros']['max_time_recu_pass'] * 3600;

                $t_fecha_reset_pass = \strtotime($info_usuario['fecha_reset_pass']); //pasa a segundos

                $fecha = \date('Y-m-d H:i:s');

                $t_fecha = \strtotime($fecha);

                $tiempo_transcurrido = $t_fecha - $t_fecha_reset_pass;

                if ($tiempo_transcurrido > $t_max_time_recu_pass) {
                    $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => "Ya venció el tiempo para la contraseña utilizada, solicite nuevamente el cambio.");
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
                }
                $_SESSION['mnsAutoInfo'] = array('titulo' => 'Cambio de Contraseña!', 'texto' => "Cambie su contraseña por una de su elección.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/cambiocontrasena");
            }


            if ($info_usuario['primer_ingreso'] == 1) {
                $_SESSION['inicio_1']['id_usuario'] = $info_usuario['id'];
                $_SESSION['inicio_1']['usuario'] = $info_usuario['usuario'];
                $_SESSION['inicio_1']['nombres'] = $info_perosnas['nombres'];
                $_SESSION['inicio_1']['apellidos'] = $info_perosnas['apellidos'];
                $_SESSION['inicio_1']['id_persona'] = $info_perosnas['id'];
                $_SESSION['inicio_1']['correo'] = $info_perosnas['correo'];

                unset($_SESSION['auth']);

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/primerlogin");
            }

            $permisos_t = $permisos_objetos_model->getPorIdRol($info_usuario['id_rol']);

            $permisos = [];
            foreach ($permisos_t as $v) {
                $obj = $v['objeto'];
                $permisos[$obj] = $v;
            }



//            if ($info_usuario['vencimiento'] >= $_SESSION['parametros']['max_time_pass']) {
//
//                $_SESSION['vence']['usuario'] = $info_usuario['usuario'];
//                $_SESSION['vence']['nusuario'] = $info_usuario['nusuario'];
//                $_SESSION['vence']['id_usuario'] = $info_usuario['id'];
//
//                $_SESSION['mnsInfo'] = array('titulo' => 'Error!', 'texto' => "contraseña vencida.");
//
//                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/renovarcontra");
//            }

            $id_usuario = $info_usuario['id'];

            $_SESSION['auth']['usuario'] = $info_usuario['usuario'];
            $_SESSION['auth']['id_rol'] = $info_usuario['id_rol'];
            $_SESSION['auth']['id_sucursal'] = $info_usuario['id_sucursal'];
            $_SESSION['auth']['id_usuario'] = $id_usuario;
            $_SESSION['auth']['id_medico'] = $id_medico;
            $_SESSION['permisos'] = $permisos;

            $usuarioc = ['usuario' => $_SESSION['auth']['usuario'], 'fecha_inicio' => date('Y-m-d H:i:s')
                , 'fecha_actividad' => date('Y-m-d H:i:s')];

            $array_reset_intentos = ['intentos' => '0', 'ultimo_ingreso' => \date('Y-m-d H:i:s')];

            $usuarios_model->actualizar($id_usuario, $array_reset_intentos);

            //SESION

            $sesiones_model->agregarNuevo($usuarioc);

            // GUARDAR EN BITACORA  
            $DateAndTime = date('Y-m-d H:i:s');

            $id_objetos = $objetos_model->getIdObjetoLogin();

            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Inicio de sesion',
                'id_objeto' => $id_objetos['id'],
                'descripcion' => $descripcion['descripcion'] = " Se inicio sesion con el usuario {$_SESSION['auth']['usuario']}"];

            $utl_bitacora->guardarBitacora($bitacora);

            if (!isset($info_usuario['id_sucursal'])) {
                $_SESSION['auth']['id_sucursal'] = 1;
            } else {
                $_SESSION['auth']['id_sucursal'] = $info_usuario['id_sucursal'];
            }

            $_SESSION['mnsAutoOK'] = array('titulo' => 'Bienvenido!', 'texto' => "Bienvenido <b>$nombre</b>.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            //Normal GET

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function primerloginAction() {
        if (!isset($_SESSION['inicio_1']['id_usuario'])) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $usuarios_model = new Usuarios($this->dbAdapter);
        $personas_model = new Personas($this->dbAdapter);
        $datosprofecionales_model = new Datosprofecionales($this->dbAdapter);

        $afiliados_model = new Afiliados($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            $datos_persona = ['nombres' => $datosFormularios['nombres'],
                'apellidos' => $datosFormularios['apellidos'],
                'fecha_nacimiento' => $datosFormularios['fecha_nacimiento'],
                'dni' => $datosFormularios['dni'],
                'id_tipo_sangre' => $datosFormularios['id_tipo_sangre'],
                'correo' => $datosFormularios['correo'],
                'departamento' => $datosFormularios['departamento'],
                'genero' => $datosFormularios['genero'],
                'id_estado_civil' => $datosFormularios['id_estado_civil'],
                'telefono' => $datosFormularios['telefono'],
                'celular' => $datosFormularios['celular'],
                'domicilio_actual' => $datosFormularios['domicilio_actual'],
                'nom_contacto_emergencia' => $datosFormularios['nom_contacto_emergencia'],
                'telefono_contacto_emergencia' => $datosFormularios['telefono_contacto_emergencia']];

            $datos_pro = ['id_persona' => $datosFormularios['id_persona'], 'id_cod_dependencia' => $datosFormularios['id_cod_dependencia'],
                'id_rango' => $datosFormularios['id_rango'], 'id_categoria' => $datosFormularios['id_categoria'],
                'n_placa_policial' => $datosFormularios['n_placa_policial'],
                'id_departamento' => $datosFormularios['id_departamento']];

            $id_usuario = $datosFormularios['id_usuario'];
            $id_persona = ['id_persona' => $datosFormularios['id_persona']];
            $estado = ['primer_ingreso' => 2];
            unset($datosFormularios);

            $Date = date('Y-m-d');

            $fecha_minima = date('1910-01-01');

            $identidad = $datos_persona['dni'];
//            validar que no existe
            $info_afiliado = $afiliados_model->existePorIdentidad($identidad);

            if ($info_afiliado) {

                unset($_SESSION['inicio_1']);

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Identidad ya registrada , por favor verifique.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            if ($datos_persona['fecha_nacimiento'] < $fecha_minima || $datos_persona['fecha_nacimiento'] > $Date) {
                unset($_SESSION['inicio_1']);

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Fecha de nacimiento inválida.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            if ($personas_model->actualizar($id_persona, $datos_persona)) {



                $datosprofecionales_model->personalNuevo($datos_pro);

                $datos_afi = ['fecha_afiliacion' => \date('Y-m-d'), 'id_persona' => $id_persona['id_persona']];

                $afiliados_model->afiNuevo($datos_afi);

                $usuarios_model->actualizar($id_usuario, $estado);

                //                          GUARDAR A BITACORA
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdObjtoUsuarios();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['inicio_1']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['inicio_1']['usuario'], 'Accion' => 'Primer login',
                    'descripcion' => $descripcion['descripcion'] = " El usuario  {$_SESSION['inicio_1']['usuario']} realizo su primer login "];

                $utl_bitacora->guardarBitacora($bitacora);

                unset($_SESSION['inicio_1']);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Afiliado Guardado', 'texto' => "El Afiliado fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            } else {

                unset($_SESSION['inicio_1']);

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar al afiliado, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }
        } else {
            //Normal GET


            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $preguntas1 = $this->getPreguntasPendientes($_SESSION['inicio_1']['id_usuario']);

            $resp_usuario_model = new Respuestausuario($this->dbAdapter);
            $respuestas = $resp_usuario_model->getRespuestasUsuario($_SESSION['inicio_1']['id_usuario']);

            $total_respuestas = \count($respuestas);

            $esciviles_model = new Esciviles($this->dbAdapter);
            $estados = $esciviles_model->getEscivil();

            $sangres_model = new Sangres($this->dbAdapter);
            $sangre = $sangres_model->getSangre();

            $asignaciones_model = new Asignaciones($this->dbAdapter);
            $asignaciones = $asignaciones_model->getAsignacion();

            $dependencias_model = new Dependencias($this->dbAdapter);
            $dependencias = $dependencias_model->getDependencia();

            $rangos_model = new Rangos($this->dbAdapter);
            $rangos = $rangos_model->getRango();

            $categorias_model = new Categorias($this->dbAdapter);
            $categorias = $categorias_model->getCategoria();

            $vista = new ViewModel(array('pregu' => $preguntas1, 'ran' => $rangos,
                'asi' => $estados, 'dep' => $dependencias, 'cat' => $categorias,
                'san' => $sangre, 'depa' => $asignaciones, 'respuestas' => $respuestas, 'tr' => $total_respuestas)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function guardarrespuestaasyncAction() {

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $resp_usuario_model = new Respuestausuario($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id_usuario = $datosFormularios['id_usuario'];

            $fecha_actual = \date('Y-m-d H:i:s');

            $datosFormularios['fecha_creacion'] = $fecha_actual;
            $datosFormularios['fecha_modificacion'] = $fecha_actual;

            if ($resp_usuario_model->agregarNuevo($datosFormularios) > 0) {

                $preguntas = $this->getPreguntasPendientes($id_usuario);

                $txt_1 = "Seleccione una pregunta";

                if (!$preguntas) {
                    $txt_1 = "No hay mas preguntas";
                }

                $combo_preguntas = "<option value='' >$txt_1</option>";
                foreach ($preguntas as $d) {
                    $combo_preguntas .= "<option  title='{$d['pregunta']}' value='{$d['id']}' >{$d['pregunta']} </option>";
                }

                $r['estado'] = "ok";
                $r['preguntas'] = $combo_preguntas;

                echo \json_encode($r);
                exit;
            } else {
                $r['estado'] = "error";
                $r['mensaje'] = "Ocurrió un error al guardar la respuesta, intentelo después.";

                echo \json_encode($r);
                exit;
            }
            exit;
        } else if ($this->getRequest()->isPost()) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function eliminarrespuestaasyncAction() {

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $resp_usuario_model = new Respuestausuario($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id_usuario = $datosFormularios['id_usuario'];
            $id_pregunta = $datosFormularios['id_pregunta'];

            if ($resp_usuario_model->borrar($id_pregunta, $id_usuario) > 0) {

                $preguntas = $this->getPreguntasPendientes($id_usuario);

                $txt_1 = "Seleccione una pregunta";

                if (!$preguntas) {
                    $txt_1 = "No hay mas preguntas";
                }

                $combo_preguntas = "<option value='' >$txt_1</option>";
                foreach ($preguntas as $d) {
                    $combo_preguntas .= "<option title='{$d['pregunta']}' value='{$d['id']}' >{$d['pregunta']} </option>";
                }

                $r['estado'] = "ok";
                $r['preguntas'] = $combo_preguntas;

                echo \json_encode($r);
                exit;
            } else {
                $r['estado'] = "error";
                $r['mensaje'] = "Ocurrió un error al eliminar la respuesta, intentelo después.";

                echo \json_encode($r);
                exit;
            }


            exit;
        } else if ($this->getRequest()->isPost()) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function cerrarsesioneAction() {
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

    public function cerrarsesionAction() {

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX

            exit;
        } else if ($this->getRequest()->isPost()) {

            if ((isset($_SESSION['cerrar_sesion']))) {


                //            GUARDADO A LA BITACORA
                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $sesiones_model = new Sesiones($this->dbAdapter);
                $usuarioc = ['usuario' => $_SESSION['cerrar_sesion']['usuario']];

                $sesiones_model->borrar($usuarioc);

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdObjetoCerrar();

                $bitacora = ['id_usuario' => $_SESSION['cerrar_sesion']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['cerrar_sesion']['usuario'], 'Accion' => 'Se cerro la sesion',
                    'descripcion' => $descripcion['descripcion'] = " Se cerro la sesion del usuario {$_SESSION['cerrar_sesion']['usuario']}"];

                $utl_bitacora->guardarBitacora($bitacora);
            }
            unset($_SESSION['cerrar_sesion']);
            unset($_SESSION['auth']);

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {


            if (isset($_SESSION['auth'])) {

                //            GUARDADO A LA BITACORA
                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $sesiones_model = new Sesiones($this->dbAdapter);
                $usuarioc = ['usuario' => $_SESSION['auth']['usuario']];

                $sesiones_model->borrar($usuarioc);

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdObjetoCerrar();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Se cerro la sesion',
                    'descripcion' => $descripcion['descripcion'] = " Se cerro la sesion del usuario {$_SESSION['auth']['usuario']}"];

                $utl_bitacora->guardarBitacora($bitacora);
            }



            unset($_SESSION['auth']);
            unset($_SESSION['cerrar_sesion']);
            unset($_SESSION['inicio_1']);
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

//    public function contraseniaAction() {
////        if (($_SESSION['auth'])) {
////           
////        }
//        if ($this->getRequest()->isXmlHttpRequest()) {
//            //para AJAX
//            exit;
//        } else if ($this->getRequest()->isPost()) {
//            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
//
//            //$id = $this->params()->fromRoute("id", null);
//            //$datosFormularios = $this->request->getPost()->toArray();
//        } else {
//            //Normal GET
//            $vista = new ViewModel([]); //Instancia de la vista
//            $this->layout(); //Parametro pasado al layout Titulo de la página
//            return $vista;
//        }
//    }

    public function recupassAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $usuarios_model = new Usuarios($this->dbAdapter);
            $preguntas_model = new Preguntasusuarios($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $datosFormularios['usuario'] = \mb_strtoupper($datosFormularios['usuario']);

            $exr_respuesta = "/^([a-z0-9A-Z ,.#ñÑáéíóúÁÉÍÓÚ]{1,100})$/";

            $exr_usuario = "/^([0-9A-Za-zñÑ]{5,20})$/";

            //Validar por expresion regular los datos por ataques
            $respuestaCorrecto = \preg_match($exr_respuesta, $datosFormularios['respuesta']);

            $usurioCorrecto = \preg_match($exr_usuario, $datosFormularios['usuario']);

            if (!$respuestaCorrecto || !$usurioCorrecto) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Datos incorrectos.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/recupass");
            }

            $info_usuario = $usuarios_model->getPorUsuario($datosFormularios['usuario']);

            // VALIDAR USUARIO

            if (!$info_usuario) {
                $_SESSION['mnsError'] = array('titulo' => 'Usuario No Encontrado!', 'texto' => 'Asegurese de escribir correctamente su nombre de usuario.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/recupass");
            }

            $id = $info_usuario['id_usuario'];
            $id_pregunta = $datosFormularios['Id_pregunta'];
            $respuesta = $datosFormularios['respuesta'];
            $validar_respuesta = $preguntas_model->getValidarRespuesta($id, $id_pregunta, $respuesta);

            if ($validar_respuesta) {

                $marca['recu_pregunta'] = 1;

                $intentos_db = 0;

                $array_actulizar = ['intentos' => $intentos_db];

                $usuarios_model->actualizar($id, $array_actulizar);

                $usuarios_model->actualizar($id, $marca);

                $_SESSION['reset_pass']['id_usuario'] = $info_usuario['id_usuario'];
                $_SESSION['reset_pass']['nombres'] = $info_usuario['nombres'];
                $_SESSION['reset_pass']['apellidos'] = $info_usuario['apellidos'];
                $_SESSION['reset_pass']['pass_min'] = $_SESSION['parametros']['min_len_pass'];
                $_SESSION['reset_pass']['pass_max'] = $_SESSION['parametros']['max_len_pass'];

                $_SESSION['mnsAutoInfo'] = array('titulo' => 'Cambio de Contraseña!', 'texto' => "Cambie su contraseña por una de su elección.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/cambiocontrasena");
            } elseif (!$validar_respuesta) {

                $intentos_db = $info_usuario['intentos'] + 1;

                $array_actulizar = ['intentos' => $intentos_db];

                $usuarios_model->actualizar($id, $array_actulizar);
                $_SESSION['mnsError'] = array('titulo' => 'Advertencia!', 'texto' => "Respuesta incorrecta.");

                if ($intentos_db == ($_SESSION['parametros']['max_error_pass'] - 1)) {
                    $_SESSION['mnsWar'] = array('titulo' => 'Advertencia!', 'texto' => "Su usuario será inactivado en el siguiente intento fallido.");
                }


                if ($intentos_db >= $_SESSION['parametros']['max_error_pass']) {
                    $array_actulizar['estado_u'] = '2';
                    $usuarios_model->actualizar($id, $array_actulizar);

                    $_SESSION['mnsWar'] = array('titulo' => 'Advertencia!', 'texto' => "Su usuario ha sido bloqueado, comuniquese con el administrador.");
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
                }
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/recupass");
            }
        } else {
            //Normal GET


            $preguntas_model = new Preguntas($this->dbAdapter);

            $preguntas = $preguntas_model->getPreguntasActivas();

            $vista = new ViewModel(array('pre' => $preguntas)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function recpassmailAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $usuarios_model = new Usuarios($this->dbAdapter);

            //$id = $this->params()->fromRoute("id", null);
            $datosFormularios = $this->request->getPost()->toArray();

            $correo = $datosFormularios['correo'];

            $exr_pass = "/^(([^<>()\[\]\\.,;:\s@”]+(\.[^<>()\[\]\\.,;:\s@”]+)*)|(“.+”))@((\[[0–9]{1,3}\.[0–9]{1,3}\.[0–9]{1,3}\.[0–9]{1,3}])|(([a-zA-Z\-0–9]+\.)+[a-zA-Z]{2,}))$/";

            //Validar por expresion regular los datos por ataques
            $correoCorrecto = \preg_match($exr_pass, $correo);

            if (!$correoCorrecto) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Datos incorrectos.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/recupass");
            }


            $info_usuario = $usuarios_model->getPorEmail($correo);

            if (!$info_usuario) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Correo inválido.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/recupass");
            }

            $envio_correo = new EnvioCorreos();

            $usuario = $info_usuario['usuario'];
            $nombre_completo = $info_usuario['nombres'];
            $id_usuario = $info_usuario['id_usuario'];

            $fecha = \date('Y-m-d H:i:s');

            $datos_pass = $usuario . $fecha;

            $pass_hash = \hash('sha256', $datos_pass . $this->salt);

            $pass_recuperacion = \substr($pass_hash, 0, $_SESSION['parametros']['max_len_pass'] - 5);

            $pass_hash_actualizar = \hash('sha256', $pass_recuperacion . $this->salt);

            $array_actualizar = ['contrasenia' => $pass_hash_actualizar, 'reseteo_clave' => '1', 'fecha_reset_pass' => $fecha];

            if ($usuarios_model->actualizar($id_usuario, $array_actualizar)) {

                //Enviar correo
                $contenido_correo = "<div>"
                        . "<h3>Recuperación de contraseña</h3>"
                        . "<p>"
                        . "Estimado <b>$nombre_completo</b> ha solicitado la recuperación de la contraseña, por favor ingrese con los siguientes datos:<br><br>"
                        . "<b>Usuario:</b> $usuario<br>"
                        . "<b>Contraseña:</b> $pass_recuperacion<br><br>"
                        . ""
                        . "La contraseña vence en {$_SESSION['parametros']['max_time_recu_pass']} horas."
                        . "</p>"
                        . "</div>";

                $asunto_correo = "Recuperación de contraseña " . \date('d/m/Y H:i:s');
                if ($envio_correo->enviarCorreo($contenido_correo, $asunto_correo, $correo)) {
                    //guardar bitacora

                    $_SESSION['mnsAutoInfo'] = array('titulo' => 'Correo Enviado!', 'texto' => "Recibirá un correo con las instrucciones para recuperar la contraseña.");

                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
                } else {
                    $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Falló al enviar correo.');

                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/recupass");
                }
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error en el intento de restableser la contraseña.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }
        } else {
            //Normal GET            
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function autoregistroAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $personas_model = new Personas($this->dbAdapter);
            $usuarios_model = new Usuarios($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

            $exr_pass = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&#._$($)$-$])[A-Za-z\d$@$!%*?&#._$-$]*$/";

            $exr_usuario = "/^[A-Z0-9a-zñÑ]{5,20}$/";

            $exr_nombres = "/^[A-Za-zñÑ]{5,50}$/";

            $exr_apellidos = "/^[A-Za-zñÑ]{1,50}$/";

            $exr_passc = "/^(([^<>()\[\]\\.,;:\s@”]+(\.[^<>()\[\]\\.,;:\s@”]+)*)|(“.+”))@((\[[0–9]{1,3}\.[0–9]{1,3}\.[0–9]{1,3}\.[0–9]{1,3}])|(([a-zA-Z\-0–9]+\.)+[a-zA-Z]{2,}))$/";

            $passCorrecto = \preg_match($exr_pass, $datosFormularios['contrasenia']);

            $usurioCorrecto = \preg_match($exr_usuario, $datosFormularios['usuario']);
            $nombresCorrecto = \preg_match($exr_nombres, $datosFormularios['nombres']);
            $apellidosCorrecto = \preg_match($exr_apellidos, $datosFormularios['apellidos']);

            $correo = $datosFormularios['correo'];

            //Validar por expresion regular los datos por ataques
            $correoCorrecto = \preg_match($exr_passc, $correo);

            if (!$passCorrecto || !$usurioCorrecto || !$correoCorrecto || !$nombresCorrecto || !$apellidosCorrecto) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Datos incorrectos.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }



            $datosFormularios['contrasenia'] = hash('sha256', $datosFormularios['contrasenia'] . $this->salt);

            $datos_u = ['usuario' => \mb_strtoupper($datosFormularios['usuario']),
                'contrasenia' => $datosFormularios['contrasenia'], 'estado_u' => '2', 'id_rol' => '4',
                'fecha_reset_pass' => $datosFormularios['fecha_reset_pass'], 'fecha_creacion' => $datosFormularios['fecha_creacion']];

            unset($datosFormularios['usuario'],
                    $datosFormularios['contrasenia'], $datosFormularios['rpass'], $datosFormularios['fecha_creacion'],
                    $datosFormularios['fecha_reset_pass']);

            $datosFormularios['dni'] = $datosFormularios['correo'];

            //conversión a mayúsculas
            $datosFormularios['nombres'] = \mb_strtoupper($datosFormularios['nombres']);
            $datosFormularios['apellidos'] = \mb_strtoupper($datosFormularios['apellidos']);

            $usuario = $datos_u['usuario'];
            $correo_usuario = $datos_u['correo'];

            // VALIDAR CORREO Y USUARIOS NO ESTEN REPETIDOS

            $validar_existenciac = $usuarios_model->getExisteCorreo($correo_usuario);
            $validar_existenciau = $usuarios_model->getExisteUsuario($usuario);

            if ($validar_existenciau || $validar_existenciac) {

                $_SESSION['mnsError'] = array('titulo' => 'Repetidos!', 'texto' => 'Usuario o Correo ya estan registrados.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/autoregistro");
            }


            if ($personas_model->agregarpNuevo($datosFormularios)) {

                $id_persona = $personas_model->getIdReciente($datosFormularios);

                $datos_u['id_persona'] = $id_persona;

                $usuarios_model->agregarNuevo($datos_u);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Usuario Registrado!', 'texto' => "El Usuario fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al crear al usuario, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }
        } else {
            //Normal GET

            $_SESSION['parametros']['min_len_pass'];
            $_SESSION['parametros']['max_len_pass'];

            $vista = new ViewModel(array('min' => $_SESSION['parametros']['min_len_pass'],
                'max' => $_SESSION['parametros']['max_len_pass']));
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function correoAction() {
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

    public function contrasenaAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $usuarios_model = new Usuarios($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $pas_antigua = \hash('sha256', $datosFormularios['pass_anterior'] . $this->salt);

            $info_usuario = $usuarios_model->validarPass($_SESSION['auth']['usuario'], $pas_antigua);

            if (!$info_usuario) {
                $_SESSION['mnsError'] = array('titulo' => 'Error al actualizar!', 'texto' => "La Contraseña anterior es incorrecta.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            } else {

                unset($datosFormularios['rpass'], $datosFormularios['pass_anterior']);

                $datosFormularios['contrasenia'] = \hash('sha256', $datosFormularios['contrasenia'] . $this->salt);

                $datosFormularios['reseteo_clave'] = "0";

                $datosFormularios['fecha_vencimiento'] = \date('Y-m-d');

                $usuarios_model->actualizar($_SESSION['auth']['id_usuario'], $datosFormularios);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Contraseña Guardado!', 'texto' => "La Contraseña se cambio exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }
        } else {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function editarAction() {
        $objeto = 'Seguridad';
        $permiso = 'permiso_actualizacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $usuarios_model = new Usuarios($this->dbAdapter);
        $preguntas_model = new Preguntas($this->dbAdapter);
        $roles_model = new Roles($this->dbAdapter);
        $personas_model = new Personas($this->dbAdapter);
        $preguntasusuarios = new Preguntasusuarios($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $sucursales_model = new Sucursales($this->dbAdapter);
        $id = $this->params()->fromRoute("id", null);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            $info_usuario = $usuarios_model->getPorId($id);

            $contra = $info_usuario['contrasenia'];

            $id_persona = ['id' => $datosFormularios['id']];
            $correo = ['correo' => $datosFormularios ['correo']];
            $id_pregunta = ['Id_pregunta' => $datosFormularios['Id_pregunta']];

            $datos_usuario = ['usuario' => $datosFormularios['usuario'], 'id_sucursal' => $datosFormularios['id_sucursal'],
                'contrasenia' => $datosFormularios['contrasenia'], 'id_rol' => $datosFormularios['id_rol'],
                'fecha_modificacion' => $datosFormularios['fecha_modificacion'],
                'modificado_por' => $datosFormularios['modificado_por'], 'estado_u' => $datosFormularios['estado_u']];

            if ($datos_usuario['contrasenia'] != $contra) {

                $datos_usuario['contrasenia'] = hash('sha256', $datos_usuario['contrasenia'] . $this->salt);
            }

            $datos_usuario['intentos'] = 0;

//            echo "<pre>";
//            print_r($contra);
//            print_r($datos_usuario);
//            exit;

            if ($usuarios_model->actualizar($id, $datos_usuario)) {

                $personas_model->actualizar($id_persona, $correo);

                unset($datosFormularios['correo'], $datosFormularios['id'], $datosFormularios['id_rol'],
                        $datosFormularios['usuario'], $datosFormularios['contrasenia'], $datosFormularios['fecha_modificacion'],
                        $datosFormularios['modificado_por'], $datosFormularios['estado_u'], $datosFormularios['id_sucursal']);

                $preguntasusuarios->actualizar($id, $id_pregunta, $datosFormularios);

                // GUARDAR A BITACORA
                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdObjtoUsuarios();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Modificacion de usuario',
                    'descripcion' => $descripcion['descripcion'] = " Se modifico la infiromacion del usuario {$datos_usuario['usuario']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Usuario Actualizado!', 'texto' => "La informacion se cambio exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/editar/$id");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al editar al usuario, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios");
            }
        } else {
            //Normal GET
            //balidacion de la existencias del afiliado       
//            if (!$info_afiliado) {
//                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'El afiliado no se encuentra o esta inactivo, intentelo después.');
//                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
//            }

            $usuarios = $usuarios_model->getInfousuarioEditar($id);
            $preguntas = $preguntas_model->getPreguntasActivas();
            $roles = $roles_model->getRoles();
            $estados_usuarios = $usuarios_model->getEstadosUsuarios();
            $sucursales = $sucursales_model->getSucursales();

            $vista = new ViewModel(array('info' => $usuarios, 'pre' => $preguntas, 'roles' => $roles,
                'estados' => $estados_usuarios, 'sucursales' => $sucursales)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarAction() {
        $objeto = 'Seguridad';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $usuarios_model = new Usuarios($this->dbAdapter);
        $personas_model = new Personas($this->dbAdapter);
        $afiliados_model = new Afiliados($this->dbAdapter);
        $bitacoras_model = new Bitacoras($this->dbAdapter);
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {



            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $id = $this->params()->fromRoute("id", null);

            $info_usuario = $usuarios_model->getInfousuario($id);

            $id_persona = ['id' => $info_usuario['id_persona']];

//              validar que el usuario no tenga relación en otras tablas

            $validar_afiliado = $afiliados_model->getInfoPersona($id_persona['id']);
            $validar_vitacora = $bitacoras_model->getInfoUsuarrio($id);

            if ($validar_afiliado || $validar_vitacora) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'El usuario no se puede borrar por que ya dejo marcar en los '
                    . 'registros del sistema.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios");
            } else {
                $usuarios_model->borrar($id);
                $personas_model->borrar($id_persona);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdObjtoUsuarios();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Usuario',
                    'descripcion' => $descripcion['descripcion'] = " Se elimino el usuario con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Usuario Borrado!', 'texto' => "El usuario fue borrado exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios");
            }


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function verAction() {
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

    private function getPreguntasPendientes($id_usuario) {
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $preguntas_model = new Preguntas($this->dbAdapter);
        $preguntas_respondidas = $preguntas_model->getPreguntaRespondidas($id_usuario);

        $preguntas_activas = $preguntas_model->getPreguntasActivas();

        $r = [];

        //Reescribir el arreglo de salida usando al ID de las preguntas como llave de las filas
        foreach ($preguntas_activas as $f) {
            $id = $f['id'];
            $r[$id] = $f;
        }

        //Eliminar las preguntas que estén en el array de preguntas respondidas
        foreach ($preguntas_respondidas as $pr) {
            $id_preg_respondida = $pr['id_respondida'];

            if (\array_key_exists($id_preg_respondida, $r)) {
                unset($r[$id_preg_respondida]);
            }
        }

        return $r;
    }

    public function cambiocontrasenaAction() {

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $usuarios_model = new Usuarios($this->dbAdapter);
            $histocontra_model = new Histocontasena($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $pass = $datosFormularios['pass'];
            $rpass = $datosFormularios['rpass'];

            $id = $datosFormularios['id'];

            $exr_pass = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[$@$!%*?&#._$($)$-$])[A-Za-z\d$@$!%*?&#._$-$]*$/";

            //Validar por expresion regular los datos por ataques
            $passCorrecto = \preg_match($exr_pass, $pass);

            if (!$passCorrecto) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Datos incorrectos.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }


            $info_usuario = $usuarios_model->getPorId($id);

            if ($pass != $rpass) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => "Las Contraseñas son distintas.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            if ($info_usuario['primer_contra'] == 1 || $info_usuario['recu_pregunta'] == 1) {

                $histo_contra_usuario = $histocontra_model->getHitoContraUsuario($id);

                $datosFormularios['contrasenia'] = \hash('sha256', $pass . $this->salt);

                $tiempo_borrar = 7889232;

                $fecha = \date('Y-m-d H:i:s');

                $t_fecha = \strtotime($fecha);

                foreach ($histo_contra_usuario as $fila) {



                    $t_fecfa_contra = \strtotime($fila['fecha']); //pasa a segundos

                    $tiempo_transcurrido_h = $t_fecha - $t_fecfa_contra;

                    $contrasena = $fila['contrasena'];

                    if ($tiempo_transcurrido_h >= $tiempo_borrar) {

                        $fecha_h = $fila['fecha'];

                        $histocontra_model->borrar($id, $fecha_h);
                    }

                    if ($contrasena == $datosFormularios['contrasenia']) {

                        $_SESSION['mnsError'] = array('titulo' => 'Contraseña Repetida!', 'texto' => "Debe ingresar una contraseña que no haya sido utilizada por usted.");

                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
                    }
                }

                $datosFormularios['primer_contra'] = 0;

                $datosFormularios['recu_pregunta'] = 0;

                unset($datosFormularios['pass']);
                unset($datosFormularios['rpass']);

                $usuarios_model->actualizar($id, $datosFormularios);

                $contrasena_h = ['id_usuario' => $id, 'contrasena' => $datosFormularios['contrasenia'],
                    'fecha' => $fecha];

                $histocontra_model->agregarNuevo($contrasena_h);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Contraseña Guardado!', 'texto' => "La Contraseña por pregunta se cambio exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }


            if (!$info_usuario) {
                $_SESSION['mnsError'] = array('titulo' => 'Error al actualizar!', 'texto' => "La Contraseña anterior es incorrecta.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            } else {

                $t_max_time_recu_pass = $_SESSION['parametros']['max_time_recu_pass'] * 3600;

                $t_fecha_reset_pass = \strtotime($info_usuario['fecha_reset_pass']); //pasa a segundos

                $fecha = \date('Y-m-d H:i:s');

                $t_fecha = \strtotime($fecha);

                $tiempo_transcurrido = $t_fecha - $t_fecha_reset_pass;

                if ($tiempo_transcurrido > $t_max_time_recu_pass) {
                    $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => "Ya venció el tiempo para la contraseña utilizada, solicite nuevamente el cambio.");
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
                }


                $histo_contra_usuario = $histocontra_model->getHitoContraUsuario($id);

                $datosFormularios['contrasenia'] = \hash('sha256', $pass . $this->salt);

                $tiempo_borrar = 7889232;

//                echo "<pre>";
//                print_r($datosFormularios['contrasenia']);
//                exit;

                foreach ($histo_contra_usuario as $fila) {

                    $t_fecfa_contra = \strtotime($fila['fecha']); //pasa a segundos

                    $tiempo_transcurrido_h = $t_fecha - $t_fecfa_contra;

                    $contrasena = $fila['contrasena'];

                    if ($tiempo_transcurrido_h >= $tiempo_borrar) {

                        $fecha_h = $fila['fecha'];

                        $histocontra_model->borrar($id, $fecha_h);
                    }

                    if ($contrasena == $datosFormularios['contrasenia']) {

                        $_SESSION['mnsError'] = array('titulo' => 'Contraseña Repetida!', 'texto' => "Debe ingresar una contraseña que no haya sido utilizada por usted.");

                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
                    }
                }



                unset($datosFormularios['pass']);
                unset($datosFormularios['rpass']);

                $datosFormularios['reseteo_clave'] = "0";

                $datosFormularios['fecha_vencimiento'] = \date('Y-m-d');

                $datosFormularios['primer_contra'] = 0;

                $datosFormularios['recu_pregunta'] = 0;

                $usuarios_model->actualizar($id, $datosFormularios);

                $contrasena_h = ['id_usuario' => $id, 'contrasena' => $datosFormularios['contrasenia'],
                    'fecha' => $fecha];

                $histocontra_model->agregarNuevo($contrasena_h);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Contraseña Guardado!', 'texto' => "La Contraseña se cambio exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }
        } else {
            //Normal GET

            if (!isset($_SESSION['reset_pass'])) {
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }
            $dat_usuario = $_SESSION['reset_pass'];
            unset($_SESSION['reset_pass']);

            $vista = new ViewModel(['du' => $dat_usuario]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function cambiocorreoAction() {
        if (!isset($_SESSION['auth'])) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $usuarios_model = new Usuarios($this->dbAdapter);

            $personas_model = new Personas($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $pas = \hash('sha256', $datosFormularios['contrasenia'] . $this->salt);

            $info_usuario = $usuarios_model->validarPass($_SESSION['auth']['usuario'], $pas);

            $correo = $datosFormularios['correo'];

            $exr_pass = "/^(([^<>()\[\]\\.,;:\s@”]+(\.[^<>()\[\]\\.,;:\s@”]+)*)|(“.+”))@((\[[0–9]{1,3}\.[0–9]{1,3}\.[0–9]{1,3}\.[0–9]{1,3}])|(([a-zA-Z\-0–9]+\.)+[a-zA-Z]{2,}))$/";

            //Validar por expresion regular los datos por ataques
            $correoCorrecto = \preg_match($exr_pass, $correo);

            if (!$correoCorrecto) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Datos incorrectos.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            if (!$info_usuario) {
                $_SESSION['mnsError'] = array('titulo' => 'Error al actualizar!', 'texto' => "La Contraseña es incorrecta.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            $validar_correo = $usuarios_model->getPorEmail($correo);

            if ($validar_correo) {
                $_SESSION['mnsError'] = array('titulo' => 'Error al actualizar!', 'texto' => "Correo ya registrado.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }

            unset($datosFormularios['contrasenia']);

            if ($personas_model->actualizar($info_usuario['id_persona'], $datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Correo Actualizado!', 'texto' => "El correo se actualizado  exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error Al Actualizar!', 'texto' => "Error al actualizar el correo.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

}
