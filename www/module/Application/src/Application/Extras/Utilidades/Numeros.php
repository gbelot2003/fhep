<?php

/*

 * @Nombre    : Monedas
 * @Author    : Erick Rodriguez
 * @Copyright : Erick Rodriguez
 * @Creado el : 05-nov-2019, 09:50:05 AM
 */

/**
 * Description of Monedas
 *
 * @author Erick
 */

namespace Application\Extras\Utilidades;





class Numeros {
    //put your code here
    
    
    /**
     * Convierte una cantidad en números a letras en formato de lempiras.
     * 
     * @param mixto $numero Número a convertir.
     * @return string Número convertido en letras en lempiras.<br>
     * eje. 102.05 -> ciento dos lempiras con 05 centavos.
     */
    public function numerosEnLetrasMoneda($numero, $moneda = "Lempiras") {
        $numero = \str_replace(",", "", $numero);
        $numProvisorio = \number_format($numero, 2, '.', ',');

        $partesNum1 = \explode('.', $numProvisorio);
        $valor = $partesNum1[0];
        $valorTxt = "";
        $partesValor = \explode(',', $valor);
        $totPartesValor = count($partesValor);

        $orden = $totPartesValor;
        for ($i = 0; $i < $totPartesValor; $i++) {
            $valorTxt .= " " . $this->numeros2Letras($partesValor[$i], $orden);
            $orden--;
        }


        $centavos = end($partesNum1);
        $cntTxt = $this->numeros2Letras($centavos, 0);

        if ($centavos != "00") {
            $moneda .= " con";
        } else {
            $cntTxt = "exactos";
        }


        $rp = "$valorTxt $moneda $cntTxt ";

        $r = \trim($rp);

        $espacios = array("  ", "   ", "    ", "     ", "      ");
        $espacio = array(" ", " ", " ", " ", " ");
        $r = \str_replace($espacios, $espacio, $r);

        return $r;
    }

    
    /**
     * Convierte una cantidad en números a letras.
     * 
     * @param mixto $numero Número a convertir.
     * @return string Número convertido en letras.<br>
     * eje. 102.05 -> ciento dos con 05.
     
    public function numerosEnLetrasGeneral($numero) {
        $numProvisorio = \number_format($numero, 0, '.', ',');


        $partesValor = explode(',', $numProvisorio);
        $totPartesValor = count($partesValor);

        $orden = $totPartesValor;
        $valorTxt = "";
        for ($i = 0; $i < $totPartesValor; $i++) {
            $valorTxt .= " " . $this->numeros2Letras($partesValor[$i], $orden);
            $orden--;
        }

        $valorTxt = \trim($valorTxt);
        $espacios = array("  ", "   ", "    ", "     ");
        $espacio = array(" ", " ", " ", " ");
        $r = \str_replace($espacios, $espacio, $valorTxt);

        return "$valorTxt";
    }*/

    /**
     * Convierte una cantidad en números a letras sin decimales para días o años.
     * 
     * @param mixto $numero Número a convertir.
     * @return string Número convertido en letras.<br>
     * eje. 102.05 -> ciento dos.
     */
    public function numerosEnLetrasFecha($numero) {
        $numProvisorio = \number_format($numero, 0, '.', ',');

        $partesValor = explode(',', $numProvisorio);
        $totPartesValor = \count($partesValor);

        $orden = $totPartesValor;
        $valorTxt = "";
        for ($i = 0; $i < $totPartesValor; $i++) {
            $valorTxt .= " " . $this->numeros2Letras($partesValor[$i], $orden);
            $orden--;
        }

        $valorTxt = \trim($valorTxt);
        $espacios = array("  ", "   ", "    ", "     ");
        $espacio = array(" ", " ", " ", " ");
        $r = \str_replace($espacios, $espacio, $valorTxt);

        return "$r";
    }

    private function numeros2Letras($numero, $orden) {
        if (strlen($numero) == 0) {
            return;
        } else if (strlen($numero) == 1) {
            $numero = "00$numero";
        } else if (strlen($numero) == 2) {
            $numero = "0$numero";
        }


        $n1 = \substr($numero, 0, 1);
        $n2 = \substr($numero, 1, 1);
        $n3 = \substr($numero, 2, 1);

        $txtN1 = array('0' => '', '1' => 'ciento', '2' => 'doscientos', '3' => 'trescientos', '4' => 'cuatrocientos',
            '5' => 'quinientos', '6' => 'seiscientos', '7' => 'setecientos', '8' => 'ochocientos', '9' => 'novecientos');

        if ($n1 == 1 && $n2 == 0 && $n3 == 0) {
            $txtN1['1'] = 'cien';
        }

        $txtN2 = [];
        $txtN3 = [];

        if ($orden == 1) {
            $txtN3 = array('0' => '', '1' => 'uno', '2' => 'dos', '3' => 'tres', '4' => 'cuatro'
                , '5' => 'cinco', '6' => 'seis', '7' => 'siete', '8' => 'ocho', '9' => 'nueve');
        } else {
            $txtN3 = array('0' => '', '1' => 'un', '2' => 'dos', '3' => 'tres', '4' => 'cuatro'
                , '5' => 'cinco', '6' => 'seis', '7' => 'siete', '8' => 'ocho', '9' => 'nueve');
        }

        if ($n3 == "0") {
            $txtN2 = array('0' => '' . $txtN3[$n3], '1' => 'diez', '2' => 'veinte', '3' => 'treinta', '4' => 'cuarenta',
                '5' => 'cincuenta', '6' => 'sesenta', '7' => 'setenta', '8' => 'ochenta', '9' => 'noventa');
        } else if ($n3 == "1") {
            $txtN2 = array('0' => '' . $txtN3[$n3], '1' => 'once', '2' => 'veinti' . $txtN3[$n3], '3' => "treinta y " . $txtN3[$n3], '4' => 'cuarenta y ' . $txtN3[$n3],
                '5' => 'cincuenta y ' . $txtN3[$n3], '6' => 'sesenta y ' . $txtN3[$n3], '7' => 'setenta y ' . $txtN3[$n3], '8' => 'ochenta y ' . $txtN3[$n3], '9' => 'noventa y ' . $txtN3[$n3]);
        } else if ($n3 == "2") {
            $txtN2 = array('0' => '' . $txtN3[$n3], '1' => 'doce', '2' => 'veintidós', '3' => "treinta y " . $txtN3[$n3], '4' => 'cuarenta y' . $txtN3[$n3],
                '5' => 'cincuenta y ' . $txtN3[$n3], '6' => 'sesenta y ' . $txtN3[$n3], '7' => 'setenta y ' . $txtN3[$n3], '8' => 'ochenta y ' . $txtN3[$n3], '9' => 'noventa y ' . $txtN3[$n3]);
        } else if ($n3 == "3") {
            $txtN2 = array('0' => '' . $txtN3[$n3], '1' => 'trece', '2' => 'veintitrés', '3' => "treinta y " . $txtN3[$n3], '4' => 'cuarenta y' . $txtN3[$n3],
                '5' => 'cincuenta y ' . $txtN3[$n3], '6' => 'sesenta y ' . $txtN3[$n3], '7' => 'setenta y ' . $txtN3[$n3], '8' => 'ochenta y ' . $txtN3[$n3], '9' => 'noventa y ' . $txtN3[$n3]);
        } else if ($n3 == "4") {
            $txtN2 = array('0' => '' . $txtN3[$n3], '1' => 'catorce', '2' => 'veinti' . $txtN3[$n3], '3' => "treinta y " . $txtN3[$n3], '4' => 'cuarenta y ' . $txtN3[$n3],
                '5' => 'cincuenta y ' . $txtN3[$n3], '6' => 'sesenta y ' . $txtN3[$n3], '7' => 'setenta y ' . $txtN3[$n3], '8' => 'ochenta y ' . $txtN3[$n3], '9' => 'noventa y ' . $txtN3[$n3]);
        } else if ($n3 == "5") {
            $txtN2 = array('0' => '' . $txtN3[$n3], '1' => 'quince', '2' => 'veinti' . $txtN3[$n3], '3' => "treinta y " . $txtN3[$n3], '4' => 'cuarenta y ' . $txtN3[$n3],
                '5' => 'cincuenta y ' . $txtN3[$n3], '6' => 'sesenta y ' . $txtN3[$n3], '7' => 'setenta y ' . $txtN3[$n3], '8' => 'ochenta y ' . $txtN3[$n3], '9' => 'noventa y ' . $txtN3[$n3]);
        } else if ($n3 == "6") {
            $txtN2 = array('0' => '' . $txtN3[$n3], '1' => 'dieciséis', '2' => 'veintiséis', '3' => "treinta y " . $txtN3[$n3], '4' => 'cuarenta y ' . $txtN3[$n3],
                '5' => 'cincuenta y ' . $txtN3[$n3], '6' => 'sesenta y ' . $txtN3[$n3], '7' => 'setenta y ' . $txtN3[$n3], '8' => 'ochenta y ' . $txtN3[$n3], '9' => 'noventa y ' . $txtN3[$n3]);
        } else {
            $txtN2 = array('0' => '' . $txtN3[$n3], '1' => 'dieci' . $txtN3[$n3], '2' => 'veinti' . $txtN3[$n3], '3' => "treinta y " . $txtN3[$n3], '4' => 'cuarenta y ' . $txtN3[$n3],
                '5' => 'cincuenta y ' . $txtN3[$n3], '6' => 'sesenta y ' . $txtN3[$n3], '7' => 'setenta y ' . $txtN3[$n3], '8' => 'ochenta y ' . $txtN3[$n3], '9' => 'noventa y ' . $txtN3[$n3]);
        }
        $txtOrden = "";
        if ($orden == "0") {
            if ($numero == "0") {
                $txtOrden = "exactos";
            } else {
                $txtOrden = "centavos";
            }
        } else if ($orden == "1") {
            
        } else if ($orden == "2") {
            $txtOrden = "mil";
        } else if ($orden == "3") {
            if ($n1 == "0" && $n2 == "0" && $n3 == "1") {
                $txtOrden = "millon";
            } else {
                $txtOrden = "millones";
            }
        } else if ($orden == "4") {
            $txtOrden = "mil";
        }

        $n3Txt = "";
//        if ($n1 == "0" && $n2 == "0") {
//            $n3Txt = $txtN3[$n3];
//        }
        $n1Txt = $txtN1[$n1];
        $n2Txt = $txtN2[$n2];
//        echo "$numero -> n1 $n1Txt n2 $n2Txt n3 $n3Txt txtOrden $txtOrden, n1 $n1, n2 $n2, n3 $n3, orden $orden <br>";



        return "$n1Txt $n2Txt $n3Txt $txtOrden";
    }

    
    
}
