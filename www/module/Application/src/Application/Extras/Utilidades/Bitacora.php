<?php

namespace Application\Extras\Utilidades;

use Zend\Db\Adapter\Adapter;
use Application\Model\Bitacoras;

class Bitacora {

    private $dbAdapter;
    private $salt = "The witcher 3";

    public function __construct(Adapter $adapter) {
        $this->dbAdapter = $adapter;
    }

    public function guardarBitacora($datos) {
        $bitacoras_model = new Bitacoras($this->dbAdapter);

        $hay_registros = $bitacoras_model->getAll();
        $hash_anterior = "";
        if ($hay_registros) {
            $hash_anterior = $bitacoras_model->getUltimoHash();
        }
        $datos_hash = $datos['id_usuario'] . $datos['Fecha'] . $datos['usuario'] . $datos['Accion'] . $datos['id_objeto'] . $datos['descripcion'] . $hash_anterior;

        $hash_nuevo = \hash('sha256', $datos_hash . $this->salt);

        $datos['hash_data'] = $hash_nuevo;

//        echo "$datos_hash<br><pre>";
//        print_r($datos);
//        exit;
        $bitacoras_model->agregarNuevo($datos);
    }

    public function validarIntegridad() {

        $bitacoras_model = new Bitacoras($this->dbAdapter);

        $filas = $bitacoras_model->getAll();
        $c = 0;
        $hash_anterior = "";
        foreach ($filas as $datos) {

            if ($c == 0) {
                $hash_anterior = "";
            }

            $datos_hash = $datos['id_usuario'] . $datos['Fecha'] . $datos['usuario'] . $datos['Accion'] . $datos['id_objeto'] . $datos['descripcion'] . $hash_anterior;

            $hash_creado = \hash('sha256', $datos_hash . $this->salt);
            $hash_bd = $datos['hash_data'];

            if ($hash_creado != $hash_bd) {
                $r['resultado'] = "error";
                $r['fila'] = $datos;
                return $r;
            } else {
                $hash_anterior = $hash_bd;
            }



            $c++;
        }

        $r['resultado'] = "ok";

        return $r;
    }

}
