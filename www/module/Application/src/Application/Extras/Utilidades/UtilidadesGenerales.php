<?php

/*

 * @Nombre    : Generales
 * @Author    : Erick Rodriguez
 * @Copyright : Erick Rodriguez
 * @Creado el : 28-nov-2019, 08:46:32 AM
 */

namespace Application\Extras\Utilidades;

use Zend\Db\Adapter\Adapter;
use Application\Model\Empleados;
use Application\Model\Cnfgral;
use Application\Model\Asuetosvacaciones;
use Application\Model\Feriados;

/**
 * Description of Generales
 *
 * @author Erick
 */
class UtilidadesGenerales {

    private $dbAdapter;

    public function __construct(Adapter $adapter) {
        $this->dbAdapter = $adapter;
    }

    /**
     * Recupera la información del alcalde actual, nombre completo, identidad
     */
    public function getInfoAlcaldeActual() {
        $empleados_model = new Empleados($this->dbAdapter);
        return $empleados_model->getInfoAlcalde();
    }
    
    
    /**
     * Recupera el nombre del Presidente del SITRAMUDIC
     */
    public function getNombrePresidenteSindicato() {
        $cnf_general_model = new Cnfgral($this->dbAdapter);
        
        $cnf_gral = $cnf_general_model->getAll();
        $nombre = "";
        
        foreach ($cnf_gral as $v) {
            $nombre = $v['nombre_presidente_sindicato'];
        }
        return $nombre;
    }
    
    public function addCerosCodigo($cod) {

        $c = "  ERROR<br>  ";

        $cod = (string) $cod;


        if (\strlen($cod) == 1) {
            $c = "00000" . $cod;
        } else if (strlen($cod) == 2) {
            $c = "0000" . $cod;
        } else if (strlen($cod) == 3) {
            $c = "000" . $cod;
        } else if (strlen($cod) == 4) {
            $c = "00" . $cod;
        } else if (strlen($cod) == 5) {
            $c = "0" . $cod;
        } else if (strlen($cod) == 6) {
            $c = $cod;
        } else {
            $c = $cod;
        }


        return $c;
    }
    
    public function getMicroTiempo() {
        list($usec, $sec) = \explode(" ", microtime());
        return ((float) $usec + (float) $sec);
    }
    
    public function timeDiaHabilSiguiente($time_fecha) {

        $asuetosVacModel = new Asuetosvacaciones($this->dbAdapter);
        $feriadosModel = new Feriados($this->dbAdapter);

        $time_f = $time_fecha + 86400;
        $habil = false;

        while (!$habil) {
            $diaBusqueda = \date('w', $time_f);
            $fechaEvaluar = \date('Y-m-d', $time_f);
//Ver si es feriado
            $esFeriado = $feriadosModel->existeFechaFeriadoEfectiva($fechaEvaluar);

//ver si es asueto
            $asueto = $asuetosVacModel->getAsuetoEnFecha($fechaEvaluar);

            if ($diaBusqueda == 6 || $diaBusqueda == 0 || $esFeriado || $asueto) {
                $time_f += 86400;
            } else {
                $habil = true;
            }
        }

        return $time_f;
    }

    public function timeDiaHabilAnterior($time_fecha) {

        $asuetosVacModel = new Asuetosvacaciones($this->dbAdapter);
        $feriadosModel = new Feriados($this->dbAdapter);

        $time_f = $time_fecha - 86400;
        $habil = false;

        while (!$habil) {
            $diaBusqueda = \date('w', $time_f);
            $fechaEvaluar = \date('Y-m-d', $time_f);
//Ver si es feriado
            $esFeriado = $feriadosModel->existeFechaFeriadoEfectiva($fechaEvaluar);

//ver si es asueto
            $asueto = $asuetosVacModel->getAsuetoEnFecha($fechaEvaluar);

            if ($diaBusqueda == 6 || $diaBusqueda == 0 || $esFeriado || $asueto) {
                $time_f -= 86400;
            } else {
                $habil = true;
            }
        }

        return $time_f;
    }

}
