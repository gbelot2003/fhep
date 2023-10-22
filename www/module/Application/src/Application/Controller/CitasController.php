<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
/* use Application\Model\Mimodelo; */
use Application\Model\Especialidades;
use Application\Model\Personas;
use Application\Model\Citas;
use Application\Model\Afiliados;
use Application\Model\Estadoscitas;
use Application\Model\Objetos;
use Application\Model\Medicos;
use Application\Model\Bitacoras;
//librerias extras
use Application\Extras\Utilidades\Fechas;
use Application\Extras\Utilidades\Bitacora;
use Application\Extras\Excel\EstilosExcel;
use Application\Extras\Utilidades\Texto;

class CitasController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function citasAction() {
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

    public function gettblgeneralcitasAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $citas_model = new Citas($this->dbAdapter);

        $id_suscursal = $_SESSION['auth']['id_sucursal'];
//        $fecha_actual = date("Y-m-d%");

        $id_rol = $_SESSION['auth']['id_rol'];

        $citas = $citas_model->getTblHistorico($id_suscursal, $id_rol);

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        $grid = new \EditableGrid();
        if (!$citas) {
            $grid->addColumn('nombre', 'NO SE ENCONTRARON REGISTROS', 'string', NULL, false);
            $grid->addColumn('opciones', '', 'html', NULL, false, 'id');
        } elseif ($id_rol == 1 || $id_rol == 5) {
            // create a new EditableGrid object

            $grid->addColumn('dni', 'N° Expediente', 'string', NULL, false);
            $grid->addColumn('nombre_completo', 'Nombre Completo', 'string', NULL, false);
            $grid->addColumn('motivo_consulta', 'Motivo consulta', 'string', NULL, false);
//            $grid->addColumn('observaciones', 'Obs. preclinica', 'string', NULL, false);
            $grid->addColumn('nombre_medico', 'Medico', 'string', NULL, false);
            $grid->addColumn('especialidad', 'especialidad', 'string', NULL, false);
            $grid->addColumn('estado', 'Estado Cita', 'string', NULL, false);
            $grid->addColumn('fecha_cita', 'Fecha de consulta', 'date', NULL, false);
            $grid->addColumn('nombre_sucursal', 'Sucursal', 'string', NULL, false);

            $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_cita');
        } else {
            $grid->addColumn('dni', 'N° Expediente', 'string', NULL, false);
            $grid->addColumn('nombre_completo', 'Nombre Completo', 'string', NULL, false);
            $grid->addColumn('motivo_consulta', 'Motivo consulta', 'string', NULL, false);
//            $grid->addColumn('observaciones', 'Obs. preclinica', 'string', NULL, false);
            $grid->addColumn('nombre_medico', 'Medico', 'string', NULL, false);
            $grid->addColumn('especialidad', 'especialidad', 'string', NULL, false);
            $grid->addColumn('estado', 'Estado Cita', 'string', NULL, false);
            $grid->addColumn('fecha_cita', 'Fecha de consulta', 'date', NULL, false);

            $grid->addColumn('opciones', 'Opciones', 'html', NULL, false, 'id_cita');
        }
        $grid->renderXML($citas);
        exit;
    }

    public function nuevaAction() {

        $objeto = 'Citas';
        $permiso = 'permiso_insercion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);
            $afiliado_model = new Afiliados($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $utl_bitacora = new Bitacora($this->dbAdapter);

            $DateAndTime = date('Y-m-d');

            $dni = ['dni' => $datosFormularios['dni']];

            unset($datosFormularios['dni']);

            $info = $citas_model->getTipocita($datosFormularios['id_afiliado']);

            $id_especialidad = $datosFormularios['id_especialidad'];
            $fecha_cita = date("Y-m-d", strtotime($datosFormularios['fecha_cita']));

            $fecha_mostrar = date("d-m-Y", strtotime($datosFormularios['fecha_cita']));

            $cant_citas = $citas_model->getCamtCitas($datosFormularios['id_medico'], $fecha_cita);

            if ($fecha_cita < $DateAndTime) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Fecha de cita inválida.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            }

            //VALIDAR CANTIDAD DE CUPOS
            if ($id_especialidad == 1) {

                if ($cant_citas['cant_citas'] >= $_SESSION['parametros']['max_cita_ginecologia']) {

                    $_SESSION['mnsWar'] = array('titulo' => 'Limite Alcansado!', 'texto' => "El ginecologo no tiene más cupos disponibles para "
                        . "la fecha {$fecha_mostrar}.");
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
                }
            }



            //VALIDAR CANTIDAD DE CUPOS
            if ($id_especialidad == 2) {

                if ($cant_citas['cant_citas'] >= $_SESSION['parametros']['max_cita_psicologia']) {

                    $_SESSION['mnsWar'] = array('titulo' => 'Limite Alcansado!', 'texto' => "El psicologo no tiene más cupos disponibles para "
                        . "la fecha {$fecha_mostrar}.");
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
                }
            }


            //VALIDAR CANTIDAD DE CUPOS
            if ($id_especialidad == 3) {
                if ($cant_citas['cant_citas'] >= $_SESSION['parametros']['max_cita_nutricion']) {

                    $_SESSION['mnsWar'] = array('titulo' => 'Limite Alcansado!', 'texto' => "El nutricionista no tiene más cupos disponibles para "
                        . "la fecha {$fecha_mostrar}.");
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
                }
            }

            if ($id_especialidad == 4) {

                if ($cant_citas['cant_citas'] >= $_SESSION['parametros']['max_cita_medico_general']) {

                    $_SESSION['mnsWar'] = array('titulo' => 'Limite Alcansado!', 'texto' => "El médico no tiene más cupos disponibles para "
                        . "la fecha {$fecha_mostrar}.");
                    return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
                }
            }


            if (!$info) {

                $tipo_cita = ['id_tipo_cita' => 1];

                $datos_citas = ['id_afiliado' => $datosFormularios['id_afiliado'], 'id_medico' => $datosFormularios['id_medico'],
                    'fecha_cita' => $datosFormularios['fecha_cita'], 'id_tipo_cita' => $tipo_cita['id_tipo_cita'],
                    'id_especialidad' => $datosFormularios['id_especialidad'], 'tipo_cita' => $datosFormularios['flexRadioDefault'],
                    'motivo_consulta' => $datosFormularios['motivo_consulta'], 'id_sucursal' => $_SESSION['auth']['id_sucursal']];
                unset($datosFormularios);

                $citas_model->agregarNuevo($datos_citas);

                $id_objetos = $objetos_model->getIdobjetoAfiliados();
//                      
//                      GUARDAR A BITACORA
                $DateAndTime = date('Y-m-d H:i:s');

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion del la primera cita ', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => " se creo la  cita para el afiliado con # de expediente {$dni['dni']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsInfo'] = array('titulo' => 'Cita Creada!', 'texto' => "La Primera Cita del Afiliado fue creada exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            } else {


                $datos_citas = ['id_afiliado' => $datosFormularios['id_afiliado'], 'id_medico' => $datosFormularios['id_medico'],
                    'fecha_cita' => $datosFormularios['fecha_cita'], 'id_especialidad' => $datosFormularios['id_especialidad'],
                    'motivo_consulta' => $datosFormularios['motivo_consulta'], 'id_sucursal' => $_SESSION['auth']['id_sucursal'],
                    'tipo_cita' => $datosFormularios['flexRadioDefault']];

                $citas_model->agregarNuevo($datos_citas);

//                      GUARDAR A BITACORA

                $id_objetos = $objetos_model->getIdobjetoAfiliados();

                $DateAndTime = date('Y-m-d H:i:s');

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de cita ', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => $descripcion['descripcion'] = " se creo la  cita para el afiliado con # de expediente {$dni['dni']}"];

                $utl_bitacora->guardarBitacora($bitacora);

//                imprimir cita

                $id_cita = $citas_model->getIdReciente($datos_citas);

                $btn_imprimir = '<a class="btn btn-primary" href="' . $this->getRequest()->getBaseUrl() . '/citas/pdffichacitas/' . $id_cita . '">'
                        . '<i class="fas fa-printer"></i>  Imprimir Ficha</a>';

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita guardado!', 'texto' => "La cita fue creada exitosamente.");
                $_SESSION['mnsInfo'] = array('titulo' => 'Imprimir Cita!', 'texto' => ".<br><br>$btn_imprimir");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            }
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

    public function cambiarmedicoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

            $id_medico = $datosFormularios['id_cita'];

            unset($datosFormularios['id_cita']);

            if ($citas_model->actualizar($id_medico, $datosFormularios)) {

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Medico Actualizado!', 'texto' => "El medico asignado a la cita fue actualizado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            } else {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede actualizar el medico asignado a la cita.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function eliminarcitaAction() {
        $objeto = 'Citas';
        $permiso = 'permiso_eliminacion';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/pacientes/triaje");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $citas_model = new Citas($this->dbAdapter);
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {



            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET

            $id = $this->params()->fromRoute("id", null);

            $info_cita = $citas_model->getCitasinfo($id);

//            echo "<pre>";
//            print_r($info_cita);
//            exit;

            if ($info_cita['id_estado_cita'] != 1) {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'No se puede eliminar la cita debido a su estado.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            }

            if ($citas_model->borrar($id)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $utl_bitacora = new Bitacora($this->dbAdapter);
                $objetos_model = new Objetos($this->dbAdapter);
                $id_objetos = $objetos_model->getIdobjetoCitas();
                $DateAndTime = date('Y-m-d H:i:s');
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Eliminar Cita',
                    'descripcion' => $descripcion['descripcion'] = " Se elimino la cita para el paciente con # de expediente {$info_cita['dni']}"
                    . " y con una fecha de cita del {$info_cita['fecha_cita']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita eliminada!', 'texto' => "La cita fue eliminada exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al eliminar la cita, intentelo después.');

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            }

            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function consultaspendientesAction() {

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
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');

            //$id = $this->params()->fromRoute("id", null);
            //$datosFormularios = $this->request->getPost()->toArray();
        } else {
            //Normal GET
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $estadosc_model = new Estadoscitas($this->dbAdapter);

            $estados = $estadosc_model->getEstadoscitas();

            $vista = new ViewModel(array('estado' => $estados)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function calendarioAction() {
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

    public function gestionarcitasAction() {
        $objeto = 'Citas';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

            $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $estadosc_model = new Estadoscitas($this->dbAdapter);
        $citas_model = new Citas($this->dbAdapter);
        $medicos_model = new Medicos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {


            $objeto = 'Citas';
            $permiso = 'permiso_actualizacion';
            if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {

                $_SESSION['mnsAutoInfo'] = array('titulo' => 'Acceso Denegado !', 'texto' => "no cuenta con el permiso necesario para acceder.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            }

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id_cita'];

            unset($datosFormularios['id_cita']);

            $info_cita = $citas_model->getPorId($id);

            if ($info_cita['id_estado_cita'] == 2 || $info_cita['id_estado_cita'] == 6) {

                $_SESSION['mnsWar'] = array('titulo' => 'Imposible Actualizar!', 'texto' => " no se pueden cambiar las citas que estén precliniadas o "
                    . "completadas.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            }


            if ($datosFormularios['id_estado_cita'] != 4) {

                unset($datosFormularios['fecha_cita']);

                $citas_model->actualizar($id, $datosFormularios);
                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita actualizada!', 'texto' => "La cita el estado cambio exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            } else {

                $id_especialidad = $info_cita['id_especialidad'];
                $fecha_cita = date("Y-m-d", strtotime($info_cita['fecha_cita']));

                $fecha_mostrar = date("d-m-Y", strtotime($info_cita['fecha_cita']));

                $cant_citas = $citas_model->getCamtCitas($info_cita['id_medico'], $fecha_cita);

                //VALIDAR CANTIDAD DE CUPOS
                if ($id_especialidad == 1) {

                    if ($cant_citas['cant_citas'] >= $_SESSION['parametros']['max_cita_ginecologia']) {

                        $_SESSION['mnsWar'] = array('titulo' => 'Limite Alcansado!', 'texto' => "El ginecologo no tiene más cupos disponibles para "
                            . "la fecha {$fecha_mostrar}.");
                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
                    }
                }



                //VALIDAR CANTIDAD DE CUPOS
                if ($id_especialidad == 2) {

                    if ($cant_citas['cant_citas'] >= $_SESSION['parametros']['max_cita_psicologia']) {

                        $_SESSION['mnsWar'] = array('titulo' => 'Limite Alcansado!', 'texto' => "El psicologo no tiene más cupos disponibles para "
                            . "la fecha {$fecha_mostrar}.");
                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
                    }
                }


                //VALIDAR CANTIDAD DE CUPOS
                if ($id_especialidad == 3) {
                    if ($cant_citas['cant_citas'] >= $_SESSION['parametros']['max_cita_nutricion']) {

                        $_SESSION['mnsWar'] = array('titulo' => 'Limite Alcansado!', 'texto' => "El nutricionista no tiene más cupos disponibles para "
                            . "la fecha {$fecha_mostrar}.");
                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
                    }
                }

                if ($id_especialidad == 4) {

                    if ($cant_citas['cant_citas'] >= $_SESSION['parametros']['max_cita_medico_general']) {

                        $_SESSION['mnsWar'] = array('titulo' => 'Limite Alcansado!', 'texto' => "El médico no tiene más cupos disponibles para "
                            . "la fecha {$fecha_mostrar}.");
                        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
                    }
                }

                $citas_model->actualizar($id, $datosFormularios);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita actualizada!', 'texto' => "El estado de la cita cambio exitosamente.");

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/gestionarcitas");
            }
        } else {
            //Normal GET

            $medicos = $medicos_model->getMedicosGeneral();

            $estados = $estadosc_model->getEstadoscitas();

            $vista = new ViewModel(array('estado' => $estados, 'medicos' => $medicos)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function getnombremedicoajaAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
//
//        $utilidad_fechas = new Fechas();


        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $personas_model = new Personas($this->dbAdapter);
            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['buscar'];

//            $fecha_db = $utilidad_fechas->convertir_datetime_local_FechaToDB($id); //Devuelve 2022-03-11
//
//            echo $fecha_db;
//            exit;

            $id_sucursal = $_SESSION['auth']['id_sucursal'];

            $medico = $personas_model->getMedicoPorespe($id, $id_sucursal);

            $options = "<option value=''>Seleccione un profecional responsable</option>";
            if (!$medico) {
                $options .= "<option value=''>Árae sin profecional responsable</option>";
            }
            foreach ($medico as $v) {
                $options .= "<option value='" . $v['id_medico'] . "'> " . $v['nombre_completo'] . "</option>";
            }

            echo $options;
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

//    NICIO PDF VOUCHER CITA MEDICA
    public function pdffichacitasAction() {
// if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
// return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
// }
        if ($this->getRequest()->isXmlHttpRequest()) {
//para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            
        } else {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);
            $id_citas = $this->params()->fromRoute("id", null);

//RECUPERAR LA INFORMACION DEL REPORTE
            $info_citas = $citas_model->getCitasinfo($id_citas);

            if (!$info_citas) {
                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita Guardada!', 'texto' => "Cita no encontrado.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas");
            }
            $expediente_afiliado = $info_citas['dni'];
            $nombre_completo = $info_citas['nombre'];
            $tipo_afiliado = $info_citas['tipo_afi'];
            $codigo_afiliacio = $info_citas['id_afiliado'];
            $especialidad = $info_citas['especialidad'];
            $medico = $info_citas['nombre_medico'];
            $tipo_consulta = $info_citas['tipo_consulta'];
            $fecha_hora = $info_citas['fecha_cita'];
            $motivo_consulta = $info_citas['motivo_consulta'];

// Incluir el MAIN de la libreria TCPDF Confirmar el path de tcpdf_include.php.
            require_once(dirname(__FILE__) . '/../Extras/Pdf/tcpdf_include.php');

// create new PDF document PDF_PAGE_ORIENTATION L = LandScape P = Portrain Normal
            $pdf = new \TCPDF('P', \PDF_UNIT, 'letter', true, 'UTF-8', false);

// set información del PDF
            $pdf->SetCreator('FHEP');
            $pdf->SetAuthor('Sistema Recursos Humanos');
            $pdf->SetTitle('Voucher de Consulta');
            $pdf->SetSubject('ficha');
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
                    . "<b>Comprobante de Consulta</b>"
                    . "</div>";

            $contenidoHTML = "<br><div style=\"text-align: justify; font-family: lucida consola; font-size: 14px; line-height: 25px;\">"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Número de Expediente:<b> $expediente_afiliado </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Nombre Completo:<b> $nombre_completo </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Tipo de Afiliado: <b> $tipo_afiliado </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Código de Afiliación:<b> $codigo_afiliacio </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Especialidad:<b> $especialidad </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Médico: <b> $medico </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Tipo de Consulta:<b> $tipo_consulta </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Fecha y Hora:<b> $fecha_hora </b>"
                    . "</div>"
                    . "<div style=\"text-align: justify; font-family:lucida console,monospace; font-size: 14px; line-height: 12px;\">"
                    . "Motivo de Consulta:<b> $motivo_consulta </b>"
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
            $pdf->Output("FICHA AFILIADO " . '.pdf', 'I');
            exit;
        }
    }

    public function exportarpdfgenerlcitasAction() {
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
            //AQUI EL REPORTE
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);

//        $id_suscursal = $_SESSION['auth']['id_sucursal'];
//        $fecha_actual = date("Y-m-d%");

            $citas = $citas_model->getTblHistorico();

//               echo "<pre>";
//           print_r($citas);
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
            $pdf->SetTitle('Rerporte de Citas');
            $pdf->SetSubject('Citas');
            $pdf->SetKeywords('Citas');

// Configurar los datos de la imagen 
            $ancho_logo = 14;
            $ancho_logo_derecho = 15;
            $nombreLogo = 'logo.png';
            $nombreLogoderecho = '_blank.png';

            $titulo_encabezado = "FUNDACIÓN HOSPITAL DE EMERGENCIA POLICIAL";
            $subtitulo = "\n\nReporte General de Citas";
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
            $header = ['N°', 'Nombre', 'DNI', 'Especialidad', 'Medico', 'Estado', 'Fecha'];
            $w = array($m * .02, $m * .13, $m * .12, $m * .15, $m * .14, $m * .20, $m * .24);

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

            foreach ($citas as $fila) {

                //Corregir textos
//Nombre', 'DNI', 'Especialidad', 'Medico','Estado', 'Fecha'
                $nombre = $utilidad_texto->fixTexto($fila['nombre_completo']);
                $identidad = $utilidad_texto->fixTexto($fila['dni']);
                $especialidad = $utilidad_texto->fixTexto($fila['especialidad']);
                $medico = $utilidad_texto->fixTexto($fila['nombre_medico']);
                $estado = $utilidad_texto->fixTexto($fila['estado']);
                $fecha = $utilidad_texto->fixTexto($fila['fecha_cita']);

                //Insertar la información el la celda correspondiente
                $pdf->Cell($w[0], 4.2, $cont, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[1], 4.2, $nombre, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[2], 4.2, $identidad, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[3], 4.2, $especialidad, 'LR', 0, 'C', $fill);
                $pdf->Cell($w[4], 4.2, $medico, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[5], 4.2, $estado, 'LR', 0, 'L', $fill);
                $pdf->Cell($w[6], 4.2, $fecha, 'LR', 0, 'L', $fill);

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
            $pdf->Output("GENERAL DE CITAS" . '.pdf', 'I');
            exit;
        }
    }

    public function calgetcitasAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $citas_model = new Citas($this->dbAdapter);

//        $id_suscursal = $_SESSION['auth']['id_sucursal'];
//        $fecha_actual = date("Y-m-d%");

        $id_medico = $_SESSION['auth']['id_medico'];

        $citas = $citas_model->getCitasCalendario($id_medico);

        foreach ($citas as $f => $valor) {
            //$citas[$f]['color'] = "#fc922f";
            $citas[$f]['color'] = "#00AAD4";

            //COLOR AZUL
            if ($valor['estado'] == "Precliniada") {
                $citas[$f]['color'] = "#ecf007";

                // COLOR NARANJA
            } else if ($valor['estado'] == "Nueva") {
                $citas[$f]['color'] = "#d67b04";

                //COLOR VERDE 
            } else if ($valor['estado'] == "Completada") {
                $citas[$f]['color'] = "#00AA00";

                //COLOR ROJO
            } else if ($valor['estado'] == "Cancelada") {
                $citas[$f]['color'] = "#d6041c";

                //COLOR ROSA
            } else if ($valor['estado'] == "Reprogramada") {
                $citas[$f]['color'] = "#d10270";

                //COLOR MORADO
            } else {
                $citas[$f]['color'] = "#5800aa";
            }
        }
        echo json_encode($citas);
        exit;
    }

    public function caleneditarcitaAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $estadosc_model = new Estadoscitas($this->dbAdapter);
        $citas_model = new Citas($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            $id = $datosFormularios['id_cita'];

            unset($datosFormularios['id_cita']);

            $info_cita = $citas_model->getPorId($id);

            if ($info_cita['id_estado_cita'] == 2 || $info_cita['id_estado_cita'] == 6) {

                $_SESSION['mnsWar'] = array('titulo' => 'Imposible Actualizar!', 'texto' => " no se pueden cambiar las citas que estén precliniadas o "
                    . "completadas.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/consultaspendientes");
            }


            if ($datosFormularios['id_estado_cita'] != 4) {

                unset($datosFormularios['fecha_cita']);

                $citas_model->actualizar($id, $datosFormularios);
                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita actualizada!', 'texto' => "La cita el estado cambio exitosamente.");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/consultaspendientes");
            } else {

                $citas_model->actualizar($id, $datosFormularios);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita actualizada!', 'texto' => "El estado de la cita cambio exitosamente.");

                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/consultaspendientes");
            }
        } else {
            //Normal GET



            $estados = $estadosc_model->getEstadoscitas();

            $vista = new ViewModel(array('estado' => $estados)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

    public function calennuevacitaAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }
        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
            $citas_model = new Citas($this->dbAdapter);
            $afiliado_model = new Afiliados($this->dbAdapter);
            $objetos_model = new Objetos($this->dbAdapter);
            $medicos_model = new Medicos($this->dbAdapter);

            $datosFormularios = $this->request->getPost()->toArray();

            $utl_bitacora = new Bitacora($this->dbAdapter);

            $dni = $datosFormularios['dni'];

            $id_medico = $_SESSION['auth']['id_medico'];

            $info_medico = $medicos_model->getPorId($id_medico);

            $id_especialidad = $info_medico['id_especialidad'];

            unset($datosFormularios['dni']);

             
            
            if ($id_medico == null) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Debe ser medico para agendar citas.');
                 return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/consultaspendientes");
            }
            
            
            $fecha_cita = date("Y-m-d", strtotime($datosFormularios['fecha_cita']));
             
             $DateAndTime = date('Y-m-d');
            
            if ($fecha_cita < $DateAndTime) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Fecha de cita inválida.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/consultaspendientes");
            }
            
            
            $info = $citas_model->getTipocita($datosFormularios['id_afiliado']);
            if (!$info) {

                $tipo_cita = ['id_tipo_cita' => 1];
                
                $datos_citas = ['id_afiliado' => $datosFormularios['id_afiliado'], 'id_medico' => $id_medico,
                    'fecha_cita' => $datosFormularios['fecha_cita'], 'id_tipo_cita' => $tipo_cita['id_tipo_cita'],
                    'id_especialidad' => $id_especialidad, 'tipo_cita' => $datosFormularios['flexRadioDefault'],
                    'motivo_consulta' => $datosFormularios['motivo_consulta'], 'id_sucursal' => $_SESSION['auth']['id_sucursal']];
                
                unset($datosFormularios);

                $citas_model->agregarNuevo($datos_citas);

                $id_objetos = $objetos_model->getIdobjetoAfiliados();

                $DateAndTime = date('Y-m-d H:i:s');

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion del la primera cita ', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => " se creo la  cita para el afiliado con # de expediente {$dni['dni']}"];

                $utl_bitacora->guardarBitacora($bitacora);

                // imprimir cita

                $id_cita = $citas_model->getIdReciente($datos_citas);

                $btn_imprimir = '<a class="btn btn-primary" href="' . $this->getRequest()->getBaseUrl() . '/citas/pdffichacitas/' . $id_cita . '">'
                        . '<i class="fas fa-printer"></i>  Imprimir Ficha</a>';

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita guardado!', 'texto' => "La cita fue creada exitosamente.");
                $_SESSION['mnsInfo'] = array('titulo' => 'Imprimir Cita!', 'texto' => ".<br><br>$btn_imprimir");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/consultaspendientes");
            } else {
                
                
                $datos_citas = ['id_afiliado' => $datosFormularios['id_afiliado'], 'id_medico' => $id_medico,
                    'fecha_cita' => $datosFormularios['fecha_cita'], 'id_especialidad' => $id_especialidad, 
                    'tipo_cita' => $datosFormularios['flexRadioDefault'],
                    'motivo_consulta' => $datosFormularios['motivo_consulta'], 'id_sucursal' => $_SESSION['auth']['id_sucursal']];

                unset($datosFormularios);

                $citas_model->agregarNuevo($datos_citas);

//                guardar a Bitacoras
                $id_objetos = $objetos_model->getIdobjetoAfiliados();

                $DateAndTime = date('Y-m-d H:i:s');

                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime,
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Creacion de cita ', 'id_objeto' => $id_objetos['id'],
                    'descripcion' => $descripcion['descripcion'] = " se creo la  cita para el afiliado con # de expediente {$dni['dni']}"];

                $utl_bitacora->guardarBitacora($bitacora);

//                imprimir cita

                $id_cita = $citas_model->getIdReciente($datos_citas);

                $btn_imprimir = '<a class="btn btn-primary" href="' . $this->getRequest()->getBaseUrl() . '/citas/pdffichacitas/' . $id_cita . '">'
                        . '<i class="fas fa-printer"></i>  Imprimir Ficha</a>';

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Cita guardado!', 'texto' => "La cita fue creada exitosamente.");
                $_SESSION['mnsInfo'] = array('titulo' => 'Imprimir Cita!', 'texto' => ".<br><br>$btn_imprimir");
                //Redireccionar
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/citas/consultaspendientes");
            }
        } else {
            //Normal GET
            $vista = new ViewModel([]); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

}
