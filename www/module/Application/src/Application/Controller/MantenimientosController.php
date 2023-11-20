<?php

namespace Application\Controller;

use Application\Model\Rangos;
use Application\Model\Objetos;

/* Aquí los Modelos */
use Zend\View\Model\ViewModel;
use Application\Model\Estadosu;
use Application\Model\Tipodato;
use Application\Model\Tiposoli;
use Application\Model\Usuarios;
use Application\Model\Bitacoras;
use Application\Model\Esciviles;
use Application\Model\Categorias;
use Application\Model\FirmaSello;
use Application\Model\Sucursales;
use Application\Model\Tipoexamen;
use Application\Model\Estadosoli; 
use Application\Model\Preclinicas;
use Application\Model\Asignaciones;
use Application\Model\Dependencias;
use Application\Model\Categoriavalor;
use Application\Model\Especialidades;
use Zend\Db\TableGateway\TableGateway;

//Para el Excel
use Application\Extras\Utilidades\Texto;
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Bitacora;
use Zend\Mvc\Controller\AbstractActionController;

/* use Application\Model\Mimodelo; */

class MantenimientosController extends AbstractActionController {

    protected $tableGateway;


    public function __construct() {
       // $this->model =  new FirmaSello($this->dbAdapter);
        //$this->tableGateway = $tableGateway;

        /* $_SESSION['unidades'] = 'active'; */
        
    }

    public function mantenimientosAction() {

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
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
    
    public function tiposoliAction() {

        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tiposoli_model = new Tiposoli($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($tiposoli_model->agregarNuevo($datosFormularios)) {

                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de una dependencia', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo el tipo de solicitud con nombre {$datosFormularios['nombre_ts']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de solicitud guardada!', 'texto' => "El tipo de solicitud fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tiposoli");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el tipo de solicitud, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tiposoli");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function firmaselloAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tiposoli_model = new Tiposoli($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);
        $model = new FirmaSello($this->dbAdapter);

        //$resultSet = $this->FirmaSello->select();

        //Normal GET
        $vista = new ViewModel(['model' => $model->getAll()]); //Instancia de la vista
        $this->layout(); //Parametro pasado al layout Titulo de la página
        return $vista;
    }

    public function firmaselloEditAction()
    {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $model = new FirmaSello($this->dbAdapter);
        $usuario = new Usuarios($this->dbAdapter);

        $id = $this->params()->fromRoute("id", null);
        $data = $model->getById($id);
        $user = $usuario->getPorId($data->ID_Usuario);

        $vista = new ViewModel(['model' => $data, 'user' => $user]); //Instancia de la vista
        $this->layout(); //Parametro pasado al layout Titulo de la página
        return $vista;
    }

    public function firmaselloCreateAction() 
    {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $usuario = new Usuarios($this->dbAdapter);
        $users = $usuario->getAll();

        $vista = new ViewModel(['users' => $users]); //Instancia de la vista
        $this->layout(); //Parametro pasado al layout Titulo de la página
        return $vista;
    }

    public function firmaselloEliminarAction()
    {
 

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $id = $this->params()->fromRoute("id", null);
        $model = new FirmaSello($dbAdapter);
        $firma = $model->getById($id);

        unlink($_SERVER["DOCUMENT_ROOT"] . "/public" . $firma->Firma);
        unlink($_SERVER["DOCUMENT_ROOT"] . "/public" . $firma->Sello);

        $model->deleteData($id);
        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/firmasello");
    }

    private function saveFirmaOrSelloToDisk($name) {
        // 1. Make sure the file exists in the request:
        if (empty($_FILES[$name])) {
            http_response_code(400);
            echo 'Archivos incompletos';
            exit;
        }

        // 2. Make sure the file has an allowed extension:
        $extensions = array('jpeg', 'jpg', 'png');
        $extension = strtolower(
            pathinfo($_FILES[$name]['name'], 
            PATHINFO_EXTENSION)
        );
        if (!in_array($extension, $extensions)) {
            http_response_code(400);
            echo "La extension de \"{$name}\" no es permitida.";
            exit;
        }

        // 3. Create directory 'uploads' in case it does not exists:
        if (!file_exists('uploads')) {
            mkdir('uploads');
        }

        // 4. Save the file uploaded into 'uploads' directory:
        // Si presenta problemas de permisos en windows
        // revisar y/o cambiar / por \

        $file['signed_name'] = rand(1, 1000000).'-'.$_FILES[$name]['name'];
        $file['file_location'] = 'public/img/'.$file['signed_name'];
        move_uploaded_file($_FILES[$name]['tmp_name'], $file['file_location']);

        return $file['signed_name'];
    }

    private function saveFirmaAndSelloToDB($firma, $sello, $idUsuario) {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $model = new FirmaSello($dbAdapter);
        
        // Si presenta problemas de permisos en windows
        // revisar y/o cambiar / por \
        $data = array(
            'ID_Usuario' => $idUsuario,
            'Firma' => "/img/{$firma}",
            'Sello' => "/img/{$sello}",
        );
        $model->save($data);
    }

    public function editarFirmaSelloAction(){
        // 1. Check request is only of AJAX type:
        if (!$this->getRequest()->isXmlHttpRequest()) {
            echo 'Only available for AJAX calls.';
            exit;
        }

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $id = $this->params()->fromRoute("id", null);
        $model = new FirmaSello($dbAdapter);

        $firma = $model->getById($id);
        $firma = $this->saveFirmaOrSelloToDisk('firma');
        $sello = $this->saveFirmaOrSelloToDisk('sello');

        // Si presenta problemas de permisos en windows
        // revisar y/o cambiar / por \
        $data = array(
            'Firma' => "/img/{$firma}",
            'Sello' => "/img/{$sello}",
        );

        $model->updateData($data, $id);

        echo 'Files Uploaded successfully';

        exit;
    }

    public function guardarfirmaselloAction() {
        // 1. Check request is only of AJAX type:
        if (!$this->getRequest()->isXmlHttpRequest()) {
            echo 'Only available for AJAX calls.';
            exit;
        }

        $idUsuario = $_POST['ID_Usuario'];

        $firma = $this->saveFirmaOrSelloToDisk('firma');
        $sello = $this->saveFirmaOrSelloToDisk('sello');
        
        // 3. Save Firma & Sello names into database:
        $this->saveFirmaAndSelloToDB($firma, $sello, $idUsuario);

      // 4. Send OK response to client:
      echo 'Files Uploaded successfully';

      exit;
    }


    public function editartiposoliAction() {
        $objeto = 'Mantenimientos';
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
            $tiposoli_model = new Tiposoli($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($tiposoli_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Tipo de solicitud',
                    'descripcion' => "Se actualizo la informacion del tipo de solicitud {$datosFormularios['nombre_ts']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de solicitud Actualizada!', 'texto' => "El tipo de solicitud fue actualizada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tiposoli");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar el tipo de solicitud, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tiposoli");
            }
        } else {
            //Normal GET


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfotiposoliAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $tiposoli_model = new Tiposoli($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_tiposoli = $tiposoli_model->getPorId($id);

            echo \json_encode($info_tiposoli);

            exit;
        } else if ($this->getRequest()->isPost()) {


            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            //Normal GET

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function nuevatiposoliAction() {
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
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el tipo de solicitud, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/tiposoli");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminartiposoliAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tiposoli_model = new Tiposoli($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET


            $id = $this->params()->fromRoute("id", null);

            $validar_tiposoli = $tiposoli_model->getExisteTiposoli($id);

            if ($validar_tiposoli) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'no se puede eliminar el tipo de solicitud por que ya hay '
                    . 'afiliados asignados.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tiposoli");
            } else {
                $tiposoli_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar tipo de solicitud',
                    'descripcion' => "Se elimino el tipo de solicitud con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de solicitud Eliminada!', 'texto' => "El tipo de solicitud fue eliminado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tiposoli");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablatiposoliAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tiposoli_model = new Tiposoli($this->dbAdapter);
        $dp = $tiposoli_model->getTblTiposoli();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object

        $grid = new \EditableGrid();
        if (!$dp) {
            $grid->addColumn('Nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('action', '', 'html', NULL, false, 'id');
        } else {


            $grid->addColumn('nombre_ts', 'Nombre', 'string', NULL, false);
//            $grid->addColumn('descripcion', 'Descripcion', 'string', NULL, false);

            $grid->addColumn('action', 'Acción', 'html', NULL, false, 'id');
        }
        $grid->renderXML($dp);
        exit;
    }
    
    public function EstadosoliAction() {

        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $estadosoli_model = new Estadosoli($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($estadosoli_model->agregarNuevo($datosFormularios)) {

                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de una dependencia', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo el tipo de solicitud con nombre {$datosFormularios['nombre_ts']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado de solicitud guardado!', 'texto' => "El estado de solicitud fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosoli");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el estado de solicitud, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosoli");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarestadosoliAction() {
        $objeto = 'Mantenimientos';
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
            $estadosoli_model = new Estadosoli($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($estadosoli_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Estado de solicitud',
                    'descripcion' => "Se actualizo el estado de solicitud {$datosFormularios['nombre_e']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado de solicitud Actualizado!', 'texto' => "El estado de solicitud fue actualizado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosoli");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar el estado de solicitud, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosoli");
            }
        } else {
            //Normal GET


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfoestadosoliAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $estadosoli_model = new Estadosoli($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_estadosoli = $estadosoli_model->getPorId($id);

            echo \json_encode($info_estadosoli);

            exit;
        } else if ($this->getRequest()->isPost()) {


            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            //Normal GET

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function nuevoestadosoliAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $estadosoli_model = new Estadosoli($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

//            echo "<pre>";
//            print_r($datosFormularios);
//            print_r($array_prueba);
//            exit;

            if ($estadosoli_model->agregarNuevo($datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado de solicitud guardado!', 'texto' => "El estado de solicitud fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el estado de solicitud, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/estadosoli");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarestadosoliAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $estadosoli_model = new Estadosoli($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET


            $id = $this->params()->fromRoute("id", null);

            $validar_estadosoli = $estadosoli_model->getExisteEstadosoli($id);

            if ($validar_estadosoli) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'no se puede eliminar el estado de solicitud por que ya hay '
                    . 'afiliados asignados.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosoli");
            } else {
                $estadosoli_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar estado de solicitud',
                    'descripcion' => "Se elimino el estado de solicitud con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado de solicitud Eliminado!', 'texto' => "El estado de solicitud fue eliminado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosoli");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablaestadosoliAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $estadosoli_model = new Estadosoli($this->dbAdapter);
        $dp = $estadosoli_model->getTblEstadosoli();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object

        $grid = new \EditableGrid();
        if (!$dp) {
            $grid->addColumn('Nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('action', '', 'html', NULL, false, 'id');
        } else {


            $grid->addColumn('nombre_e', 'Nombre', 'string', NULL, false);
//            $grid->addColumn('descripcion', 'Descripcion', 'string', NULL, false);

            $grid->addColumn('action', 'Acción', 'html', NULL, false, 'id');
        }
        $grid->renderXML($dp);
        exit;
    }
    
    public function tipodatoAction() {

        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tipodato_model = new Tipodato($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($tipodato_model->agregarNuevo($datosFormularios)) {

                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de un tipo de dato', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo el tipo de dato con nombre {$datosFormularios['nombre_td']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de dato guardado!', 'texto' => "El tipo de dato fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipodato");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el tipo de dato, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipodato");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editartipodatoAction() {
        $objeto = 'Mantenimientos';
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
            $tipodato_model = new Tipodato($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($tipodato_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Tipo de dato',
                    'descripcion' => "Se actualizo la informacion del tipo de dato {$datosFormularios['nombre_td']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de dato Actualizado!', 'texto' => "El tipo de dato fue actualizada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipodato");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar el tipo de dato, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipodato");
            }
        } else {
            //Normal GET


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfotipodatoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $tipodato_model = new Tipodato($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_tipodato = $tipodato_model->getPorId($id);

            echo \json_encode($info_tipodato);

            exit;
        } else if ($this->getRequest()->isPost()) {


            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            //Normal GET

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function nuevatipodatoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $tipodato_model = new Tipodato($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

//            echo "<pre>";
//            print_r($datosFormularios);
//            print_r($array_prueba);
//            exit;

            if ($tipodato_model->agregarNuevo($datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de dato guardado!', 'texto' => "El tipo de dato fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el tipo de dato, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/tipodato");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminartipodatoAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tipodato_model = new Tipodato($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET


            $id = $this->params()->fromRoute("id", null);

            $validar_tipodato = $tipodato_model->getExisteTipodato($id);

            if ($validar_tipodato) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'no se puede eliminar el tipo de dato por que ya hay '
                    . 'afiliados asignados.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipodato");
            } else {
                $tipodato_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar tipo de dato',
                    'descripcion' => "Se elimino el tipo de dato con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de dato Eliminado!', 'texto' => "El tipo de dato fue eliminado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipodato");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablatipodatoAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tipodato_model = new Tipodato($this->dbAdapter);
        $dp = $tipodato_model->getTblTipodato();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object

        $grid = new \EditableGrid();
        if (!$dp) {
            $grid->addColumn('Nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('action', '', 'html', NULL, false, 'id');
        } else {


            $grid->addColumn('nombre_td', 'Nombre', 'string', NULL, false);
//            $grid->addColumn('descripcion', 'Descripcion', 'string', NULL, false);

            $grid->addColumn('action', 'Acción', 'html', NULL, false, 'id');
        }
        $grid->renderXML($dp);
        exit;
    }
    
    public function categoriavalorAction() {

        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $categoriavalor_model = new Categoriavalor($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($categoriavalor_model->agregarNuevo($datosFormularios)) {

                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de una categoria', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo la categoria con nombre {$datosFormularios['nombre_cv']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Categoria guardada!', 'texto' => "La categoria fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categoriavalor");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar la categoria, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categoriavalor");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarcategoriavalorAction() {
        $objeto = 'Mantenimientos';
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
            $categoriavalor_model = new Categoriavalor($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($categoriavalor_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Categoria',
                    'descripcion' => "Se actualizo la informacion de la categoria {$datosFormularios['nombre_cv']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Categoria Actualizada!', 'texto' => "La categoria fue actualizada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categoriavalor");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar la categoria, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categoriavalor");
            }
        } else {
            //Normal GET


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfocategoriavalorAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $categoriavalor_model = new Categoriavalor($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_categoriavalor = $categoriavalor_model->getPorId($id);

            echo \json_encode($info_categoriavalor);

            exit;
        } else if ($this->getRequest()->isPost()) {


            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            //Normal GET

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function nuevacategoriavalorAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $categoriavalor_model = new Categoriavalor($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

//            echo "<pre>";
//            print_r($datosFormularios);
//            print_r($array_prueba);
//            exit;

            if ($categoriavalor_model->agregarNuevo($datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Categoria guardada!', 'texto' => "La categoria fue creada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar la categoria, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/categoriavalor");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarcategoriavalorAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $categoriavalor_model = new Categoriavalor($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET


            $id = $this->params()->fromRoute("id", null);

            $validar_categoriavalor = $categoriavalor_model->getExisteCategoriavalor($id);

            if ($validar_categoriavalor) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'no se puede eliminar la categoria por que ya hay '
                    . 'afiliados asignados.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categoriavalor");
            } else {
                $categoriavalor_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar categoria',
                    'descripcion' => "Se elimino la categoria con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Categoria Eliminada!', 'texto' => "La categoria fue eliminado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categoriavalor");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablacategoriavalorAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $categoriavalor_model = new Categoriavalor($this->dbAdapter);
        $dp = $categoriavalor_model->getTblCategoriavalor();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object

        $grid = new \EditableGrid();
        if (!$dp) {
            $grid->addColumn('Nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('action', '', 'html', NULL, false, 'id');
        } else {


            $grid->addColumn('nombre_cv', 'Nombre', 'string', NULL, false);
//            $grid->addColumn('descripcion', 'Descripcion', 'string', NULL, false);

            $grid->addColumn('action', 'Acción', 'html', NULL, false, 'id');
        }
        $grid->renderXML($dp);
        exit;
    }

    public function dependenciasAction() {

        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $dependencias_model = new Dependencias($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($dependencias_model->agregarNuevo($datosFormularios)) {

                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de una dependencia', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo la dependencia con nombre {$datosFormularios['nombre_dp']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Dependencia guardada!', 'texto' => "La dependencua fue creada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/dependencias");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar la dependencia, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/dependencias");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editardependenciaAction() {
        $objeto = 'Mantenimientos';
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
            $dependencias_model = new Dependencias($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($dependencias_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Dependecia',
                    'descripcion' => "Se actualizo la informacion de la dependencia {$datosFormularios['nombre_dp']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Dependencia Actualizada!', 'texto' => "La dependencia fue actualizada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/dependencias");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar la dependencia, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/dependencias");
            }
        } else {
            //Normal GET


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfodependenciaAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $dependencias_model = new Dependencias($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_dependencia = $dependencias_model->getPorId($id);

            echo \json_encode($info_dependencia);

            exit;
        } else if ($this->getRequest()->isPost()) {


            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            //Normal GET

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function nuevadependenciaAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $dependencias_model = new Dependencias($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

//            echo "<pre>";
//            print_r($datosFormularios);
//            print_r($array_prueba);
//            exit;

            if ($dependencias_model->agregarNuevo($datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Dependencia guardada!', 'texto' => "La dependencia fua creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar la Dependencia, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/dependencias");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminardependenciaAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $dependencias_model = new Dependencias($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET


            $id = $this->params()->fromRoute("id", null);

            $validar_dependencia = $dependencias_model->getExisteDependencia($id);

            if ($validar_dependencia) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'no se puede eliminar la dependedci por que ya hay '
                    . 'afiliados asignados.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/dependencias");
            } else {
                $dependencias_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar dependencia',
                    'descripcion' => "Se elimino la dependencia con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Dependencia Eliminada!', 'texto' => "La Dependecia fue eliminado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/dependencias");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettabladependenciasAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
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
//            $grid->addColumn('descripcion', 'Descripcion', 'string', NULL, false);

            $grid->addColumn('action', 'Acción', 'html', NULL, false, 'id');
        }
        $grid->renderXML($dp);
        exit;
    }
    
    public function tipoexamenAction() {

        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "No cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tipoexamen_model = new tipoexamen($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($tipoexamen_model->agregarNuevo($datosFormularios)) {

                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de un Tipo de Examen', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo el tipo de examen con nombre {$datosFormularios['nombre_dp']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de Examen guardado!', 'texto' => "El Tipo de Examen fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipoexamen");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el tipo de examen, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipoexamen");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editartipoexamenAction() {
        $objeto = 'Mantenimientos';
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
            $tipoexamen_model = new Tipoexamen($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($tipoexamen_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Tipo de Examen',
                    'descripcion' => "Se actualizo la informacion del Tipo de Examen {$datosFormularios['nombre_dp']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de Examen Actualizado!', 'texto' => "El Tipo de Examen fue actualizado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipoexamen");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar el Tipo de Examen, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipoexamen");
            }
        } else {
            //Normal GET


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfotipoexamenAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $tipoexamen_model = new Tipoexamen($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_tipoexamen = $tipoexamen_model->getPorId($id);

            echo \json_encode($info_tipoexamen);

            exit;
        } else if ($this->getRequest()->isPost()) {


            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            //Normal GET

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function nuevatipoexamenAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $tipoexamen_model = new Tipoexamen($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

//            echo "<pre>";
//            print_r($datosFormularios);
//            print_r($array_prueba);
//            exit;

            if ($tipoexamen_model->agregarNuevo($datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de Examen guardado!', 'texto' => "El Tipo de Examen fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/objetos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el Tipo de Examen, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/tipoexamen");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminartipoexamenAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "No cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tipoexamen_model = new Tipoexamen($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET


            $id = $this->params()->fromRoute("id", null);

            $validar_tipoexamen = $tipoexamen_model->getExisteTipoexamen($id);

            if ($validar_tipoexamen) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede eliminar el Tipo de Examen por que ya hay '
                    . 'afiliados asignados.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipoexamen");
            } else {
                $tipoexamen_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Tipo de Examen',
                    'descripcion' => "Se elimino el Tipo de Examen con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Tipo de Examen Eliminado!', 'texto' => "El Tipo de Examen fue eliminado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/tipoexamen");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablatipoexamenAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "No cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        //Adaptador para la base de datos
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $tipoexamen_model = new Tipoexamen($this->dbAdapter);
        $dp = $tipoexamen_model->getTblTipoexamen();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object

        $grid = new \EditableGrid();
        if (!$dp) {
            $grid->addColumn('Nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('action', '', 'html', NULL, false, 'id');
        } else {


            $grid->addColumn('nombre_dp', 'Nombre', 'string', NULL, false);
//            $grid->addColumn('descripcion', 'Descripcion', 'string', NULL, false);

            $grid->addColumn('action', 'Acción', 'html', NULL, false, 'id');
        }
        $grid->renderXML($dp);
        exit;
    }    

    public function medicosAction() {
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

    public function gttblespecialidadAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $especialidades_model = new Especialidades($this->dbAdapter);
        $especialidades = $especialidades_model->getEspecialidad();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('nombre_e', 'Nombre', 'string', NULL, false);
        $grid->addColumn('action', 'Opciones', 'html', NULL, false, 'id');

        $grid->renderXML($especialidades);
        exit;
    }

    public function especialidadesAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $especialidades_model = new Especialidades($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($especialidades_model->agregarNuevo($datosFormularios)) {
//                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de una especialidad', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo la especialidad con nombre {$datosFormularios['nombre_e']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Especialidad guardada!', 'texto' => "La especialidad fue creada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/especialidades");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar la especialidad, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/especialidades");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfoespecialidadAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $especialidades_model = new Especialidades($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_especialidad = $especialidades_model->getPorId($id);

            echo \json_encode($info_especialidad);

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

    public function editarespecialidadAction() {
        $objeto = 'Mantenimientos';
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
            $especialidades_model = new Especialidades($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($especialidades_model->actualizar($id, $datosFormularios)) {


                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Editar Especialidad',
                    'descripcion' => "Se edito la informacion de la especialidad con nombre {$datosFormularios['nombre_e']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Especialidad Actualizada!', 'texto' => "La especialidad fue Actalizado exitosamente.");

                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/especialidades");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al Actualizar la especialidad, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/especialidades");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarespecialidadesAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $especialidades_model = new Especialidades($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {



            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $id = $this->params()->fromRoute("id", null);

            $validar_especialidad = $especialidades_model->getExisteEspecialidad($id);

            if ($validar_especialidad) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede eliminar la especialidad debido a que '
                    . 'hay medicos asignados ella, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/especialidades");
            } else {

                $especialidades_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Especialidad',
                    'descripcion' => "Se elimino el la especialidad con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Especialidad eliminada!', 'texto' => "La especialidad fue eliminada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/especialidades");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function ecivilAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $esciviles_model = new Esciviles($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($esciviles_model->agregarNuevo($datosFormularios)) {

                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de un estado civil', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo el estado civil con nombre {$datosFormularios['nombre_es']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado civil guardado!', 'texto' => " EL estado civil fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/ecivil");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el estado civil, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/ecivil");
            }
        } else {
            //Normal GET

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarecivilAction() {
        $objeto = 'Mantenimientos';
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
            $esciviles_model = new Esciviles($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($esciviles_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Estado Civil',
                    'descripcion' => "Se actualizo la informacion del estado civil {$datosFormularios['nombre_es']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado Civil Actualizado!', 'texto' => "El estado civil fue actualizada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/ecivil");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar  estado civil, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/ecivil");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfoecivilAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $esciviles_model = new Esciviles($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_estado = $esciviles_model->getPorId($id);

            echo \json_encode($info_estado);

            exit;
        } else if ($this->getRequest()->isPost()) {
            
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarecivilAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $ecivil_model = new Esciviles($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $id = $this->params()->fromRoute("id", null);

            $validar_ecivil = $ecivil_model->getExisteEscivil($id);

            if ($validar_ecivil) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede borrar el estado civil debido a que hay '
                    . 'afiliados que lo estan utilizando , intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/ecivil");
            } else {

                $ecivil_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Estado civil',
                    'descripcion' => "Se elimino el estado civil con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado civil eliminado!', 'texto' => " EL estado civil fue eliminado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/ecivil");
            }


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettblcivilAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $esciviles_model = new Esciviles($this->dbAdapter);

        $estados = $esciviles_model->getEscivil();

        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

// create a new EditableGrid object
        $grid = new \EditableGrid();

        $grid->addColumn('nombre_es', 'Nombre Completo', 'varchar', NULL, false); //ocupo nombre completo
//        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
        $grid->addColumn('action', 'Opciones', 'html', NULL, false, 'id');

        $grid->renderXML($estados);
        exit;
    }

    public function categoriasAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $categorias_model = new Categorias($this->dbAdapter);

        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($categorias_model->agregarNuevo($datosFormularios)) {

                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de una categoria', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo la categori con nombre {$datosFormularios['nombre_c']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Categoria guardada!', 'texto' => "La catrgoria fue creada exitosamente.<br><br>$btn_imprimir");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categorias");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar la categoria, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categorias");
            }
        } else {
//Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarcategoriaAction() {
        $objeto = 'Mantenimientos';
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
            $categorias_model = new Categorias($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($categorias_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Categoria',
                    'descripcion' => "Se actualizo la informacion de la categoria {$datosFormularios['nombre_c']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Categoria Actualizada!', 'texto' => "La categoria fue actualizada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categorias");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar la categoria, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categorias");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfocategoriaAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $categorias_model = new Categorias($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_categoria = $categorias_model->getPorId($id);

            echo \json_encode($info_categoria);

            exit;
        } else if ($this->getRequest()->isPost()) {
//            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
//            
            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarcategoriaAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $categorias_model = new Categorias($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $id = $this->params()->fromRoute("id", null);

            $validar_catecoria = $categorias_model->getExisteCategoria($id);

            if ($validar_catecoria) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede eliminar la categoria por que hay afiliados'
                    . 'que la estan utilizando, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categorias");
            } else {


                $categorias_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Categoria',
                    'descripcion' => "Se elimino la categoria con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Categoria eliminada!', 'texto' => "La catrgoria fue eliminada exitosamente.<br><br>$btn_imprimir");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/categorias");
            }


            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablacategoriasAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }


        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $categorias_model = new Categorias($this->dbAdapter);
        $categorias = $categorias_model->getCategoria();

        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');
// create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('nombre_c', 'nombre', 'string', NULL, false);

//        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
        $grid->addColumn('action', 'Opciones', 'html', NULL, false, 'id');

        $grid->renderXML($categorias);
        exit;
    }

    public function rangosAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $rangos_model = new Rangos($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($rangos_model->agregarNuevo($datosFormularios)) {
//                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de un rango', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo el rango con nombre {$datosFormularios['nombre_r']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Rango guardado!', 'texto' => "El rango fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/rangos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el rango, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/rangos");
            }
        } else {
//Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editarrangoAction() {
        $objeto = 'Mantenimientos';
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
            $rangos_model = new Rangos($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($rangos_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar rangos',
                    'descripcion' => "Se actualizo la informacion del rango {$datosFormularios['nombre_r']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Rango Actualizado!', 'texto' => "El Rango fue actualizada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/rangos");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar el rango, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/rangos");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinforangoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['a'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $rangos_model = new Rangos($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_rango = $rangos_model->getPorId($id);

            echo \json_encode($info_rango);

            exit;
        } else if ($this->getRequest()->isPost()) {
            
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarrangoAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $rangos_model = new Rangos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {



            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $id = $this->params()->fromRoute("id", null);

            $validar_rango = $rangos_model->getExiterango($id);

            if ($validar_rango) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede eliminar el rango por que hay afiliados'
                    . 'que lo estan utilizando , intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/rangos");
            } else {

                $rangos_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Rango',
                    'descripcion' => "Se elimino el rango con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Rango eliminado!', 'texto' => "El rango fue eliminado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/rangos");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettablarangosAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $rangos_model = new Rangos($this->dbAdapter);
        $rangos = $rangos_model->getRango();

// Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

// create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('nombre_r', 'Nombre', 'string', NULL, false);
        $grid->addColumn('action', 'Opciones', 'html', NULL, false, 'id');

        $grid->renderXML($rangos);
        exit;
    }
    

    public function departamentosAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $departamentos_model = new Asignaciones($this->dbAdapter);
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
//para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            if ($departamentos_model->agregarNuevo($datosFormularios)) {
//                      
//                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de un departamento', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "se creo el departamento con nombre {$datosFormularios['nombre_d']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Departamento guardado!', 'texto' => "EL departamento fue creada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/departamentos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el departamento, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/departamentos");
            }
        } else {
//Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function editardepartamentoAction() {
        $objeto = 'Mantenimientos';
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
            $departamentos_model = new Asignaciones($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($departamentos_model->actualizar($id, $datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Departamento Actualizaso!', 'texto' => "EL departamento fue Actalizado exitosamente.");

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Editar Departamento',
                    'descripcion' => "Se edito la informacion del departamento {$datosFormularios['nombre_d']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/departamentos");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al Actualizar el departamento, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/departamentos");
            }
        } else {
            //Normal GET




            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getinfodepartamentoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $departamentos_model = new Asignaciones($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_departamento = $departamentos_model->getPorId($id);

            echo \json_encode($info_departamento);

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

    public function eliminardepartamentoAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

        $asignaciones_model = new Asignaciones($this->dbAdapter);

        $id = $this->params()->fromRoute("id", null);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            
        } else {
            //Normal GET

            $id = $this->params()->fromRoute("id", null);

            $validar_departamentos = $asignaciones_model->getExisteDepartamento($id);
            $validar_asiganacion = $asignaciones_model->getExisteAsiganacion($id);

            if ($validar_departamentos || $validar_asiganacion) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'no se puede eliminar el departamento por que ya hay '
                    . 'afiliados asiganados a el.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/departamentos");
            } else {

                $asignaciones_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar departamento',
                    'descripcion' => "Se elimino el departamento con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Departamento Eliminada!', 'texto' => "El Departamento fue eliminado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/departamentos");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettabladepartamentosAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }


        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $departamentos_model = new Asignaciones($this->dbAdapter);
        $departamentos = $departamentos_model->getAsignacion();

// Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

// create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('nombre_d', 'Nombre', 'string', NULL, false);
//        $grid->addColumn('estado_u', 'Estado', 'string', NULL, false);
        $grid->addColumn('action', 'Opciones', 'html', NULL, false, 'id');

        $grid->renderXML($departamentos);
        exit;
    }

//    INICIO EXPORTACION DE LOS PDF
    public function exportarpdfdependenciasAction() {
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
            //AQUI EL PDF

            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

//            $id_suscursal = $_SESSION['auth']['id_sucursal'];
            //RECUPERAR LA INFORMACION DEL REPORTE

            $dependencias_model = new Dependencias($this->dbAdapter);
            $dp = $dependencias_model->getTblDependencia();

//            echo "<pre>";
//            print_r($dp);
//            exit;

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
            $subtitulo = "\n\nReporte De Dependecnias";
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
            $header = ['N°', 'Dependecnia', 'Descripción'];
            $w = array($m * .02, $m * .50, $m * .46);

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

            foreach ($dp as $fila) {

                //Corregir textos


                $nom_dependencia = $utilidad_texto->fixTexto($fila['nombre_dp']);
                $descripcion_dependencia = $utilidad_texto->fixTexto($fila['descripcion']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nom_dependencia, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[2], 4.2, $descripcion_dependencia, 'LR', 0, 'L', $fill);
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
            exit;

            //FIN PDF  
        }
    }

//DEPARTAMENTOS
    public function exportarpdfdepartamentosAction() {
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
            //AQUI EL PDF
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $departamentos_model = new Asignaciones($this->dbAdapter);
            $departamentos = $departamentos_model->getAsignacion();

//             echo "<pre>";
//          print_r($departamentos);
//           exit;

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Departamentos');
            $pdf->SetSubject('Departamentos');
            $pdf->SetKeywords('Departamentos');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Departamentos";
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
            $header = ['N°', 'Departamentos', 'Creado Por'];
            $w = array($m * .02, $m * .50, $m * .48);

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

            foreach ($departamentos as $fila) {

                //Corregir textos


                $nom_departamento = $utilidad_texto->fixTexto($fila['nombre_d']);
                $creado_por = $utilidad_texto->fixTexto($fila['creado_por']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nom_departamento, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[2], 4.2, $creado_por, 'LR', 0, 'L', $fill);
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
            $pdf->Output("DEPARTAMENTOS" . '.pdf', 'I');
            exit;

            //FIN PDF
        }
    }

    public function exportarpdfrangosAction() {
// if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
// return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
// }
        if ($this->getRequest()->isXmlHttpRequest()) {
//para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {



//$id = $this->params()->fromRoute("id", null);
//$datosFormularios = $this->request->getPost()->toArray();
        } else {
//AQUI EL PDF
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $rangos_model = new Rangos($this->dbAdapter);
            $rangos = $rangos_model->getRango();

// RANGOS PDF
// echo "<pre>";
// print_r($rangos);
// exit;
// DESDE AQUI LLAMAR PDF
            $utilidad_texto = new Texto();

// Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

// create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Rangos');
            $pdf->SetSubject('Rangos');
            $pdf->SetKeywords('Rangos');

// Configurar los datos de la imagen
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Rangos";
// $linea_auxiliar = " ";
// $subtitulo1 = "";



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
            $header = ['N°', 'Nombre Rango', 'Descripción'];
            $w = array($m * .02, $m * .50, $m * .48);

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

            foreach ($rangos as $fila) {



//Corregir textos




                $nom_rango = $utilidad_texto->fixTexto($fila['nombre_r']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);

//Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nom_rango, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[2], 4.2, $descripcion, 'LR', 0, 'L', $fill);
// ['N°', 'Dependecnia', 'Descripción'];



                $pdf->Ln();
                $fill = !$fill;
                $cont++;
            }



            $pdf->Cell($m, 0, '', 'T');
            $pdf->Ln();

            $pdf->Ln();
            $pdf->Cell($m, 4.2, "========== FIN DEL REPORTE ==========", '', 0, 'C', false);
            $pdf->Cell($m, 0, '', '');
            $pdf->Ln();

            \ob_clean();
// Cerrar enviar documento PDF , la I (i)= lo muestra en pantalla D = lo descarga a la maquina del cliente
            $pdf->Output("ESPECIALIDADES" . '.pdf', 'I');
            exit;
        }
    }

    public function exportarpdfcategoriasAction() {
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
            //AQUI EL PDF
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $categorias_model = new Categorias($this->dbAdapter);
            $categorias = $categorias_model->getCategoria();

//              echo "<pre>";
//      print_r($categorias);
//         exit;
//            CATEGORIAS PDF

            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

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
            $subtitulo = "\n\nReporte De Categorias";
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
            $header = ['N°', 'Nombre Categoria', 'Descripción'];
            $w = array($m * .02, $m * .50, $m * .48);

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

            foreach ($categorias as $fila) {

                //Corregir textos


                $nom_categoria = $utilidad_texto->fixTexto($fila['nombre_c']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nom_categoria, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[2], 4.2, $descripcion, 'LR', 0, 'L', $fill);
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
            $pdf->Output("CATEGORIAS" . '.pdf', 'I');
            exit;
        }
    }

    public function exportarpdfespecialidadesAction() {
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
            //AQUI EL PDF
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $especialidades_model = new Especialidades($this->dbAdapter);
            $especialidades = $especialidades_model->getEspecialidad();

//            ESPECIALIDADES PDF
//             echo "<pre>";
//          print_r($especialidades);
//           exit;
// DESDE AQUI LLAMAR PDF
            $utilidad_texto = new Texto();

            // Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

            // create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('L', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema de Gestión de Citas');
            $pdf->SetTitle('Rerporte de Departamentos');
            $pdf->SetSubject('Departamentos');
            $pdf->SetKeywords('Departamentos');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte De Especialidades";
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
            $header = ['N°', 'Nombre Especialidad', 'Descripción'];
            $w = array($m * .02, $m * .50, $m * .48);

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

            foreach ($especialidades as $fila) {

                //Corregir textos


                $nom_especialidad = $utilidad_texto->fixTexto($fila['nombre_e']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nom_especialidad, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[2], 4.2, $descripcion, 'LR', 0, 'L', $fill);
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
            $pdf->Output("ESPECIALIDADES" . '.pdf', 'I');
            exit;
        }
    }

    public function exportarpdfestadoscivilAction() {
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
            //AQUI EL PDF
        }
    }

    public function exportarexceldependenciasAction() {
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

//            $id_suscursal = $_SESSION['auth']['id_sucursal'];
            //RECUPERAR LA INFORMACION DEL REPORTE

            $dependencias_model = new Dependencias($this->dbAdapter);
            $dp = $dependencias_model->getTblDependencia();
        }
    }

    public function exportarexceldepartamentosAction() {
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
            //AQUI EL EXCEL
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $departamentos_model = new Asignaciones($this->dbAdapter);
            $departamentos = $departamentos_model->getAsignacion();

//             echo "<pre>";
//          print_r($departamentos);
//           exit;
        }
    }

    public function exportarexcelrangosAction() {
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
            $ragos_model = new Rangos($this->dbAdapter);

            $datos_excel = $ragos_model->getRango();

            $total_filas = \count($datos_excel);

            // FIN PASO 1
            //PASO 2 -> VARIABLES PARA LA CREACION Y CONTROL DEL EXCEL
            $txt_fecha = \date('d-m-Y_H-i-s');
            $titulo = "PACIENTES TRIAJE $txt_fecha";
            $nombre_archivo = "PACIENTES TRIAJE $txt_fecha.xlsx"; //NOMBRE DEL ARCHIVO
            //Encabezados de las columnas
            $titulos_columnas = ['N°', 'Nombre', 'Descripcion', 'Fecha DE Creacion'];

            //INFORMACIÓN DEL ENCABEZADO
            $encabezado_linea_1 = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $encabezado_linea_2 = "FHEP";
            $encabezado_linea_3 = "";
            $encabezado_linea_4 = "LISTA DE RANGOS";

            $ultima_columna = "D";
            $num_fila_titulos = 6;
            $num_fila_inicio_info = 7;  //Numero de fila donde se va a comenzar a rellenar la información
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
            $excel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);

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

                $nombre = $utilidad_texto->fixTexto($fila['nombre_r']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);
                $fecha_creacion = $fila['fecha_creacion'];

                //Insertar la información el la celda correspondiente
                $excel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $f, $cont)
                        ->setCellValueExplicit('B' . $f, $nombre, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('C' . $f, $descripcion, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('D' . $f, \PHPExcel_Shared_Date::PHPToExcel(new \DateTime($fecha_creacion)));

                $f++;
                $cont++;
            }




            //Dar formato de fecha 
            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                $excel->getSheet(0)->getStyle('D' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
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

    public function exportarexcelcategoriasAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        } else {
            //AQUI EL EXCEL
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $categorias_model = new Categorias($this->dbAdapter);

            $datos_excel = $categorias_model->getCategoria();
            $total_filas = \count($datos_excel);

            //INFORMACIÓN DE EXCEL
            //PASO 2 -> VARIABLES PARA LA CREACION Y CONTROL DEL EXCEL
            $txt_fecha = \date('d-m-Y_H-i-s');
            $titulo = "Categorias $txt_fecha";
            $nombre_archivo = "TABLA DE CATEGORIAS $txt_fecha.xlsx"; //NOMBRE DEL ARCHIVO
            //Encabezados de las columnas
            $titulos_columnas = ['N°', 'Id Categoria', 'Nombre Categoria', 'Descripción', 'Creado Por', 'Fecha Creación', 'Modificado Por', 'Fecha Modificación'];

            //INFORMACIÓN DEL ENCABEZADO
            $encabezado_linea_1 = "FUNDACIÓN HOSPITAL DE EMERCENCIA POLICIAL";
            $encabezado_linea_2 = "MODULO DE MANTENIMIENTOS";
            $encabezado_linea_3 = "";
            $encabezado_linea_4 = "Mantenimientos";

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
                $id = $fila['id'];
                $nombre_c = $utilidad_texto->fixTexto($fila['nombre_c']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);
                $creado_por = $utilidad_texto->fixTexto($fila['creado_por']);
                $fecha_creacion = $fila['fecha_creacion'];
                $modificado_por = $utilidad_texto->fixTexto($fila['modificado_por']);
                $fecha_modificacion = $fila['fecha_modificacion'];

                //Insertar la información el la celda correspondiente
                $excel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $f, $cont)
                        ->setCellValueExplicit('B' . $f, $id, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('C' . $f, $nombre_c, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('D' . $f, $descripcion, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('E' . $f, $creado_por, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('F' . $f, \PHPExcel_Shared_Date::PHPToExcel(new \DateTime($fecha_creacion)))
                        ->setCellValueExplicit('G' . $f, $modificado_por, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('H' . $f, \PHPExcel_Shared_Date::PHPToExcel(new \DateTime($fecha_modificacion)));

                $f++;
                $cont++;
            }

            //Dar formato de fecha 
            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                $excel->getSheet(0)->getStyle('H' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
                        ->getNumberFormat()->setFormatCode($formato);
            }
            
            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                $excel->getSheet(0)->getStyle('F' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
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
            $excel->getActiveSheet()->setTitle("CATEGORIAS");

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
            // FIN INFORMACIÓN DE EXCEL
        }
    }

    public function exportarexcelespecialidadesAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            return $this->redirect()->toUrl($this->getRequest()->GetBaseUrl() . "/");
        } else {
            //AQUI EL EXCEL
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $especialidades_model = new Especialidades($this->dbAdapter);
            $datos_excel = $especialidades_model->getEspecialidad();

            $total_filas = \count($datos_excel);
            //INFORMACIÓN DE EXCEL
            //PASO 2 -> VARIABLES PARA LA CREACION Y CONTROL DEL EXCEL
            $txt_fecha = \date('d-m-Y_H-i-s');
            $titulo = "Especialidades $txt_fecha";
            $nombre_archivo = "TABLA DE ESPECIALIDADES $txt_fecha.xlsx"; //NOMBRE DEL ARCHIVO
            //Encabezados de las columnas
            $titulos_columnas = ['N°', 'Id Especialidad', 'Nombre Especialidad', 'Descripción', 'Estado', 'Creado Por', 'Fecha Creación', 'Modificado Por', 'Fecha Modificación'];

            //INFORMACIÓN DEL ENCABEZADO
            $encabezado_linea_1 = "FUNDACIÓN HOSPITAL DE EMERCENCIA POLICIAL";
            $encabezado_linea_2 = "MODULO DE MANTENIMIENTOS";
            $encabezado_linea_3 = "";
            $encabezado_linea_4 = "Especialidades";

            $ultima_columna = "I";
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
                $id = $fila['id'];
                $nombre_e = $utilidad_texto->fixTexto($fila['nombre_e']);
                $descripcion = $utilidad_texto->fixTexto($fila['descripcion']);
                $estado = $utilidad_texto->fixTexto($fila['estado']);
                $creado_por = $utilidad_texto->fixTexto($fila['creado_por']);
                $fecha_creacion = $fila['fecha_creacion'];
                $modificado_por = $utilidad_texto->fixTexto($fila['modificado_por']);
                $fecha_modificacion = $fila['fecha_modificacion'];

                //Insertar la información el la celda correspondiente
                $excel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $f, $cont)
                        ->setCellValueExplicit('B' . $f, $id, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('C' . $f, $nombre_e, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('D' . $f, $descripcion, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('E' . $f, $estado, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('F' . $f, $creado_por, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('G' . $f, \PHPExcel_Shared_Date::PHPToExcel(new \DateTime($fecha_creacion)))
                        ->setCellValueExplicit('H' . $f, $modificado_por, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('I' . $f, \PHPExcel_Shared_Date::PHPToExcel(new \DateTime($fecha_modificacion)));

                $f++;
                $cont++;
            }

            //Dar formato de fecha 
            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                $excel->getSheet(0)->getStyle('G' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
                        ->getNumberFormat()->setFormatCode($formato);
            }

            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                $excel->getSheet(0)->getStyle('I' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
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
            $excel->getActiveSheet()->setTitle("CATEGORIAS");

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
            // FIN INFORMACIÓN DE EXCEL
        }
    }

    public function exportarexcelestadoscivilAction() {
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
            $esciviles_model = new Esciviles($this->dbAdapter);
            $datos_excel = $esciviles_model->getEscivil();

            $total_filas = \count($datos_excel);
            //INFORMACIÓN DE EXCEL
            //PASO 2 -> VARIABLES PARA LA CREACION Y CONTROL DEL EXCEL
            $txt_fecha = \date('d-m-Y_H-i-s');
            $titulo = "Categorias $txt_fecha";
            $nombre_archivo = "TABLA DE ESTADOS CIVILES $txt_fecha.xlsx"; //NOMBRE DEL ARCHIVO
            //Encabezados de las columnas
            $titulos_columnas = ['N°', 'Id Estado Civil', 'Nombre Estado', 'Creado Por', 'Fecha Creación', 'Modificado Por', 'Fecha Modificación'];

            //INFORMACIÓN DEL ENCABEZADO
            $encabezado_linea_1 = "FUNDACIÓN HOSPITAL DE EMERCENCIA POLICIAL";
            $encabezado_linea_2 = "MODULO DE MANTENIMIENTOS";
            $encabezado_linea_3 = "";
            $encabezado_linea_4 = "Estado Civil";

            $ultima_columna = "G";
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
                $id = $fila['id'];
                $nombre_es = $utilidad_texto->fixTexto($fila['nombre_es']);
                $creado_por = $utilidad_texto->fixTexto($fila['creado_por']);
                $fecha_creacion = $fila['fecha_creacion'];
                $modificado_por = $utilidad_texto->fixTexto($fila['modificado_por']);
                $fecha_modificacion = $fila['fecha_modificacion'];

                //Insertar la información el la celda correspondiente
                $excel->setActiveSheetIndex(0)
                        ->setCellValue('A' . $f, $cont)
                        ->setCellValueExplicit('B' . $f, $id, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('C' . $f, $nombre_es, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValueExplicit('D' . $f, $creado_por, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('E' . $f, \PHPExcel_Shared_Date::PHPToExcel(new \DateTime($fecha_creacion)))
                        ->setCellValueExplicit('F' . $f, $modificado_por, \PHPExcel_Cell_DataType::TYPE_STRING)
                        ->setCellValue('G' . $f, \PHPExcel_Shared_Date::PHPToExcel(new \DateTime($fecha_modificacion)));

                $f++;
                $cont++;
            }

            //Dar formato de fecha 
            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                $excel->getSheet(0)->getStyle('G' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
                        ->getNumberFormat()->setFormatCode($formato);
            }

            for ($j = $num_fila_inicio_info; $j < $total_filas + $num_fila_inicio_info; $j++) {
                $excel->getSheet(0)->getStyle('E' . $j)//INDICAR LA COLUMNA A APLICAR ej. H4 o tambien en rango H4:J4
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
            $excel->getActiveSheet()->setTitle("ESTADOS CIVILES");

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

    public function sucursalesAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $sucursales_model = new Sucursales($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

            if ($sucursales_model->agregarNuevo($datosFormularios)) {
                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');

                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de una sucursal', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "Se creo la sucursal con nombre {$datosFormularios['nombre_sucursal']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Sucursal Guardado!', 'texto' => "La sucursal fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/sucursales");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar la sucursal, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/sucursales");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettblsucursalesAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $suscursales_model = new Sucursales($this->dbAdapter);

        $sucursales = $suscursales_model->getAllSucursales();

        foreach ($sucursales as $f => $c) {

            if ($c['estado'] == '1') {
                $sucursales[$f]['estado'] = "Activo";
            } else if ($c['estado'] == '0') {
                $sucursales[$f]['estado'] = "Inactivo";
            } else {
                $sucursales[$f]['estado'] = "Desconocido";
            }
        }

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        $grid = new \EditableGrid();

        if (!$sucursales) {
            $grid->addColumn('nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('opciones', '', 'html', NULL, false, 'id');
        } else {
            // create a new EditableGrid object
            $grid->addColumn('nombre_sucursal', 'Nombres', 'string', NULL, false);
            $grid->addColumn('descripcion', 'Descripcion', 'string', NULL, false);
            $grid->addColumn('estado', 'Estado', 'string', NULL, false);

            $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'Id');
        }
        $grid->renderXML($sucursales);
        exit;
    }

    public function getinfosucursalAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $sucursales_model = new Sucursales($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_sucursal = $sucursales_model->getPorId($id);

            echo \json_encode($info_sucursal);

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

    public function editarsucursalAction() {
        $objeto = 'Mantenimientos';
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
            $sucursales_model = new Sucursales($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($sucursales_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Sucursal',
                    'descripcion' => "Se actualizo la informacion de la sucursal {$datosFormularios['nombre_sucursal']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Sucursal Actualizada!', 'texto' => "La sucursal fue actualizada exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/sucursales");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar la sucursal, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/sucursales");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarsucursalAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
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
            $sucursales_model = new Sucursales($this->dbAdapter);
            $id = $this->params()->fromRoute("id", null);

            $validar_sucursal_u = $sucursales_model->getExisteUsuario($id);

            $validar_sucursal_c = $sucursales_model->getExistenCitas($id);

            if ($validar_sucursal_u || $validar_sucursal_c) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede eliminar la sucursal por que ya'
                    . ' dejo lo marca en los registros del sistema , intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/sucursales");
            } else {

                $sucursales_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Sucursal',
                    'descripcion' => "Se elimino la sucursal con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Sucursal Eliminada!', 'texto' => "La suscursal fue eliminado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/sucursales");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function estadosuAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $objetos_model = new Objetos($this->dbAdapter);
            $utl_bitacora = new Bitacora($this->dbAdapter);
            $estadosu_model = new Estadosu($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            if ($estadosu_model->agregarNuevo($datosFormularios)) {
                //                      GUARDAR A BITACORA

                $DateAndTime = date('Y-m-d H:i:s');
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de estado', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => "Se creo el estado con nombre {$datosFormularios['nombre']}"];
                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado Guardado!', 'texto' => "La sucursal fue creado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosu");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al guardar el estado, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosu");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function gettblestadosuAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $estadosu_model = new Estadosu($this->dbAdapter);

        $estados = $estadosu_model->getEstadosU();

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        $grid = new \EditableGrid();

        if (!$estados) {
            $grid->addColumn('nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('opciones', '', 'html', NULL, false, 'id');
        } else {
            // create a new EditableGrid object
            $grid->addColumn('nombre', 'Nombres', 'string', NULL, false);
            $grid->addColumn('descripcion', 'Descripcion', 'string', NULL, false);
            $grid->addColumn('estado', 'Estado', 'string', NULL, false);

            $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id');
        }
        $grid->renderXML($estados);
        exit;
    }

    public function getinfoestadosuAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            $estadosu_model = new Estadosu($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            $info_estadou = $estadosu_model->getPorId($id);

            echo \json_encode($info_estadou);

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

    public function editarestadosuAction() {
        $objeto = 'Mantenimientos';
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
            $estadosu_model = new Estadosu($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id'];

            unset($datosFormularios['id']);

            if ($estadosu_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Actualizar Estado',
                    'descripcion' => "Se actualizo la informacion de la estado {$datosFormularios['nombre_sucursal']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado Actualizado!', 'texto' => "La estado fue actualixado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosu");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar el estado, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosu");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarestadoeAction() {
        $objeto = 'Mantenimientos';
        $permiso = 'permiso_eliminacion';
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
            $estadosu_model = new Estadosu($this->dbAdapter);

            $id = $this->params()->fromRoute("id", null);

            $info = $estadosu_model->getExisteEstadoU($id);

            if ($info) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede eliminar es estado por que ya'
                    . ' hay usuarios utilizandolo , intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosu");
            } else {

                $estadosu_model->borrar($id);

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoMantenimiento();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Estado',
                    'descripcion' => "Se elimino el estado con id {$id}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Estado Borradol!', 'texto' => "La estado fue eliminado exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/mantenimientos/estadosu");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }
    
}
