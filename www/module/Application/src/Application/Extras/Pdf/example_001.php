<?php
//============================================================+
// File name   : Formulario inscripcion.php
// Begin       : 2008-03-04
// Last Update : 2013-05-14
//
// Description : Formulario de inscripción para el programa de apoyo escolar
//               
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Creates an example PDF TEST document using TCPDF
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Example: Default Header and Footer
 * @author Nicola Asuni
 * @since 2008-03-04
 */

// Include the main TCPDF library (search for installation path).
require_once('tcpdf_include.php');

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nicola Asuni');
$pdf->SetTitle('TCPDF Example 001');
$pdf->SetSubject('TCPDF Tutorial');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('dejavusans', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();

// set text shadow effect
$pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));

// Set some content to print
$html = <<<EOD
        
<h1>Hola Mundo</h1>
        <div class="formulario">
        <form name="upload-form" action="javascript:void(0);"  id="upload-form" method="post" enctype="multipart/form-data">


            <div class="row">
                <div class="col-lg-6">

                    <div class="form-group">
                        <label for="usuario">Usuario *</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required="required"
                               placeholder="Ingrese el usuario " pattern="[a-zA-Z0-9_-]*">
                    </div>
                    <div class="form-group">
                        <label for="pass">Contraseña *</label>
                        <input type="password" class="form-control" id="pass" name="pass" required="required"
                               placeholder="Ingrese la contraseña ">
                    </div>
                    <div class="form-group">
                        <label for="pass2">Reescribir Contraseña *</label>
                        <input type="password" class="form-control" id="rpass" name="rpass" required="required"
                               placeholder="Reescriba la contraseña ">
                    </div>
                    <div class="form-group">
                        <label for="id_permiso">Seleccione el Set de Permisos *</label>
                        <select class="form-control" id="id_permiso" name="id_permiso">
                            <option value="-1">Seleccione un set de permisos</option>
                            
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="telefono">Teléfono Fijo</label>
                                <input type="text" maxlength="30" class="form-control" id="telefonos" name="telefonos"
                                       placeholder="2233-4455/2221-2343" pattern="([0-9]{4}[-][0-9]{4}([/]([0-9]{4}[-][0-9]{4}))*)">
                            </div>

                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="celular">Teléfono Celular</label>
                                <input type="text" maxlength="30" class="form-control" id="celular" name="celular"
                                       placeholder="9933-4455/3321-2343" pattern="([0-9]{4}[-][0-9]{4}([/]([0-9]{4}[-][0-9]{4}))*)">
                            </div>

                        </div>
                    </div>

                </div>
                <div class="col-lg-6">

                    <div class="form-group">
                        <label for="identidad">Identidad *</label>
                        <input type="text" maxlength="15" class="form-control" id="identidad" name="identidad" required="required" pattern="([0-9]{4}[-][0-9]{4}[-][0-9]{5})"
                               placeholder="Ingrese el Número de Identidad así: 0801-1999-00156">
                    </div>
                    <div class="form-group">
                        <label for="nombres">Nombres *</label>
                        <input type="text" class="form-control" id="nombres" name="nombres" required="required"
                               placeholder="Ingrese el usuario " pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚ]*([ ]?[a-zA-ZñÑáéíóúÁÉÍÓÚ.]*)*">
                    </div>
                    <div class="form-group">
                        <label for="titulo">Apellidos *</label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos" required="required"
                               placeholder="Ingrese el usuario " pattern="[a-zA-ZñÑáéíóúÁÉÍÓÚ]*([ ]?[a-zA-ZñÑáéíóúÁÉÍÓÚ]*)*">
                    </div>
                    <div class="form-group">
                        <label for="direccion">Dirección *</label>
                        <textarea  class="form-control" rows="1" style="resize: none;" id="direccion" name="direccion" required="required" placeholder="Ingrese la dirección"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo Electrónico</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               placeholder="Ingrese el email: usuario@dominio.com">
                    </div>
                </div>

            </div>




            <input type="submit" id="boton_subir" value="Crear Nuevo Usuario" class="btn btn-primary"/>

        </form>
    </div>
EOD;

// Print text using writeHTMLCell()
$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output('example_001.pdf', 'I');

//============================================================+
// END OF FILE
//============================================================+
