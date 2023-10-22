<?php

/*

 * @Nombre    : CorreosController
 * @Author    : Erick R. Rodríguez
 * @Copyright : Erick R. Rodríguez
 * @Email     : eramsses@gmail.com
 * @Creado el : 02-abr-2022, 05:52:16 PM
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Correos;
use Application\Model\Objetos;
use Application\Model\Bitacoras;

class CorreosController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function indexAction() {
        if (!isset($_SESSION['auth'])) {
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

    public function correosAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $correos_model = new Correos($this->dbAdapter);

        if ($this->getRequest()->isXmlHttpRequest()) {
            //para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {

            $datosFormularios = $this->request->getPost()->toArray();

            $id = ['id' => $datosFormularios['id']];
            
            
           

            if ($correos_model->actualizar($id, $datosFormularios)) {

                //                     GUARDAR A BITACORA

                $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
                $objetos_model = new Objetos($this->dbAdapter);
                $bitacoras_model = new Bitacoras($this->dbAdapter);
                $id_objetos = $objetos_model->getIdObjtoUsuarios();
                $DateAndTime = date('Y-m-d h:i:s a', time());
                $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
                    'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Editar Corro',
                    'descripcion' => $descripcion['descripcion'] = " Se edito la informacion del conrreo {$datosFormularios['correo']}"];

                $bitacoras_model->agregarNuevo($bitacora);

                $_SESSION['mnsAutoOK'] = array('titulo' => 'Corro Actualizado!', 'texto' => "La informacion se cambio exitosamente.");
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/correos");
            } else {

                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrió un error al actualizar la informacion.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/correos");
            }
        } else {
            //Normal GET

            $id_correo = 1;

            $info_correo = $correos_model->getPorId($id_correo);

            $vista = new ViewModel(array('info' => $info_correo)); //Instancia de la vista
            $this->layout(); //Parametro pasado al layout Titulo de la página
            return $vista;
        }
    }

}
