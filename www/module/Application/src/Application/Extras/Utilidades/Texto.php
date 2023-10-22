<?php
namespace Application\Extras\Utilidades;

/*

 * @Nombre    : Texto
 * @Author    : Erick Rodriguez
 * @Copyright : Erick Rodriguez
 * @Creado el : 21-oct-2021, 11:28:48 AM
 */
class Texto {

    /**
     * Convierte los caracteres latinos para su correcta presentación
     * @param String $texto
     * @return String
     */
    public function fixTexto($texto) {
        $t = \mb_convert_encoding($texto, "UTF-8", \mb_detect_encoding($texto, "UTF-8, ISO-8859-1, ISO-8859-15", true));
        return $t;
    }

}
