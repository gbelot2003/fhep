<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Application\Model\Afiliados;
use Application\Model\Medicos;
use Application\Model\Citas;
use Application\Model\Parametros;

class IndexController extends AbstractActionController {

    public function indexAction() {

//        if (isset($_SESSION['inicio_1'])) {
//
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/usuarios/primerlogin");
//        }

        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $afiliados_model = new Afiliados($this->dbAdapter);
        $num_afiliados = $afiliados_model->getNumAfiliados();

        $medicos_model = new Medicos($this->dbAdapter);
        $num_medicos = $medicos_model->getNumMedicos();

        $fecha_actual = date("Y-m-d%");

        $citas_model = new Citas($this->dbAdapter);
        $num_citas = $citas_model->getNumCitas($fecha_actual);

        $beneficiarios_model = new Afiliados($this->dbAdapter);
        $num_beneficiarios = $beneficiarios_model->getNumBeneficiarios();

        // recuperar parametros
        $parametros_model = new Parametros($this->dbAdapter);
        $parametros_info = $parametros_model->getAll();

        $parametros = [];

        foreach ($parametros_info as $v) {
            $parametros[$v['parametro']] = $v['valor'];
        }


        $_SESSION['parametros'] = $parametros;

        $_SESSION['parametros']['min_len_pass'];
        $_SESSION['parametros']['max_len_pass'];
        
        $vista = new ViewModel(array('num_bene' => $num_beneficiarios, 'num_citas' => $num_citas, 'num_medi' => $num_medicos,
            'num_afi' => $num_afiliados, 'min' => $_SESSION['parametros']['min_len_pass'],
            'max' => $_SESSION['parametros']['max_len_pass'])); //Instancia de la vista
        $this->layout(); //Parametro pasado al layout Titulo de la página
        return $vista;
    }

}
