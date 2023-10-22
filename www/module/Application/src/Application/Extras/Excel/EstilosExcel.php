<?php

/*

 * @Nombre    : EstilosExcel
 * @Author    : Erick Rodriguez
 * @Copyright : Erick Rodriguez
 * @Creado el : 13-sep-2019, 09:15:09 AM
 */

/**
 * Description of EstilosExcel
 *
 * @author Erick R. Rodríguez
 */

namespace Application\Extras\Excel;

class EstilosExcel {

    //put your code here
    private $font;
    private $tipo;

    /**
     * En el constructor se define el tipo de letra y el esquema de colores a usar.<br>
     * Por defecto el font es Arial y sin colores
     * @param string $font -Verdana<br>
     * -Arial<br>
     * -Courier New<br>
     * -Verdana
     * 
     * @param string $tipo -AC (Acuerdo)<br>
     * -CH (Contrato por hora)<br>
     * -PRE (Planilla preliminar de acuerdo)
     */
    public function __construct($font = "Arial", $tipo = "NINGUNO") {
        $this->font = $font;
        $this->tipo = $tipo;
    }

    /**
     * Devuelve el array con el estilo para ser aplicado en excel.
     * 
     * @param string $estilo -encabezados (Encabezado o título del documento)<br>
     * -tituloColumnas (Encabezados de columnas)<br>
     * -informacionPar (Color aplicado a las filas par)<br>
     * -informacionImpar (Color aplicado a las filas impar)<br>
     * -totales (Toles números)<br>
     * -totalesTxt(Toles texto)<br>
     * -negritaIzquierdaSinFondo<br>
     * -negritaDerechaSinFondo<br>
     * -firmas<br>
     * -sinRegistros (Usado cuando no hay registros para mostrar)
     * @return array
     */
    public function getEstiloExcel($estilo, $wrapLineData = false) {
        $blanco = "FFFFFF";
        $negro = "000000";

        $colorClaroPar = "FFFFFF";
        $colorObscuroTit = "FFFFFF";
        $colorBordeTit = "000000";
        $colorBordeCelda = "000000";
        $textoTit = "000000";

        $colorError = "FF0000";
        $textoError = "FFFFFF";

        $font = $this->font;
        
        $tipo = \strtoupper($this->tipo);

        if ($tipo == "AC") {
            $colorClaroPar = "E0E7FF";
            $colorObscuroTit = "2F6A9D";
            $colorBordeTit = "E0E7FF";
            $colorBordeCelda = "2F6A9D";
            $textoTit = "$blanco";
        } elseif ($tipo == "CH") {
            $colorClaroPar = "C2E4E6";
            $colorObscuroTit = "1D747C";
            $colorBordeTit = "C2E4E6";
            $colorBordeCelda = "1D747C";
            $textoTit = "$blanco";
        } elseif ($tipo == "PRE") {
            $colorClaroPar = "F7BE81";
            $colorObscuroTit = "FF8000";
            $colorBordeTit = "F7BE81";
            $colorBordeCelda = "FF8000";
            $textoTit = "$negro";
        }



        $totales = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => $textoTit
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => $colorObscuroTit)
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeTit
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $totalesTxt = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => $textoTit
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => $colorObscuroTit)
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeTit
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $encabezados = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => $negro
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => $blanco)
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array(
                        'rgb' => $blanco
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => true
            )
        );

        $tituloColumnas_vertical = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'size' => 10,
                'color' => array(
                    'rgb' => $textoTit
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => $colorObscuroTit)
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeTit
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 90,
                'wrap' => true
            )
        );


        $tituloColumnas = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'size' => 10,
                'color' => array(
                    'rgb' => $textoTit
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => $colorObscuroTit)
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeTit
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => true
            )
        );

        $informacionPar = array(
            'font' => array(
                'name' => $font,
                'size' => 10,
                'color' => array(
                    'rgb' => $negro
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => $colorClaroPar)
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeCelda
                    )
                ),
            ),
            'alignment' => array(
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => $wrapLineData
            )
        );


        $informacionImpar = array(
            'font' => array(
                'name' => $font,
                'size' => 10,
                'color' => array(
                    'rgb' => "$negro"
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => "$blanco")
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeCelda
                    )
                ),
            ),
            'alignment' => array(
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => $wrapLineData
            )
        );

        $firmas = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => "$negro"
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_NONE,
                'color' => array(
                    'rgb' => "$blanco")
            ),
            'borders' => array(
                'top' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => array(
                        'rgb' => "$negro"
                    )
                ),
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => false
            )
        );

        $firmasSinLinea = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => "$negro"
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_NONE,
                'color' => array(
                    'rgb' => "$blanco")
            ),
            'borders' => array(
                'top' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_NONE,
                    'color' => array(
                        'rgb' => "$negro"
                    )
                ),
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => false
            )
        );

        $sinRegistros = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => "$negro"
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => "$blanco")
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeCelda
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => TRUE
            )
        );

        $negritaDerechaSinFondo = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => "$negro"
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => "$blanco")
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeCelda
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => false
            )
        );

        $negritaIzquierdaSinFondo = array(
            'font' => array(
                'name' => $font,
                'bold' => true,
                'italic' => false,
                'strike' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => "$negro"
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => "$blanco")
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeCelda
                    )
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => false
            )
        );

        $error = array(
            'font' => array(
                'name' => $font,
                'bold' => false,
                'italic' => false,
                'strike' => false,
                'size' => 10,
                'color' => array(
                    'rgb' => $textoError
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => "$colorError")
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array(
                        'rgb' => $colorBordeCelda
                    )
                )
            ),
            'alignment' => array(
                //'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => false
            )
        );

        $estiloContenidoJustificado = array(
            'font' => array(
                'name' => $font,
                'bold' => false,
                'italic' => false,
                'strike' => false,
                'size' => 11,
                'color' => array(
                    'rgb' => '000000'
                )
            ),
            'fill' => array(
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array(
                    'rgb' => $blanco)
            ),
            'borders' => array(
                'allborders' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_NONE
                )
            ),
            'alignment' => array(
                'horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY,
                'vertical' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                'rotation' => 0,
                'wrap' => true
            )
        );


        if ($estilo == "totales") {
            return $totales;
        } else if ($estilo == "totalesTxt") {
            return $totalesTxt;
        } else if ($estilo == "firmas") {
            return $firmas;
        } else if ($estilo == "sinRegistros") {
            return $sinRegistros;
        } else if ($estilo == "encabezados") {
            return $encabezados;
        } else if ($estilo == "tituloColumnas") {
            return $tituloColumnas;
        } else if ($estilo == "informacionPar") {
            return $informacionPar;
        } else if ($estilo == "informacionImpar") {
            return $informacionImpar;
        } else if ($estilo == "negritaDerechaSinFondo") {
            return $negritaDerechaSinFondo;
        } else if ($estilo == "negritaIzquierdaSinFondo") {
            return $negritaIzquierdaSinFondo;
        } else if ($estilo == "firmasSinLinea") {
            return $firmasSinLinea;
        } else if ($estilo == "estiloContenidoJustificado") {
            return $estiloContenidoJustificado;
        } else if ($estilo == "tituloColumnas_vertical") {
            return $tituloColumnas_vertical;
        } else {
            return $error;
        }
    }

}
