<?php

/*

 * @Nombre    : ReportesController
 * @Author    : Bessy Castillo
 * @Copyright : Bessy Castillo
 * @Email     : bcastillor90@gmail.com
 * @Creado el : 28-mar-2022, 09:26:10 PM
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/* Aquí los Modelos */
use Application\Model\Bitacoras;
use Application\Model\Citas;
use Application\Model\Afiliados;
use Application\Model\Objetos;
use Application\Extras\Utilidades\Bitacora;

class ReportesController extends AbstractActionController {

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function indexAction() {
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

    public function reportesAction() {
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

    public function gettablarepafiliadosAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }


        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $afiliados_model = new Afiliados($this->dbAdapter);
        

        $ff = $this->params()->fromRoute("ff", null);
        $fi = $this->params()->fromRoute("fi", null);
        

        $afiliados = $afiliados_model->getTblReporteAfiliados($fi, $ff);

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('expediente', 'Codigo de Afiliado', 'string', NULL, false);
        $grid->addColumn('nombre_completo', 'Nombre Completo', 'varchar', NULL, false); //ocupo nombre completo
        $grid->addColumn('tipo_de_afiliado', 'Tipo de Afiliado', 'varchar', NULL, false);
        $grid->addColumn('fecha', 'Fecha De Afiliacion', 'date', NULL, false);
        $grid->renderXML($afiliados);
        exit;
    }

    public function gettablarepcitasAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }


        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $citas_model = new Citas($this->dbAdapter);

        $ff = $this->params()->fromRoute("ff", null);
        $fi = $this->params()->fromRoute("fi", null);

        $citas = $citas_model->getTblReporteCitas($fi, $ff);

        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('dni', 'N° Expediente', 'string', NULL, false);
        $grid->addColumn('nombre_completo', 'Nombre Completo', 'string', NULL, false);
        $grid->addColumn('motivo_consulta', 'Motivo consulta', 'string', NULL, false);
        $grid->addColumn('nombre_medico', 'Medico', 'string', NULL, false);
        $grid->addColumn('especialidad', 'especialidad', 'string', NULL, false);
        $grid->addColumn('estado', 'Estado Cita', 'string', NULL, false);
        $grid->addColumn('fecha_cita', 'Fecha de consulta', 'date', NULL, false);
        $grid->renderXML($citas);
        exit;
    }

    public function gettablavaciaAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['nombre_permiso'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        $bitacoras = [];
        // Incluir el MAIN de la libreria
        require_once(dirname(__FILE__) . '/../Extras/EditableGrid/EditableGrid.php');

        // create a new EditableGrid object
        $grid = new \EditableGrid();
        $grid->addColumn('', 'SELECCIONE UN TIPO DE REPORTE', 'string', NULL, false);
        $grid->renderXML($bitacoras);
        exit;
    }

}
