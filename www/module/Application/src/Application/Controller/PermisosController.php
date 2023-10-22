<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Extras\Auditoria\GuardarEnAuditoria;
//Usar lo modelos requeridos
use Application\Model\Empleados;
use Application\Model\Usuarios;
use Application\Model\Permisos;
use Application\Model\Objetos;
use Application\Model\Roles;
use Application\Model\Bitacoras;
use Application\Model\RolesxObjetos;
use Zend\Db\Metadata\Metadata;
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Texto;
use Application\Extras\Utilidades\Bitacora;

class PermisosController extends AbstractActionController {

//    public function __construct() {
//        //session_start();
//        $_SESSION['permisos'] = 'active';
//    }

    public function permisosAction() {

//       $objeto = 'Seguridad';
//        $permiso = 'permiso_consultar';
//        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");

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

    public function gttblpermisosAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $roles_objetos_model = new RolesxObjetos($this->dbAdapter);
        $roles_objetos = $roles_objetos_model->getRolesxobjetos();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_insercion'] == '1') {
                $roles_objetos[$f]['permiso_insercion'] = "Si";
            } else if ($c['permiso_insercion'] == '0') {
                $roles_objetos[$f]['permiso_insercion'] = "No";
            }
        }
        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_actualizacion'] == '1') {
                $roles_objetos[$f]['permiso_actualizacion'] = "Si";
            } else if ($c['permiso_actualizacion'] == '0') {
                $roles_objetos[$f]['permiso_actualizacion'] = "No";
            }
        }
        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_actualizacion'] == '1') {
                $roles_objetos[$f]['permiso_actualizacion'] = "Si";
            } else if ($c['permiso_actualizacion'] == '0') {
                $roles_objetos[$f]['permiso_actualizacion'] = "No";
            }
        }
        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_consultar'] == '1') {
                $roles_objetos[$f]['permiso_consultar'] = "Si";
            } else if ($c['permiso_consultar'] == '0') {
                $roles_objetos[$f]['permiso_consultar'] = "No";
            }
        }
        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_consultar'] == '1') {
                $roles_objetos[$f]['permiso_consultar'] = "Si";
            } else if ($c['permiso_consultar'] == '0') {
                $roles_objetos[$f]['permiso_consultar'] = "No";
            }
        }
        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_eliminacion'] == '1') {
                $roles_objetos[$f]['permiso_eliminacion'] = "Si";
            } else if ($c['permiso_eliminacion'] == '0') {
                $roles_objetos[$f]['permiso_eliminacion'] = "No";
            }
        }

        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_reportes'] == '1') {
                $roles_objetos[$f]['permiso_reportes'] = "Si";
            } else if ($c['permiso_reportes'] == '0') {
                $roles_objetos[$f]['permiso_reportes'] = "No";
            }
        }

        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_expedientes'] == '1') {
                $roles_objetos[$f]['permiso_expedientes'] = "Si";
            } else if ($c['permiso_expedientes'] == '0') {
                $roles_objetos[$f]['permiso_expedientes'] = "No";
            }
        }

        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_triaje'] == '1') {
                $roles_objetos[$f]['permiso_triaje'] = "Si";
            } else if ($c['permiso_triaje'] == '0') {
                $roles_objetos[$f]['permiso_triaje'] = "No";
            }
        }

        foreach ($roles_objetos as $f => $c) {

            if ($c['permiso_gestion_medicos'] == '1') {
                $roles_objetos[$f]['permiso_gestion_medicos'] = "Si";
            } else if ($c['permiso_gestion_medicos'] == '0') {
                $roles_objetos[$f]['permiso_gestion_medicos'] = "No";
            }
        }




        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('nombre_rol', 'Rol', 'string', NULL, false);
        $grid->addColumn('objeto', 'Objeto', 'varchar', NULL, false); //ocupo nombre completo
        $grid->addColumn('permiso_insercion', 'Insertar', 'string', NULL, false);
        $grid->addColumn('permiso_actualizacion', 'Actualizar', 'varchar', NULL, false);
        $grid->addColumn('permiso_consultar', 'Consultar', 'varchar', NULL, false);
        $grid->addColumn('permiso_eliminacion', 'Eliminación', 'varchar', NULL, false);
        $grid->addColumn('permiso_reportes', 'Reportes', 'varchar', NULL, false);
        $grid->addColumn('permiso_expedientes', 'Expedientes', 'varchar', NULL, false);
        $grid->addColumn('permiso_triaje', 'Triaje', 'varchar', NULL, false);
        $grid->addColumn('permiso_gestion_medicos', 'Gestion Medico', 'varchar', NULL, false);
//        $grid->addColumn('fecha_creacion', 'Fecha de creación', 'varchar', NULL, false);
//        $grid->addColumn('creado_por', 'Creado por', 'varchar', NULL, false);
        //        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
        $grid->addColumn('action', 'Opciones', 'html', NULL, false, 'id_objeto');

        $grid->renderXML($roles_objetos);
        exit;
    }

    public function exportarpdfpermisosAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $roles_objetos_model = new RolesxObjetos($this->dbAdapter);
            $roles_objetos = $roles_objetos_model->getRolesxobjetos();

            // Incluir el MAIN de la libreria
            require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_insercion'] == '1') {
                    $roles_objetos[$f]['permiso_insercion'] = "Si";
                } else if ($c['permiso_insercion'] == '0') {
                    $roles_objetos[$f]['permiso_insercion'] = "No";
                }
            }
            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_actualizacion'] == '1') {
                    $roles_objetos[$f]['permiso_actualizacion'] = "Si";
                } else if ($c['permiso_actualizacion'] == '0') {
                    $roles_objetos[$f]['permiso_actualizacion'] = "No";
                }
            }
            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_actualizacion'] == '1') {
                    $roles_objetos[$f]['permiso_actualizacion'] = "Si";
                } else if ($c['permiso_actualizacion'] == '0') {
                    $roles_objetos[$f]['permiso_actualizacion'] = "No";
                }
            }
            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_consultar'] == '1') {
                    $roles_objetos[$f]['permiso_consultar'] = "Si";
                } else if ($c['permiso_consultar'] == '0') {
                    $roles_objetos[$f]['permiso_consultar'] = "No";
                }
            }
            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_consultar'] == '1') {
                    $roles_objetos[$f]['permiso_consultar'] = "Si";
                } else if ($c['permiso_consultar'] == '0') {
                    $roles_objetos[$f]['permiso_consultar'] = "No";
                }
            }
            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_eliminacion'] == '1') {
                    $roles_objetos[$f]['permiso_eliminacion'] = "Si";
                } else if ($c['permiso_eliminacion'] == '0') {
                    $roles_objetos[$f]['permiso_eliminacion'] = "No";
                }
            }

            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_reportes'] == '1') {
                    $roles_objetos[$f]['permiso_reportes'] = "Si";
                } else if ($c['permiso_reportes'] == '0') {
                    $roles_objetos[$f]['permiso_reportes'] = "No";
                }
            }

            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_expedientes'] == '1') {
                    $roles_objetos[$f]['permiso_expedientes'] = "Si";
                } else if ($c['permiso_expedientes'] == '0') {
                    $roles_objetos[$f]['permiso_expedientes'] = "No";
                }
            }

            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_triaje'] == '1') {
                    $roles_objetos[$f]['permiso_triaje'] = "Si";
                } else if ($c['permiso_triaje'] == '0') {
                    $roles_objetos[$f]['permiso_triaje'] = "No";
                }
            }

            foreach ($roles_objetos as $f => $c) {

                if ($c['permiso_gestion_medicos'] == '1') {
                    $roles_objetos[$f]['permiso_gestion_medicos'] = "Si";
                } else if ($c['permiso_gestion_medicos'] == '0') {
                    $roles_objetos[$f]['permiso_gestion_medicos'] = "No";
                }
            }

//            PDF PERMISOS
//            echo "<pre>";
//            print_r($roles_objetos);
//            exit;
//            PERMISOS PDF

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Permisos');
            $pdf->SetSubject('Permisos');
            $pdf->SetKeywords('Permisos');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Permisos";
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
            $header = ['N°', 'Nombre', 'Objeto', 'Insertar', 'Editar', 'Eliminar', 'Consultar', 'Reportes', 'Expedientes', 'Triaje', 'Gestion M'];
            $w = array($m * .04, $m * .16, $m * .16, $m * .08, $m * .07, $m * .08, $m * .08, $m * .08, $m * .10, $m * .07, $m * .08);

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

            foreach ($roles_objetos as $fila) {

                //Corregir textos
//'Nombre','Descripción','Estado'
                $nombre = $utilidad_texto->fixTexto($fila['nombre_rol']);
                $objeto = $utilidad_texto->fixTexto($fila['objeto']);
                $insertar = $utilidad_texto->fixTexto($fila['permiso_insercion']);
                $editar = $utilidad_texto->fixTexto($fila['permiso_actualizacion']);
                $eliminar = $utilidad_texto->fixTexto($fila['permiso_eliminacion']);
                $consultar = $utilidad_texto->fixTexto($fila['permiso_consultar']);
                $permiso_reportes = $utilidad_texto->fixTexto($fila['permiso_reportes']);
                $permiso_expedientes = $utilidad_texto->fixTexto($fila['permiso_expedientes']);
                $permiso_triaje = $utilidad_texto->fixTexto($fila['permiso_triaje']);
                $permiso_gestion_medicos = $utilidad_texto->fixTexto($fila['permiso_gestion_medicos']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nombre, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[2], 4.2, $objeto, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[3], 4.2, $insertar, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[4], 4.2, $editar, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[5], 4.2, $eliminar, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[6], 4.2, $consultar, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[7], 4.2, $permiso_reportes, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[8], 4.2, $permiso_expedientes, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[9], 4.2, $permiso_triaje, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[10], 4.2, $permiso_gestion_medicos, 'LR', 0, 'L', $fill);

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
            $pdf->Output("ROLES" . '.pdf', 'I');
            exit;
        }
    }

    public function asinaciondepermisosAction() {

        if (!isset($_SESSION['auth'])) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }


        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $roles_model = new Roles($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);
        $rolesxobjetos_model = new RolesxObjetos($this->dbAdapter);
        $permisosModel = new Permisos($this->dbAdapter);
        $idc = $this->params()->fromRoute("id", null);
        $utl_bitacora = new Bitacora($this->dbAdapter);

        if ($this->getRequest()->isPost()) {
            //Recuperar los datos de los campos del formulario
            $datosFormularios = $this->request->getPost()->toArray();

            //agregar el estado de la zona como activo
            $datosFormularios['estado'] = '1';
            //Adaptador para la base de datos

            $id_objeto = ['id_objeto' => $datosFormularios['id_objeto']];
            $id_rol = ['id_rol' => $idc];
            $nombre_rol = ['nombre_rol' => $datosFormularios['nombre_rol']];
            $info_estado = $roles_model->tipoDeinser($id_rol['id_rol'], $id_objeto['id_objeto']);

//            $roleobjeto = ['id_rol' => $id_rol['id_rol'], 'id_objeto' => $datosFormularios['id_objeto'],
//                'permiso_consultar' => $datosFormularios['permiso_consultar'], 'permiso_actualizacion' => $datosFormularios['permiso_actualizacion'],
//                'permiso_insercion' => $datosFormularios['permiso_insercion'], 'permiso_eliminacion' => $datosFormularios['permiso_eliminacion'],
//                'estado' => $datosFormularios['estado']];





            unset($datosFormularios['nombre_rol']);

            if (!isset($datosFormularios['permiso_insercion'])) {
                $datosFormularios['permiso_insercion'] = 0;
            }


            if (!isset($datosFormularios['permiso_consultar'])) {
                $datosFormularios['permiso_consultar'] = 0;
            }

            if (!isset($datosFormularios['permiso_actualizacion'])) {
                $datosFormularios['permiso_actualizacion'] = 0;
            }

            if (!isset($datosFormularios['permiso_eliminacion'])) {
                $datosFormularios['permiso_eliminacion'] = 0;
            }

            if (!isset($datosFormularios['permiso_reportes'])) {
                $datosFormularios['permiso_reportes'] = 0;
            }

            if (!isset($datosFormularios['permiso_expedientes'])) {
                $datosFormularios['permiso_expedientes'] = 0;
            }

            if (!isset($datosFormularios['permiso_triaje'])) {
                $datosFormularios['permiso_triaje'] = 0;
            }

            if (!isset($datosFormularios['permiso_gestion_medicos'])) {
                $datosFormularios['permiso_gestion_medicos'] = 0;
            }

            if (!$info_estado) {


                unset($datosFormularios['fecha_modificacion']);

                $rolesxobjetos_model->agregarNuevo($datosFormularios);

                //GUARDAR A BITACORA
                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdObjtoUsuarios();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de un nuevo Set de persisos',
                    'descripcion' => "Se creo un set de permisos para el rol {$nombre_rol['nombre_rol']} con el objeto con ID {$id_objeto['id_objeto']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Permisos Guardados!', 'texto' => "El set de permisos fue guardaos exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos/asinaciondepermisos/$idc");
            } else {

                $rolesxobjetos_model->actualizar($idc, $id_objeto, $datosFormularios);

                //GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdObjtoUsuarios();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de un nuevo Set de persisos',
                    'descripcion' => "Se actualizo un set de permisos para el rol {$nombre_rol['nombre_rol']} con el objeto con ID {$id_objeto['id_objeto']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoInfo'] = array('titulo' => 'Permisos Actualizado!', 'texto' => "El set de permisos se  actualizo exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos/asinaciondepermisos/$idc");
            }

//            if ($permisosModel->agregarNuevo($datosFormularios)) {
//                //Mensaje de confirmación
//                $_SESSION['mnsAutoOK'] = array('titulo' => 'Set de Permisos Creado!', 'texto' => 'Set de permisos credo exitosamente.');
//                //Redireccionar
//                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos");
//            } else {
//                $_SESSION['mnsError'] = array('titulo' => 'Error al Guardar!', 'texto' => 'Set de permisos no pudo ser guardado.');
//            }
        } else {

            //GET NORMAL

            $info_rol = $roles_model->getRolExiste($idc);

            if (!$info_rol) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'El Rol no se encuentra o esta inactivo, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos");
            }

            $objetos = $objetos_model->getObjetos();

            $roles = $roles_model->getRoles();

            $vista = new ViewModel(array('obje' => $objetos, 'role' => $roles, 'inforol' => $info_rol)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function borrarsetAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $rolesxobjetos_model = new RolesxObjetos($this->dbAdapter);
        $id = $this->params()->fromRoute("id", null);
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $id = $this->params()->fromRoute("id", null);

            if ($rolesxobjetos_model->borrarObjeto($id)) {


                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdObjtoUsuarios();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Set de persimos',
                    'descripcion' => "Se elimino el set de permisos en el objeto con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Persimisos Eliminados!', 'texto' => "Los permisos fueron eliminados correctamente.");

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al elimibar el set de permisos, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function asignarpAction() {
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
        //Validar si se esta autenticado, sino redireccionar al index público
        if (!isset($_SESSION['auth']) || $_SESSION['rol']['adm_permisos'] != '1') {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        //Recuperar parametro de la url
        $idRol = $this->params()->fromRoute("idpermiso", null);
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        //Recuperar el modelo
        $permisosModel = new Permisos($this->dbAdapter);

        $datRol = $permisosModel->getPermisoPorId($idRol);
        if (!$datRol) {
            $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Set de Permisos no pudo ser encontrado.');

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos");
        }




        if ($this->getRequest()->isPost()) {
            //Recuperar los datos de los campos del formulario
            $datosFormularios = $this->request->getPost()->toArray();

            if ($datosFormularios['confirmacion'] != 1) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error de confirmación!', 'texto' => 'Set de Permisos no pudo ser actualizado.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos");
            }

            unset($datosFormularios['confirmacion']);

            //agregar el permisos no seleccionados
            $datosUpdate = $this->verificarParametros($datosFormularios);

            //Adaptador para la base de datos
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $permisosModel = new Permisos($this->dbAdapter);

            //instancia de clase para guardar en auditoría
            $auditoria = new GuardarEnAuditoria($this->getServiceLocator()->get('Zend\Db\Adapter'));

            $datViejos = $permisosModel->getPermisoPorId($idRol);
            $valViejos = $auditoria->getValoresPerimisos($datViejos);

            if ($permisosModel->actualizarPermiso($idRol, $datosUpdate) > 0) {


                $datNuevos = $permisosModel->getPermisoPorId($idRol);
                $valNuevos = $auditoria->getValoresPerimisos($datNuevos);

                $campos = $auditoria->getCampos($datNuevos);

                //Guardar en auditoria
                $nombres = $datosFormularios['nombre_rol'];
                $datosAuditoria = array();
                $datosAuditoria['tabla_afectada'] = 'roles';
                $datosAuditoria['campos_tabla'] = $campos;
                $datosAuditoria['valores_anteriores'] = $valViejos;
                $datosAuditoria['valores_nuevos'] = $valNuevos;
                $datosAuditoria['descripcion'] = "Modificó el set de permisos: $nombres";

                $auditoria->enviarAuditoria($datosAuditoria);

                //Mensaje de confirmación
                $_SESSION['mnsAutoOK'] = array('titulo' => 'Set de Permisos Actualizado!', 'texto' => 'Set de permisos actualizado exitosamente.');
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error al Guardar!', 'texto' => 'Set de permisos no pudo ser actualizado.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/permisos");
            }
        } else {

            $datRolPantalla = $this->datosPermisoEditar($datRol);

            $vista = new ViewModel(array('datosRol' => $datRolPantalla)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function validarpermisousuarioAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $roles_obetos_model = new RolesxObjetos($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id_rol = $datosFormularios['id_rol'];
            $id_objeto = $datosFormularios['id_objeto'];

            $info = $roles_obetos_model->existeRolObjeto($id_rol, $id_objeto);

            if (!$info) {
                $r['estado'] = "nuevo";

                echo \json_encode($r);
                exit;
            }

            $r['estado'] = "mod";
            $r['p'] = $info;

            echo \json_encode($r);
            exit;
        } else if ($this->getRequest()->isPost()) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    private function verificarParametros($datosPost = array()) {

        $metadata = new Metadata($this->dbAdapter);
        $table = $metadata->getTable("roles");
        $colsFull = $table->getColumns();
        $cols = [];
        foreach ($colsFull as $colums => $col) {
            $cols[$colums] = $col->getName();
        }

        //Validar la existencia, sino agregarlo
        for ($i = 4; $i < count($cols); $i++) {
            $colName = $cols[$i];
            if (!isset($datosPost["$colName"])) {
                $datosPost["$colName"] = '0';
            }
        }

        return $datosPost;
    }

    public function getdatoempleadoAction() {
        //Validar si se esta autenticado, sino redireccionar al index público
        if (!isset($_SESSION['auth']) || $_SESSION['rol']['adm_permisos'] != '1') {
            return; // $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isPost()) {
            $datosFormularios = $this->request->getPost();

            $identidad = $datosFormularios['buscar'];

            //Adaptador para la base de datos
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $empleadosModel = new Empleados($this->dbAdapter);
            $usuariosModel = new Usuarios($this->dbAdapter);

            $datEmpleado = $empleadosModel->getPersonaPorIdentidad($identidad);
            $yaExiste = $usuariosModel->getUsuarioPorIdentidad($identidad);

            if ($yaExiste) {
                echo "<b class=\"text-danger\">Usuario ya Existe!! por favor verifique</b>";
                exit;
            }

            if ($datEmpleado) {
                echo "<b class=\"text-success\">" . $datEmpleado['nombres'] . " " . $datEmpleado['apellidos'] . "</b>";
                exit;
            } else {

                $resp = "<b class=\"text-danger\">Empleado no Existe!!";

                // if ($_SESSION['rol']['adm_personas'] == '1') {
                //   $resp =$resp."&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"" . $this->getRequest()->getBaseUrl() . "/personas/nuevo\" class=\"btn btn-primary btn-xs\"><span class=\"icon icon-std icon-user-add\"></span> </a>";
                // }else{
                $resp = $resp . " <h5>Solicite que lo agregen</h5>";
                // }

                $resp = $resp . "</b>";
                echo $resp;
                exit;
            }
        } else {

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios");
        }
    }

    public function validarusuarioAction() {
        //Validar si se esta autenticado, sino redireccionar al index público
        if (!isset($_SESSION['auth']) || $_SESSION['rol']['adm_permisos'] != '1') {
            echo 'Debe autenticarse nuevamente, su sesión ha vencido';
            exit;
            // $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }


        if ($this->getRequest()->isPost()) {
            $datosFormularios = $this->request->getPost();

            $usuario = $datosFormularios['buscar'];

            //Adaptador para la base de datos
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $usuariosModel = new Usuarios($this->dbAdapter);

            $yaExiste = $usuariosModel->getUsuarioPorUsuario($usuario);

            if ($yaExiste) {
                echo "<b class=\"text-danger\">Nombre de Usuario Ya Existe!! </b>";
                exit;
            } else {
                echo "<b class=\"text-success\">Nombre de Usuario Disponible!! </b>";
                exit;
            }
        } else {

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios");
        }
    }

    private function datosPermisoEditar($datosPost = array()) {
        foreach ($datosPost as $key => $value) {
            if ($key != 'estado' && $key != 'id_rol' && $key != 'nombre_rol') {
                $resp = $this->cambioChecked($value);
                $datosPost[$key] = $resp;
            }
        }
        return $datosPost;
    }

    private function cambioChecked($valor) {
        if ($valor == '1') {
            return 'checked="checked"';
        } else if ($valor == '0') {
            return '';
        } else {
            return $valor;
        }
    }

    public function gettablapermisosAction() {
        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
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

}
