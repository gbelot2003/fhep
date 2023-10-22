<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Afiliados;
use Application\Model\Dependencias;
use Application\Model\Personas;
use Application\Model\Rangos;
use Application\Model\Asignaciones;
use Application\Model\Categorias;
use Application\Model\Sangres;
use Application\Model\Esciviles;
use Application\Model\Datosprofecionales;
use Application\Model\Parentescos;
use Application\Model\Objetos;
use Application\Model\Bitacoras;
use Application\Model\Citas;
use Application\Model\Usuarios;
use Application\Model\Especialidades;
use Application\Model\Medicos;
use Zend\View\Model\JsonModel;
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Texto;
use Application\Extras\Utilidades\Bitacora;

class AfiliadosController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function afiliadosAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['plan_mensual'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $filiados_model = new Afiliados($this->dbAdapter);
//
//            $datosFormularios = $this->request->getPost()->toArray();
//            echo "<pre>";
//            print_r($datosFormularios);
//            exit;
        } else {
            //Normal GET
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $especialidades_model = new Especialidades($this->dbAdapter);

            $especialidades = $especialidades_model->getEspecialidad();

            $vista = new ViewModel(array('espe' => $especialidades)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablaafiliadosAction() {

//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['conf_cargos'] != '1') {            
//            exit;
//        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $afiliados_model = new Afiliados($this->dbAdapter);
        $afiliados = $afiliados_model->getTblAfiliados();

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
        $grid->addColumn('dni', 'N° de Expediente', 'string', NULL, false);
        $grid->addColumn('nombre_completo', 'Nombre Completo', 'varchar', NULL, false); //ocupo nombre completo
        $grid->addColumn('tipo_de_afiliado', 'Tipo de Afiliado', 'varchar', NULL, false);
        $grid->addColumn('nombre_ea', 'Estado', 'varchar', NULL, false);
        $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_persona');

        $grid->renderXML($afiliados);
        exit;
    }

    public function exportexcelpacientesAction() {
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

            $datos_excel = $citas_model->getTblPacientes($id_suscursal);

            $total_filas = \count($datos_excel);

            // FIN PASO 1
            //PASO 2 -> VARIABLES PARA LA CREACION Y CONTROL DEL EXCEL
            $txt_fecha = \date('d-m-Y_H-i-s');
            $titulo = "Afiliados $txt_fecha";
            $nombre_archivo = "TABLA DE AFILIADOS $txt_fecha.xlsx"; //NOMBRE DEL ARCHIVO
            //Encabezados de las columnas
            $titulos_columnas = ['N°', 'Cód. Afiliado', 'N° Expediente', 'Nombre Completo', 'Especialidad', 'Médico', 'Estado Cita', 'Fecha de consulta'];

            //INFORMACIÓN DEL ENCABEZADO
            $encabezado_linea_1 = "FUNDACIÓN HOSPITAL DE EMERCENCIA POLICIAL";
            $encabezado_linea_2 = "NOMBRE DEL DEPARTAMENTO";
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

    public function agregarafiliadoAction() {

        $objeto = 'Afiliados';
        $permiso = 'permiso_insercion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $afiliados_model = new Afiliados($this->dbAdapter);
            $personas_model = new Personas($this->dbAdapter);
            $datosprofecionales_model = new Datosprofecionales($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $utl_bitacora = new Bitacora($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();
              
            $identidad = $datosFormularios['dni'];
//            validar que no existe
            $info_afiliado = $afiliados_model->existePorIdentidad($identidad);
            if ($info_afiliado) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Identidad ya registrada , por favor verifique.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados/agregarafiliado");
            }

              $fecha_minima = date('1910-01-01'); 
            
            if ($datosFormularios['fecha_nacimiento'] < $fecha_minima)  {
                 $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Fecha de nacimiento inválida.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }
            
            unset($datosFormularios['tipoformulario'], $datosFormularios['confirmacion']);
            //Validaciones

            /*
              $exr_pass = '/^([a-fA-F0-9]*)$/';

              //Validar por expresion regular los datos por ataques
              $nombreUsuarioCorrecto = \preg_match('/^([a-zA-Z0-9áéíóúÁÉÍÓÚñÑ_-]*)$/', $nombreUsu);
              $passCorrecto = \preg_match($exr_pass, $pass);


              if (!$nombreUsuarioCorrecto || !$passCorrecto) {
              $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Datos incorrectos.');
              return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
              }
             */

//             $array_prueba['id_cod_dependencia' ] = $datosFormularios['id_cod_dependencia'];

            $datos_pro = ['id_cod_dependencia' => $datosFormularios['id_cod_dependencia'],
                'id_rango' => $datosFormularios['id_rango'], 'id_categoria' => $datosFormularios['id_categoria'],
                'n_placa_policial' => $datosFormularios['n_placa_policial'],
                'id_departamento' => $datosFormularios['id_departamento']];

            unset($datosFormularios['id_cod_dependencia'], $datosFormularios['id_departamento'],
                    $datosFormularios['id_rango'], $datosFormularios['id_categoria'],
                    $datosFormularios['n_placa_policial']);

            if ($personas_model->agregarpNuevo($datosFormularios)) {

                $id_persona = $personas_model->getIdReciente($datosFormularios);

                $datos_afi['id_persona'] = $id_persona;
                $datos_afi['fecha_afiliacion'] = \date('Y-m-d H:i:s');
                $datos_pro['id_persona'] = $id_persona;

                $datosprofecionales_model->personalNuevo($datos_pro);

                $afiliados_model->afiNuevo($datos_afi);

                $id_objetos = $objetos_model->getIdobjetoAfiliados();

                //  GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de un afiliado', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => $descripcion['descripcion'] = " se creo el afiliado con # de expediente {$datosFormularios['dni']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $btn_imprimir = '<a class="btn btn-primary" href="' . $this->getRequest()->getBaseUrl() . '/afiliados/pdffichaafiliado/' . $id_persona . '">'
                        . '<i class="fas fa-printer"></i>  Imprimir Ficha</a>';

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Afiliado guardado!', 'texto' => "El Afiliado fue creado exitosamente.");
                $_SESSION['mnsInfo'] = array('titulo' => 'Afiliado guardado!', 'texto' => "El Afiliado fue creado exitosamente.<br><br>$btn_imprimir");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar al afiliado, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }





//
//
//            $datos_persona['nombre_completo'] = \mb_strtoupper($datosFormularios['nombres']);
//            $datos_persona['cod_rango'] = 1;
//            $datos_persona['n_identidad'] = \mb_strtoupper($datosFormularios['identidad']);
//            $datos_persona['n_placaidp'] = 1;
//            $datos_persona['domicilio_actual'] = \mb_strtoupper($datosFormularios['nombres']);
//
//            if ($personas_model->agregarNuevo($datos_persona)) {
//
//
//                $_SESSION['mnsAutoOK'] = array('titulo' => 'Afiliado guardado!', 'texto' => "El afiliado fua creado exitosamente.");
//                //Redireccionar
//                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
//            } else {
//                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el afiliado, intentelo después.');
//
//                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
//            }
        } else {
            //Normal GET

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $dependencias_model = new Dependencias($this->dbAdapter);
            $dependencias = $dependencias_model->getDependencia();

            $rangos_model = new Rangos($this->dbAdapter);
            $rangos = $rangos_model->getRango();

            $asignaciones_model = new Asignaciones($this->dbAdapter);
            $asignaciones = $asignaciones_model->getAsignacion();

            $categorias_model = new Categorias($this->dbAdapter);
            $categorias = $categorias_model->getCategoria();

            $sangres_model = new Sangres($this->dbAdapter);
            $sangre = $sangres_model->getSangre();

            $esciviles_model = new Esciviles($this->dbAdapter);
            $estados = $esciviles_model->getEscivil();

            $vista = new ViewModel(array('dep' => $dependencias, 'ran' => $rangos, 'asi' => $asignaciones
                , 'cat' => $categorias, 'est' => $estados, 'san' => $sangre)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarafiliadoAction() {

        $objeto = 'Afiliados';
        $permiso = 'permiso_actualizacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $idc = $this->params()->fromRoute("id", null);
        $afiliados_model = new Afiliados($this->dbAdapter);
        $personas_model = new Personas($this->dbAdapter);
        $datosprofecionales_model = new Datosprofecionales($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $datosFormularios = $this->request->getPost()->toArray();

            $datosFormularios['fecha_nacimiento'] = date("Y-m-d", strtotime($datosFormularios['fecha_nacimiento']));

            $fecha_minima = date('1910-01-01'); 
            
            $datos_pro = ['id_cod_dependencia' => $datosFormularios['id_cod_dependencia'],
                'id_rango' => $datosFormularios['id_rango'], 'id_categoria' => $datosFormularios['id_categoria'],
                'n_placa_policial' => $datosFormularios['n_placa_policial'],
                'id_departamento' => $datosFormularios['id_departamento']];

            unset($datosFormularios['id_cod_dependencia'], $datosFormularios['id_departamento'],
                    $datosFormularios['id_rango'], $datosFormularios['id_categoria'],
                    $datosFormularios['n_placa_policial'], $datosFormularios['tipoformulario'],
                    $datosFormularios['confirmacion'], $datosFormularios['id_persona']);
            //balidacion de la existencias del afiliado
            $info_afiliado = $afiliados_model->getAfiliadoInfo($idc);
            
            if ($datosFormularios['fecha_nacimiento'] < $fecha_minima)  {
                 $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Fecha de nacimiento inválida.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }
            
            
            if (!$info_afiliado) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'El afiliado no se encuentra o esta inactivo, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }

//
//            echo "<pre>";
//            print_r($id_persona);
//            print_r($datos_pro);

            $datosprofecionales_model->actualizar($idc, $datos_pro);
            $personas_model->actualizar($idc, $datosFormularios);

            // GUARDAR A BITACORA
            $DateAndTime = date('Y-m-d H:i:s');

            $id_objetos = $objetos_model->getIdobjetoAfiliados();

            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Modificacion de afiliado',
                'descripcion' => $descripcion['descripcion'] = " se modifico la informacion del afiliado con # de expediente {$datosFormularios['dni']}"];

            $utl_bitacora->guardarBitacora($bitacora);

            $_SESSION['mnsAutoOK'] = array('titulo' => 'Afiliado editado!', 'texto' => "El Afiliado fue editado exitosamente.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
        } else {
            //get normal 
            $afiliados_model = new Afiliados($this->dbAdapter);
            $info_afiliado = $afiliados_model->getAfiliadoInfo($idc);

//            
            //balidacion de la existencias del afiliado       
            if (!$info_afiliado) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'El afiliado no se encuentra o esta inactivo, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }


            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $dependencias_model = new Dependencias($this->dbAdapter);
            $dependencias = $dependencias_model->getDependencia();

            $rangos_model = new Rangos($this->dbAdapter);
            $rangos = $rangos_model->getRango();

            $asignaciones_model = new Asignaciones($this->dbAdapter);
            $asignaciones = $asignaciones_model->getAsignacion();

            $categorias_model = new Categorias($this->dbAdapter);
            $categorias = $categorias_model->getCategoria();

            $sangres_model = new Sangres($this->dbAdapter);
            $sangre = $sangres_model->getSangre();

            $esciviles_model = new Esciviles($this->dbAdapter);
            $estados = $esciviles_model->getEscivil();

            $vista = new ViewModel(array('dep' => $dependencias, 'ran' => $rangos, 'asi' => $asignaciones
                , 'cat' => $categorias, 'est' => $estados, 'san' => $sangre, 'afiliado' => $info_afiliado)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarafiliadoAction() {

        $objeto = 'Afiliados';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {



            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $afiliados_model = new Afiliados($this->dbAdapter);
            $personas_model = new Personas($this->dbAdapter);
            $citas_model = new Citas($this->dbAdapter);
            $usuarios_model = new Usuarios($this->dbAdapter);
            $datosprofecionales_model = new Datosprofecionales($this->dbAdapter);
            $id = $this->params()->fromRoute("id", null);

            $info_afiliado = $afiliados_model->getInfoAfiliadoB($id);

            $info_persona = $personas_model->getPorId($id);

            $id_afiliado = $info_afiliado['id'];

            // VALIDAR SY EL AFILIADO TIENE USUARIO
            $validar_usuario = $usuarios_model->getValidarUsuario($id);

            // VALIDAR SY EL AFILIADO TIENE CITAS REGISTRADAS

            $validar_citas = $citas_model->getValidarCitas($id_afiliado);

            if ($validar_citas || $validar_usuario) {

                $_SESSION['mnsAutoError'] = array('titulo' => 'Imposible!', 'texto' => 'El afiliado no se puede borrar ya que dejo marca en los registros del sistema, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            } else {

                $afiliados_model->borrar($id_afiliado);
                $datosprofecionales_model->borrar($id);
                $personas_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoAfiliados();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Afiliado',
                    'descripcion' => $descripcion['descripcion'] = " Se elimino el afiliado con nombre {$info_persona['nombres']}{$info_persona['apellidos']} y N° de Expediente {$info_persona['dni']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Afiliado Eliminado!', 'texto' => "El Afiliado fue eliminado exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function cambiarestadoafiliadoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            
        } else {
            //Normal GET
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $afiliados_model = new Afiliados($this->dbAdapter);

            $personas_model = new Personas($this->dbAdapter);

            $id_persona = $this->params()->fromRoute("id", null);

            $info_afiliado = $afiliados_model->getInfoAfiliadoPersona($id_persona);

            $id_afiliado = $info_afiliado['id_afiliado'];

            if ($info_afiliado['id_status'] == 1) {

                $id_estatus['id_status'] = 2;

                $beneficiarios = $personas_model->getBeneficiarios($id_persona);

                foreach ($beneficiarios as $fila) {

                    if ($fila['id_status'] == '1') {

                        $id_afi = $fila['id_afiliado'];

                        $afiliados_model->actualizar($id_afi, $id_estatus);
                    }
                }
                $afiliados_model->actualizar($id_afiliado, $id_estatus);

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoAfiliados();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Cambio De Estado Afiiado',
                    'descripcion' => "se cambio el estado del afiliado {$info_afiliado['nombre']} con # de expediente{$info_afiliado['dni']} y el de todos sus bebeficiarios a inactivo "];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Afiliado Inactivado!', 'texto' => "El estado del afiliado y sus beneficiarios fue cambiado exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            } elseif (($info_afiliado['id_status'] == 2)) {

                $id_estatus['id_status'] = 1;

                $beneficiarios = $personas_model->getBeneficiarios($id_persona);

                foreach ($beneficiarios as $fila) {

                    if ($fila['id_status'] == '2') {

                        $id_afi = $fila['id_afiliado'];

                        $afiliados_model->actualizar($id_afi, $id_estatus);
                    }
                }

                $afiliados_model->actualizar($id_afiliado, $id_estatus);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoAfiliados();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Cambio De Estado Afiiado',
                    'descripcion' => "se cambio el estado de los afiliado  {$info_afiliado['nombre']} con # de expediente{$info_afiliado['dni']} y el de todos sus bebeficiarios a activo "];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Afiliado Activado!', 'texto' => "El estado del afiliado y sus beneficiarios fue cambiado exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => "El estado del afiliado no se pudo cambiar intentelo despues.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }



            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function agregarbeneficiarioAction() {

        $objeto = 'Afiliados';
        $permiso = 'permiso_insercion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $idc = $this->params()->fromRoute("id", null);
        $afiliados_model = new Afiliados($this->dbAdapter);
        $personas_model = new Personas($this->dbAdapter);
        $parentescos_model = new Parentescos($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $datosFormularios = $this->request->getPost()->toArray();
            $info_afiliado = $afiliados_model->getAfiliadoExiste($idc);

            $identidad = $datosFormularios['dni'];
//           
//             validar que no existe
            $info_dni = $afiliados_model->existePorIdentidad($identidad);
            if ($info_dni) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Identidad ya registrada , por favor verifique.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }
            
              $fecha_minima = date('1910-01-01'); 
            
            if ($datosFormularios['fecha_nacimiento'] < $fecha_minima)  {
                 $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Fecha de nacimiento inválida.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }

            $datos_persona = ['id_padre' => $info_afiliado['id_persona'],
                'dni' => $datosFormularios['dni'], 'nombres' => $datosFormularios['nombres'],
                'apellidos' => $datosFormularios['apellidos'], 'fecha_nacimiento' => $datosFormularios['fecha_nacimiento'],
                'id_tipo_sangre' => $datosFormularios['id_tipo_sangre'], 'id_estado_civil' => $datosFormularios['id_estado_civil']
                , 'departamento' => $datosFormularios['departamento'], 'telefono' => $datosFormularios['telefono']
                , 'celular' => $datosFormularios['celular'], 'domicilio_actual' => $datosFormularios['domicilio_actual'],
                'parentesco' => $datosFormularios['parentesco'], 'genero' => $datosFormularios['genero']];

            unset($datosFormularios);

            if ($personas_model->agregarpNuevo($datos_persona)) {

                $id_persona = $personas_model->getIdReciente($datos_persona);

                $tipoafi = ['id_tipafiliado' => 2];

                $datos_afi = ['id_persona' => $id_persona, 'id_tipafiliado' => $tipoafi['id_tipafiliado']];

                $datos_afi['fecha_afiliacion'] = \date('Y-m-d H:i:s');

                $afiliados_model->afiNuevo($datos_afi);

                // GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoAfiliados();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de beneficiario',
                    'descripcion' => $descripcion['descripcion'] = " se creo un beneficiario # numero de expediente {$datos_persona['dni']} que pertense al afiliado con nombre {$info_afiliado['nombre_completo']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Beneficiario Guardado!', 'texto' => "El  Beneficiario fue creado exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el Benficario, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }
        } else {
            //Normal GET
            $afiliados_model = new Afiliados($this->dbAdapter);
            $info_afiliado = $afiliados_model->getAfiliadoExiste($idc);

            if (!$info_afiliado) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'El afiliado no se encuentra o esta inactivo, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }

//
//            echo "<pre>";
//            print_r($info_afiliado);
//            exit;

            $asignaciones_model = new Asignaciones($this->dbAdapter);
            $asignaciones = $asignaciones_model->getAsignacion();

            $sangres_model = new Sangres($this->dbAdapter);
            $sangre = $sangres_model->getSangre();

            $esciviles_model = new Esciviles($this->dbAdapter);
            $estados = $esciviles_model->getEscivil();
//
//            $parentescos_model = new Parentescos($this->dbAdapter);
//            $paraentescos = $parentescos_model->getParaentescos();

            $vista = new ViewModel(array('beneficiario' => $info_afiliado, 'est' => $estados, 'san' => $sangre,
                'asi' => $asignaciones)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function verbeneficiariosAction() {

        $objeto = 'Afiliados';
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
            $afiliados_model = new Afiliados($this->dbAdapter);
            $id = $this->params()->fromRoute("id", null);

            $info_afiliado = $afiliados_model->getInfoAfiliadoPersona($id);

            $vista = new ViewModel(['id_persona' => $id, 'info_afiliado' => $info_afiliado]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettblbeneficiariosAction() {
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
            $personas_model = new Personas($this->dbAdapter);

            $id = $this->params()->fromRoute("id", null);

            $beneficiarios = $personas_model->getBeneficiarios($id);

            // Incluir el MAIN de la libreria
            require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

            foreach ($beneficiarios as $f => $c) {

                if ($c['id_status'] == '1') {
                    $beneficiarios[$f]['id_status'] = "Activo";
                } else {
                    $beneficiarios[$f]['id_status'] = "Inactivo";
                }
            }

            $grid = new \EditableGrid();
            if (!$beneficiarios) {
                $grid->addColumn('Nombre Completo', 'AUN NO SE HAN  REGISTRADO BENEFICIARIOS', 'string', NULL, false);
                $grid->addColumn('opciones', '', 'html', NULL, false, 'id');
            } else {
                // create a new EditableGrid object
                $grid->addColumn('dni', 'N° Expediente', 'string', NULL, false);
                $grid->addColumn('nombre_completo', 'Nombre Completo', 'string', NULL, false);
                $grid->addColumn('parentesco', 'Parentesco ', 'string', NULL, false);
                $grid->addColumn('id_status', 'Estado ', 'string', NULL, false);
                $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_persona');
            }
            $grid->renderXML($beneficiarios);
            exit;
        }
    }

    public function editarbeneficiarioAction() {

        $objeto = 'Afiliados';
        $permiso = 'permiso_actualizacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $id = $this->params()->fromRoute("id", null);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $datosFormularios = $this->request->getPost()->toArray();
            $personas_model = new Personas($this->dbAdapter);
            $datosFormularios['fecha_nacimiento'] = date("Y-m-d", strtotime($datosFormularios['fecha_nacimiento']));
            unset($datosFormularios['confirmacion']);

            $info_beneficiarios = $personas_model->getPorId($id);

            $fecha_minima = date('1910-01-01'); 
            
            if ($datosFormularios['fecha_nacimiento'] < $fecha_minima)  {
                 $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Fecha de nacimiento inválida.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }
            
            
            if (!$info_beneficiarios) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Informacion del  el benficario no encontrada, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }



            if ($personas_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoAfiliados();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Modificar Beneficiario',
                    'descripcion' => "Se modifico la informcion del beneficiario con # de expediente {$datosFormularios['dni']} y "
                    . "nombre {$datosFormularios['nombres']} {$datosFormularios['apellidos']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Beneficiario Guardado!', 'texto' => "La informacion del beneficiario fue editada exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el Benficario, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }
        } else {
            //Normal GET

            $id = $this->params()->fromRoute("id", null);

            $afiliados_model = new Afiliados($this->dbAdapter);
            $info_beneficiario = $afiliados_model->getInfoBeneficiario($id);

            $id_afiliado = $info_beneficiario['id_padre'];

            $info_afiliado = $afiliados_model->getAfiliadoExiste($id_afiliado);

            $info_beneficiario['nombre_completo'] = $info_afiliado['nombre_completo'];

            $esciviles_model = new Esciviles($this->dbAdapter);
            $estados = $esciviles_model->getEscivil();

            $sangres_model = new Sangres($this->dbAdapter);
            $sangre = $sangres_model->getSangre();

            $asignaciones_model = new Asignaciones($this->dbAdapter);
            $asignaciones = $asignaciones_model->getAsignacion();

            $vista = new ViewModel(array('asi' => $asignaciones, 'est' => $estados, 'san' => $sangre,
                'beneficiario' => $info_beneficiario)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function registrardoctorAction() {

        $objeto = 'Afiliados';
        $permiso = 'permiso_gestion_medicos';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $doctores_model = new Medicos($this->dbAdapter);
            $personas_model = new Personas($this->dbAdapter);
            $especialidades_model = new Especialidades($this->dbAdapter);
            $usuarios_model = new Usuarios($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $info_persona = $personas_model->getPorId($datosFormularios['id_persona']);

            $info_especialidad = $especialidades_model->getPorId($datosFormularios['id_especialidad']);

            $info_usuario = $usuarios_model->getValidarUsuario($datosFormularios['id_persona']);

            if (!$info_usuario) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'El afiliado debe tener un usuario registrado, inténtelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            } else {

                $id_persona_usu = $info_usuario['id_persona'];
            }


            if ($id_persona_usu !== null) {

                $medico_repe = $doctores_model->getValidarMedicoRepetido($id_persona_usu);
            }


            if ($medico_repe) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'El afiliado ya esta registrado como medico.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }

            //GUARDAR A LA TABLA DE MEDICOS

            if ($doctores_model->agregarNuevo($datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoAfiliados();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Registro De Doctro',
                    'descripcion' => "Se registro al afiliado {$info_persona['nombres']} {$info_persona['apellidos']} como doctor en la espcialiadidad "
                    . "{$info_especialidad['nombre_e']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Doctor Resgistrado!', 'texto' => "El Afiliado fue registrado como doctor exitosamente.");

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al registrar al doctor, inténtelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }
        } else {
            //Normal GET



            $vista = new ViewModel(); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function listmedicosAction() {
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
            $especialidades_model = new Especialidades($this->dbAdapter);

            $especialidades = $especialidades_model->getEspecialidad();

            $vista = new ViewModel(array('espe' => $especialidades)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function infomedicoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $medicos_model = new Medicos($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_medico = $medicos_model->getInfoMedico($id);

            echo \json_encode($info_medico);

            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarmedicoAction() {

        $objeto = 'Afiliados';
        $permiso = 'permiso_actualizacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $medicos_model = new Medicos($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];
            $nombre = $datosFormularios['nombre_completo'];
            unset($datosFormularios['nombre_completo']);

            $existe_medico = $medicos_model->getPorId($id);

            if (!$existe_medico) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'La infomacion del medico no se encuentra, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }

            if ($medicos_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoCitas();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Modificar Informacion',
                    'descripcion' => "Se modifico la informacion de medico {$nombre}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Medico Guardado!', 'texto' => "La informacion del medico fue editada exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados/listmedicos");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al editar la informacio, inténtelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados/listmedicos");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettblListMedicosAction() {
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $medicos_model = new Medicos($this->dbAdapter);
        $medicos = $medicos_model->getListMedicos();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        foreach ($medicos as $f => $c) {

            if ($c['Estado'] == '1') {
                $medicos[$f]['Estado'] = "Activo";
            } else if ($c['Estado'] == '0') {
                $medicos[$f]['Estado'] = "Inactivo";
            }
        }


        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('dni', 'N° de Expediente', 'string', NULL, false);
        $grid->addColumn('nombre_completo', 'Nombre', 'varchar', NULL, false);
        $grid->addColumn('nombre_e', 'Especialidad', 'varchar', NULL, false);
        $grid->addColumn('Estado', 'Estado', 'varchar', NULL, false);
        //        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
        $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_medico');

        $grid->renderXML($medicos);
        exit;
    }

    public function pdffichaafiliadoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            
        } else {

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $afiliados_model = new Afiliados($this->dbAdapter);
            $personas_model = new Personas($this->dbAdapter);

            $id_persona = $this->params()->fromRoute("id", null);

            //RECUPERAR LA INFORMACION DEL REPORTE
            $info_afiliado = $afiliados_model->getInfoAfiliado($id_persona);
            $beneficiarios = $personas_model->getCantBeneficiarios($id_persona);

//            echo "<pre>";
//            print_r($info_afiliado);
//            exit;
//            
            if (!$info_afiliado) {
                $_SESSION['mnsAutoOK'] = array('titulo' => 'Beneficiario Guardado!', 'texto' => "Afiliado no encontrado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/afiliados");
            }
            $identidad_afiliado = $info_afiliado['dni'];
            $nombre_completo = $info_afiliado['Nombre_Completo'];
            $fecha_nacimiento = $info_afiliado['fecha_nacimiento'];
            $tipo_sangre = $info_afiliado['sangre'];
            $estado_civil = $info_afiliado['sta_civlil'];
            $lugar_nacimiento = $info_afiliado['dep_origen'];
            $telefono = $info_afiliado['telefono'];
            $celular = $info_afiliado['celular'];
            $direccion_actual = $info_afiliado['domicilio_actual'];
            $dependencia = $info_afiliado['dependencia'];
            $departamento_asignacion = $info_afiliado['asiganacion'];
            $categoria = $info_afiliado['categoria'];
            $rango = $info_afiliado['rango'];
            $contacto_emergencia = $info_afiliado['Nom_contacto_emergencia'];
            $telefono_emergencia = $info_afiliado['telefono_contacto_emergencia'];

//                    ['nombres' => $info_beneficiario['nombre_completo'], 'parentesco' => $info_beneficiario['parentesco']];
//            echo "<pre>";
//            print_r($beneficiario);
//            exit;
            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('P', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Ficha de afiliado');
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

            $tituloHTML = "<br><div style=\"text-align: center; font-family:Helvetica ; font-size: 16px; line-height: 14px;\">"
                    . "<b>FICHA DE AFILIADO</b>"
                    . "</div>";

            $contenidoHTML = "<div style=\"text-align: justify; font-family:Helvetica; font-size: 16px; line-height: 18px;\">"
                    . "<hr>"
                    . "<div style=\"text-align: center; font-family:Helvetica; font-size: 14px; line-height: 6px;\">"
                    . "<b>Información General</b>"
                    . "</div>"
                    . "<hr>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Nombre Completo:<b> $nombre_completo </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Identidad:<b> $identidad_afiliado </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Fecha Nacimiento: <b> $fecha_nacimiento </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Tipo Sangre:<b> $tipo_sangre </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Estado Civil:<b> $estado_civil </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Lugar de Nacimiento: <b> $lugar_nacimiento </b>"
                    . "</div>"
                    . "<hr>"
                    . "<div style=\"text-align:center; font-family:Helvetica; font-size: 14px; line-height: 6px;\">"
                    . "<b>Información de Contacto </b>"
                    . "</div>"
                    . "<hr>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Teléfono:<b> $telefono </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Celular:<b> $celular </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Residencia Actual:<b> $direccion_actual </b>"
                    . "</div>"
                    . "<hr>"
                    . "<div style=\"text-align: center; font-family:Helvetica; font-size: 14px; line-height: 6px;\">"
                    . "<b>Información de Profesional </b>"
                    . "</div>"
                    . "<hr>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Dependencia: <b>$dependencia</b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Lugar de Asignación: <b> $departamento_asignacion </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Categoria:<b> $categoria </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Rango:<b> $rango </b>"
                    . "</div>"
                    . "<hr>"
                    . "<div style=\"text-align: center; font-family:Helvetica; font-size: 14px; line-height: 6px;\">"
                    . "<b>Contacto de Emergencia </b>"
                    . "</div>"
                    . "<hr>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Contacto de Emergencia:<b> $contacto_emergencia </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                    . "Telefono:<b> $telefono_emergencia </b>"
                    . "</div>"
                    . "<hr>"
                    . "<div style=\"text-align: center; font-family:Helvetica; font-size: 14px; line-height: 6px;\">"
                    . "<b>Información de Beneficiarios </b>"
                    . "</div>"
                    . "<hr>";
            if ($beneficiarios) {
                foreach ($beneficiarios as $ben) {
                    $contenidoHTML .= "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                            . "Nombre Completo:<b> {$ben['nombre_completo']}  </b>"
                            . "</div>"
                            . "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px; margin-top: 20px;\">"
                            . "Parentesco:<b> {$ben['parentesco']} </b>"
                            . "</div><br>";
                }
            } else {
                $contenidoHTML .= "<div style=\"text-align: justify; font-family:Helvetica; font-size: 14px; line-height: 12px;\">"
                        . "NO TIENE NINGÚN BENEFICIARIO"
                        . "</div>";
            }

            $contenidoHTML .= "</div>";
            $firmasHTML = "<div style=\"text-align: center; font-family: helvetica; font-size: 14px; line-height: 10px;  margin-top: 100px;\"><p>"
//                    . "__________________________________________<br>"
//                    . "$tituloFirmante $nombreFirmante<br>"
//                    . "$cargoFirmante<br>"
                    . "</p></div>";

            // output the HTML content
            $pdf->writeHTML($tituloHTML, true, false, true, false, '');
            $pdf->writeHTML($contenidoHTML, true, false, true, false, '');

            $pdf->Ln();
            $pdf->Cell($m, 0, '', '');
            $pdf->Ln();

            $pdf->writeHTML($firmasHTML, true, false, true, false, '');

            \ob_clean();
            // Cerrar enviar documento PDF , la  I (i)= lo muestra en pantalla D = lo descarga a la maquina del cliente
            $pdf->Output("FICHA AFILIADO " . '.pdf', 'I');

            //                     GUARDAR A BITACORA

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $id_objetos = $objetos_model->getIdobjetoAfiliados();
            $DateAndTime = date('Y-m-d H:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Descargar PDF',
                'descripcion' => "Se descargao la ficha del afiliado {$nombre_completo} con # de expediente {$identidad_afiliado}"];
            $utl_bitacora->guardarBitacora($bitacora);
            exit;
        }
    }

    public function getnombreafiliadoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $afiliados_model = new Afiliados($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $dni = $datosFormularios['id'];

            $patron_1 = "/^[0-9]{4}[-| ]?[0-9]{4}[-| ]?[0-9]{5}$/";

            if (!\preg_match($patron_1, $dni)) {
                $r['estado'] = "error";
                $r['mensaje'] = "La identidad N° $dni no corresponde a un número correcto, se esperaba 0801-1950-12345, 0801 1950 12345, 0801195012345.";
                echo \json_encode($r);
                exit;
            }

            $dni_sin_guiones = \str_replace(["-", " "], "", $dni);

            $info_afiliado = $afiliados_model->getNombreAfiliado($dni_sin_guiones);

            $r['estado'] = 'ok';
            if (!$info_afiliado) {
                $r['estado'] = 'error';
                $r['mensaje'] = "No existe ningún afiliado con esa identidad o esta inactivo.";
                echo \json_encode($r);
                exit;
            }

            $r['mensaje'] = "";
            $r['d']['nombres'] = $info_afiliado['Nombre_Completo'];
            $r['d']['id_afiliado'] = $info_afiliado['id'];
            $r['d']['nombre_ta'] = $info_afiliado['nombre_ta'];
            $r['d']['nombres'] = $info_afiliado['Nombre_Completo'];
            $r['d']['parentesco'] = $info_afiliado['parentesco'];

            echo \json_encode($r);
            exit;
        } else if ($this->getRequest()->isPost()) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function reporteAction() {
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

    public function editarAction() {
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

    public function exportarpdfafiliadosAction() {
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
            //AQUI EL PDF
            $afiliados_model = new Afiliados($this->dbAdapter);
            $afiliados = $afiliados_model->getPdfAfiliado();
            $beneficiarios = $afiliados_model->getTodosBeneficiarios();

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Afiliados');
            $pdf->SetSubject('Reporte De Afiliados');
            $pdf->SetKeywords('Reporte De Afiliados');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Afiliados";
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
            $header = ['N°', 'Nombre Completo', 'DNI', 'Sexo', 'Categoria', 'Rango', 'Afiliado'];
            $w = array($m * .03, $m * .25, $m * .14, $m * .07, $m * .16, $m * .26, $m * .09);

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

            foreach ($afiliados as $fila) {

                //Corregir textos


                $nom_afiliado = $utilidad_texto->fixTexto($fila['Nombre_Completo']);
                $num_identidad = $utilidad_texto->fixTexto($fila['dni']);
                $sexo = $utilidad_texto->fixTexto($fila['genero']);
                $categoria = $utilidad_texto->fixTexto($fila['categoria']);
                $rango = $utilidad_texto->fixTexto($fila['rango']);
                $tip_afiliado = $utilidad_texto->fixTexto($fila['tipo_afi']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nom_afiliado, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[2], 4.2, $num_identidad, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[3], 4.2, $sexo, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[4], 4.2, $categoria, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[5], 4.2, $rango, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[6], 4.2, $tip_afiliado, 'LR', 0, 'L', $fill);

//                 ['N°', 'Dependecnia', 'Descripción'];

                $pdf->Ln();
                $fill = !$fill;
                $cont++;
            }
//            BENEFICIARIOS
            foreach ($beneficiarios as $fila) {

                //Corregir textos


                $nom_beneficiario = $utilidad_texto->fixTexto($fila['nombre_completo']);
                $num_identidad = $utilidad_texto->fixTexto($fila['dni']);
                $sexo = $utilidad_texto->fixTexto($fila['genero']);
                $categoria = $utilidad_texto->fixTexto($fila['categoria']);
                $rango = $utilidad_texto->fixTexto($fila['rango']);
                $tip_afiliado = $utilidad_texto->fixTexto($fila['nombre_ta']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nom_beneficiario, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[2], 4.2, $num_identidad, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[3], 4.2, $sexo, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[4], 4.2, '', 'LR', 0, 'L', $fill);
                $pdf->Cell($w[5], 4.2, '', 'LR', 0, 'L', $fill);
                $pdf->Cell($w[6], 4.2, $tip_afiliado, 'LR', 0, 'L', $fill);

//                 ['N°', 'Dependecnia', 'Descripción'];

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

            //                     GUARDAR A BITACORA

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $id_objetos = $objetos_model->getIdobjetoAfiliados();
            $DateAndTime = date('Y-m-d H:i:s');
            $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Descarga PDF',
                'descripcion' => "Se descargo el pdf con la lista de todos los afiliados"];

            $utl_bitacora->guardarBitacora($bitacora);

            exit;

            //FIN PDF  
        }
    }

}
