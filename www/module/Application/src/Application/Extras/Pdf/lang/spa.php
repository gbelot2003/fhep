<?php
//============================================================+
// File name   : spa.php
// Begin       : 2004-03-03
// Last Update : 2010-10-26
//
// Description : Language module for TCPDF
//               (contains translated texts)
//               Spanish; Castilian
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
 * TCPDF language file (contains translated texts).
 * @package com.tecnick.tcpdf
 * @brief TCPDF language file: Spanish; Castilian
 * @author Nicola Asuni
 * @since 2004-03-03
 */

// Spanish; Castilian

global $l;
$l = Array();

// PAGE META DESCRIPTORS --------------------------------------

$l['a_meta_charset'] = 'UTF-8';
$l['a_meta_dir'] = 'ltr';
$l['a_meta_language'] = 'es';

// TRANSLATIONS --------------------------------------
$l_0['w_page'] = 'Página ';
$l['w_page'] = 'Creado el: '. date('d/m/Y H:i:s'). ' Por: '. $_SESSION['auth']['usuario'] . '                       página';
$l2['w_page'] = 'Por: '. $_SESSION['auth']['usuario'] . '                       página';
$l3['w_page'] = 'Generado por: '. $_SESSION['auth']['usuario'];

//============================================================+
// END OF FILE
//============================================================+
