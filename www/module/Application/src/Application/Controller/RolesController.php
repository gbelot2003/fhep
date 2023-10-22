<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Roles;
use Application\Model\Objetos;
use Application\Model\Bitacoras;
use Application\Model\Usuarios;
use Application\Model\RolesxObjetos;
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Texto;
use Application\Extras\Utilidades\Bitacora;

class RolesController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function rolesAction() {
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
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $roles_model = new Roles($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

            unset($datosFormularios['chk3']);

            if ($roles_model->agregarNuevo($datosFormularios)) {


                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoSeguridad();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de un rol',
                    'descripcion' => " Se creo el rol con nombre {$datosFormularios['nombre_rol']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Rol guardado!', 'texto' => "El Rol fue creado exitosamente.");

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/roles");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el rol, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/roles");
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
        $roles_model = new Roles($this->dbAdapter);
        $id = $this->params()->fromRoute("id", null);
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {



            $datosFormularios = $this->request->getPost()->toArray();

            if ($roles_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoSeguridad();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Editar Rol',
                    'descripcion' => " Se edito el rol con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Rol Actualizado!', 'texto' => "La informacion se cambio exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/roles");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar la informacion.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/roles");
            }
        } else {
            //Normal GET

            $info_rol = $roles_model->getInfoRol($id);

            $vista = new ViewModel(array('info' => $info_rol)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $roles_model = new Roles($this->dbAdapter);
        $rolesxobjetos_model = new RolesxObjetos($this->dbAdapter);
        $usuarios_model = new Usuarios($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            //$datosFormularios = $this->request->getPost()->toArray();
            exit;
        } else if ($this->getRequest()->isPost()) {



            $id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $id = $this->params()->fromRoute("id", null);

            $validar_rol_usuario = $usuarios_model->getExisteRol($id);

            if ($validar_rol_usuario) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'no se puede borrar el rol por que uno o mas usuarios lo estan utilizándolo'
                    . ' desactive a los usuarios e intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/roles");
            } else {

                $rolesxobjetos_model->borrar($id);
                $roles_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoSeguridad();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Rol',
                    'descripcion' => "Se elimino el rol con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Rol Eliminado!', 'texto' => "El Rol fue eliminado.");

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/roles");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablarolesAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $roles_model = new Roles($this->dbAdapter);

        $roles = $roles_model->getblRoles();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        $grid = new \EditableGrid();
        if (!$roles) {
            $grid->addColumn('nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('action', '', 'html', NULL, false, 'id');
        } else {
            // create a new EditableGrid object
            $grid->addColumn('nombre_rol', 'Nombre', 'string', NULL, false);
            $grid->addColumn('descripcion', 'Descripción', 'string', NULL, false);
            $grid->addColumn('tipo', 'Tipo', 'string', NULL, false);
            $grid->addColumn('estado', 'Estado', 'string', NULL, false);
            $grid->addColumn('action', 'Opciones', 'html', NULL, false, 'id');
        }
        $grid->renderXML($roles);
        exit;
    }

    public function exportarrolesAction() {
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

            $roles_model = new Roles($this->dbAdapter);

            $roles = $roles_model->getblRoles();

            //PDF ROLES
//                  echo "<pre>";
//            print_r($roles);
//            exit;
//            
//            CATEGORIAS PDF

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Roles');
            $pdf->SetSubject('Roles');
            $pdf->SetKeywords('Roles');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Roles";
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
            $header = ['N°', 'Nombre', 'Descripción', 'Estado'];
            $w = array($m * .02, $m * .23, $m * .40, $m * .35);

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

            foreach ($roles as $fila) {

                //Corregir textos
//'Nombre','Descripción','Estado'
                $nombre = $utilidad_texto->fixTexto($fila['nombre_rol']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);
                $estado = $utilidad_texto->fixTexto($fila['estado']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nombre, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[2], 4.2, $descripcion, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[3], 4.2, $estado, 'LR', 0, 'L', $fill);

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

}
