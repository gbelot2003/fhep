<?php

/*

 * @Nombre    : RespaldosController
 * @Author    : Erick Rodriguez
 * @Copyright : Erick Rodriguez
 * @Creado el : 12-26-2014, 05:48:28 PM
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use PDO;
use Application\Extras\Utilidades\Bitacora;
use Application\Model\Objetos;

/* Aquí los Modelos */
use Application\Model\Respaldosdb;

class RespaldosController extends AbstractActionController {

    private $clave = 'Una cadena, muy, muy larga para mejorar la encriptacion';

    public function __construct() {
        /* $_SESSION['unidades'] = 'active'; */
    }

    public function respaldosAction() {

        $path = $_SESSION['directorioBase'] . "/../backup";

        if (!\file_exists($path)) {
            \mkdir($path, 0700);
        }

        $dir_handle = opendir($path) or die("No se pudo abrir $path");

        $mostrar = "_bk";
        $archivos = array();
        $cont = 0;
        while ($file = readdir($dir_handle)) {
            \set_time_limit(30);
            $pos = \strrpos($file, ".");
            $extension = \substr($file, $pos + 1);

            $nombre = \substr($file, 0, $pos);
            $fecha = \substr($file, 8, 10);
            $hora = \substr($file, 19, 8);
            $horaF = \str_replace('-', ':', $hora);

            if (in_array($extension, $mostrar)) {

                $descDat = $respaldosModel->getDescripcion($nombre);

                $desc = $descDat['descripcion'];
                $id = $descDat['id'];
                if (!$desc) {
                    $desc = "No disponible";
                }
                $archivos[$cont] = array('n' => $id, 'nombre' => $nombre, 'fecha' => $fecha, 'hora' => $horaF, 'desc' => $desc);
                $cont++;
            }
        }

        \closedir($dir_handle);

        return new ViewModel(array('archivos' => $archivos));
    }

    public function restaurarAction() {
        if (!isset($_SESSION['auth']) || $_SESSION['rol']['restaurar_backup'] != '1') {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        $path = $_SESSION['directorioBase'] . "/../backupAMDC";

        if (!\file_exists($path)) {
            \mkdir($path, 0700);
        }

        $dir_handle = opendir($path) or die("No se pudo abrir $path");

        $mostrar = Array("_bk");

        $archivos = array();
        $cont = 0;
        while ($file = readdir($dir_handle)) {
            \set_time_limit(30);
            $pos = \strrpos($file, ".");
            $extension = \substr($file, $pos + 1);

            $nombre = \substr($file, 0, $pos);
            $fecha = \substr($file, 8, 10);
            $hora = \substr($file, 19, 8);
            $horaF = \str_replace('-', ':', $hora);

            $desc = $descDat['descripcion'];
            $id = $descDat['id'];
            if (!$desc) {
                $desc = "No disponible";
            }

            if (\in_array($extension, $mostrar)) {
                $archivos[$cont] = array('n' => $id, 'nombre' => $nombre, 'fecha' => $fecha, 'hora' => $horaF, 'desc' => $desc);
                $cont++;
            }
        }
        \closedir($dir_handle);

        return new ViewModel(array('archivos' => $archivos));
    }

    public function restaurararchivoAction() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['restaurar_backup'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        if ($this->getRequest()->isPost()) {
//Recuperar los datos de los campos del formulario
            $datosFormularios = $this->request->getPost()->toArray();
            \ini_set('memory_limit', '-1');
            $archivo = $_FILES["archivo"]["tmp_name"];
            $nombre = $_FILES['archivo']['name'];

            $inicioNombre = \substr($archivo, 0, 2);

            $ext_permitidas = array('_bk');
            $partes_nombre = \explode('.', $nombre);
            $extension = \end($partes_nombre);

//            echo $extension;exit;
            $ext_correcta = \in_array($extension, $ext_permitidas);

            if (!$ext_correcta) {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'El archivo recibido no corresponde al archivo esperado.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/respaldos/backup");
            }
//Leer el archivo con la configuracion de la base de datos
            $config = $this->getEvent()->getApplication()->getServiceManager()->get('Config');

//recuperar solo el nombre sin extensión
            $soloNombre = $partes_nombre[0];

            $usuarioDB = $config['db']['username'];
            $hostDB = $config['db']['hostname'];
            $passDB = $config['db']['password'];
            $nameDB = $config['db']['database'];

//crear un respaldo automatico antes de restaurar la anterior
            $this->respaldarDB($hostDB, $usuarioDB, $passDB, $nameDB, "RESTAURACION_", false, '*', $soloNombre);

            if ($this->restaurarBaseDatos($hostDB, $usuarioDB, $passDB, $nameDB, $archivo, true)) {


                $_SESSION['mnsAutoOK'] = array('titulo' => 'Respaldo Finalizado!', 'texto' => 'Respaldo reslizado exitosamente!!');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/respaldos/backup");
            } else {
                $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrio un error al realizar el respaldo, intentelo después.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/respaldos/backup");
            }
        } else {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
    }

    public function restaurararchivo2Action() {
//        if (!isset($_SESSION['auth']) || $_SESSION['rol']['restaurar_backup'] != '1') {
//            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
//        }

        $archivo = $this->params()->fromRoute("archivo", null);

        $path = $_SESSION['directorioBase'] . "/../backup/" . $archivo . "._bk";

//Leer el archivo con la configuracion de la base de datos
        $config = $this->getEvent()->getApplication()->getServiceManager()->get('Config');

        $usuarioDB = $config['db']['username'];
        $hostDB = $config['db']['hostname'];
        $passDB = $config['db']['password'];
        $nameDB = $config['db']['database'];

        //crear un respaldo automatico antes de restaurar la anterior
        $this->respaldarDB($hostDB, $usuarioDB, $passDB, $nameDB, "RESTAURACION_", false, '*', $archivo);

        if ($this->restaurarBaseDatos($hostDB, $usuarioDB, $passDB, $nameDB, $path, false)) {
            $_SESSION['mnsAutoOK'] = array('titulo' => 'Respaldo Finalizado!', 'texto' => 'Respaldo reslizado exitosamente!!');
        } else {
            $_SESSION['mnsAutoError'] = array('titulo' => 'Error!', 'texto' => 'Ocurrio un error al realizar el respaldo, intentelo después.');
        }


        return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/respaldos/backup");
    }

    private function restaurarBaseDatos($host, $user, $pass, $name, $archivo, $borrar) {

// db config
        $dbhost = $host;
        $dbuser = $user;
        $dbpass = $pass;
        $dbname = $name;
        try {
// Conectar la Base de datos
            $pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

            \ini_set('memory_limit', '-1');

            $texto = \file_get_contents($archivo);

//Desencriptacion del archivo

            $method = 'aes-256-cbc';
            // Puedes generar una diferente usando la funcion $getIV()
            $iv = \base64_decode("C9fBxl1EWtYTL1/M8jfstw==");

            $texto = \openssl_decrypt($texto, $method, $this->clave, false, $iv);

            
            $sentencia = \explode("&#!#&", $texto);

            for ($i = 1; $i < (count($sentencia)); $i++) {
                \set_time_limit(30);
                $pdo->exec("$sentencia[$i]"); // or die("Error en sentencia: $i << " . $sentencia[$i] . ';  >>  ' . \print_r($pdo->errorInfo()));

                if ($pdo->errorCode() != '00000') {
                    echo "<pre>ERROR:<br>";
                    echo "$sentencia[$i]<br><br>";
                    echo "Error Code: " + $pdo->errorCode();
                    exit;
                    return false;
                }
            }

            if ($borrar) {
                \unlink($archivo);
            }

            return true;
        } catch (PDOException $e) {
            echo "Error!: " . $e->getMessage() . "<br/>";
            return false;
        }
    }

    public function nuevoAction() {
        if (!isset($_SESSION['auth']) || $_SESSION['rol']['crear_backup'] != '1') {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }

        if ($this->getRequest()->isPost()) {


            $datosFormularios = $this->request->getPost()->toArray();
            $descargar = false;
            if (isset($datosFormularios['descargar'])) {
                $descargar = true;
            }

            $descripcion = $datosFormularios['descripcion'];

//Leer el archivo con la configuracion de la base de datos
            $config = $this->getEvent()->getApplication()->getServiceManager()->get('Config');

            $usuarioDB = $config['db']['username'];
            $hostDB = $config['db']['hostname'];
            $passDB = $config['db']['password'];
            $nameDB = $config['db']['database'];

            $this->respaldarDB($hostDB, $usuarioDB, $passDB, $nameDB, $descargar, $descripcion);
            $_SESSION['mnsAutoOK'] = array('titulo' => 'Archivo Creado!', 'texto' => 'Archivo creado exitosamente!!');
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/respaldos");
        } else {

            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/respaldos");
        }
    }

    private function restaurarBD($host, $user, $pass, $name, $archivo) {
        $conecta = mysql_connect("127.0.0.1:3306", $user, $pass);
        if (!$conecta) {
            die('No conectado : ' . mysql_error());
        }
        $db_selected = mysql_select_db($name, $conecta);
        if (!$db_selected) {
            echo 'No es la base de datos', $db_selected, '<br/>';
            die(mysql_error());
        } else {
            $texto = file_get_contents($archivo);

//Seguridad para el archivo descargado
            $algorithm = MCRYPT_BLOWFISH;
            $key = 'Aqui va la llave para que la encriptacion sea segura.';

            $mode = MCRYPT_MODE_CBC;

            $iv = mcrypt_create_iv(mcrypt_get_iv_size($algorithm, $mode), MCRYPT_DEV_URANDOM);

            $encrypted_data = base64_decode($texto);

            if (strlen($encrypted_data) == 0) {
                return false;
            }

            $txtDesencriptado = \mcrypt_decrypt($algorithm, $key, $encrypted_data, $mode, $iv);

            $sentencia = explode("&#!#&", $txtDesencriptado);
            for ($i = 1;
                    $i < (count($sentencia) - 1);
                    $i++) {
                \set_time_limit(30);
                $r = mysql_query("$sentencia[$i];") or die("Error en sentencia: " . $sentencia[$i] . '-->>  ' . mysql_error());

                if (!$r) {
                    return false;
                }
            }
        }
        return true;
    }

    private function eliminarRespaldosAT() {
        $path = $_SESSION['directorioBase'] . "/../backup";
        $dir_handle = opendir($path) or die("No se pudo abrir $path");

        $mostrar = Array("_bk");

        while ($file = readdir($dir_handle)) {
            \set_time_limit(30);
            $pos = \strrpos($file, ".");
            $extension = \substr($file, $pos + 1);

            $nombre = \substr($file, 0, $pos);
            $fecha = \substr($file, 8, 10);
            $hora = \substr($file, 19, 8);
            $horaF = \str_replace('-', ':', $hora);

            if (in_array($extension, $mostrar)) {
                $inicioNombre = \substr($nombre, 0, 2);
                $archivo = $path . "/$nombre._bk";
                if ($inicioNombre == "AT") {
                    unlink($archivo);
                }
            }
        }

        closedir($dir_handle);
    }

    private function respaldarDB($host, $user, $pass, $name, $nombre_r, $descargar = false, $tables = '*', $archivo_restaurado = null) {
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
//        $respaldosDbModel = new Respaldosdb($this->dbAdapter);

        $dbhost = $host;
        $dbuser = $user;
        $dbpass = $pass;
        $dbname = $name;

        $a = "";
        $restaurado = "";
        if ($archivo_restaurado != null) {

            $a = "AT_";
            $restaurado = "#SE RESTAURÓ EL RESPALDO $archivo_restaurado \n\n";
        } else {
            $a = "BK_";
        }

        $fecha = \date('d/m/Y H:i:s');

        $salida = "#CREADO DESDE EL SISTEMA FHEP\n"
                . "#CREADO EL $fecha\n\n $restaurado \n\n";

// Conectar la Base de datos
        $pdo = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);

        if ($tables == '*') {
            $salida .= "DROP DATABASE IF EXISTS `$dbname`;\n";
            $salida .= "CREATE DATABASE `$dbname`;\n";

            $tables = array();
            $stmt = $pdo->query("SHOW TABLES");
            while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
                $tables[] = $row[0];
            }
        } else {
            $tables = is_array($tables) ? $tables : explode(',', $tables);
        }

        $salida .= "USE `$dbname`;&#!#&\n\n\n";

        $salida .= "SET FOREIGN_KEY_CHECKS = 0;&#!#&\n\n";

        \ini_set('memory_limit', '-1');
// process each table in the db
        foreach ($tables as $table) {
            \set_time_limit(0);

//            if ($table != "filas_auditoria" && $table != "respaldos") {
            if (true) {
                $fields = "";
                $sep2 = "";

                $max_insert = 1000;
                $c_insert = 0;
                $salida .= "DROP TABLE IF EXISTS `$table`;&#!#&\n";

// Obtener la informacion para crear la tabla
                $stmt = $pdo->query("SHOW CREATE TABLE $table");
                $row = $stmt->fetch(PDO::FETCH_NUM);

                $salida .= $row[1] . ";&#!#&\n\n";
// Obtener los datos de la tabla
//                $salida .= "LOCK TABLES `$table` WRITE;\n";
//                $salida .= "ALTER TABLE `$table` DISABLE KEYS;\n\n";

                $stmt_col = $pdo->query("DESCRIBE $table");
                $cols = $stmt->fetch(PDO::FETCH_NUM);

                $info_campos = [];
                while ($row = $stmt_col->fetch(PDO::FETCH_OBJ)) {
                    $d = \json_decode(\json_encode($row), true);
                    $info_campos[$d['Field']] = $d['Type'];
                }

                $stmt = $pdo->query("SELECT * FROM $table");
                while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {

                    if ($c_insert == 0) {
                        $salida .= $sep2 . "INSERT INTO `$table`  VALUES " . "";
                        $sep3 = "";
                    }

                    if ($c_insert >= $max_insert) {
                        $salida .= $sep2 . ";&#!#&\n\n\nINSERT INTO `$table`  VALUES " . "";
                        $sep3 = "";
                        $c_insert = 0;
                    }
// Crear los insert de cada fila de la tabla por separado
                    $sep = "";

                    $salida .= "$sep3 (";

                    foreach ($row as $col => $val) {
// agregar slashes en los contenidos de los campos
//$val = addslashes($val);
//echo "$val tipo {$info_campos[$col]}" ;
                        if (\strpos($info_campos[$col], 'int') !== false || \strpos($info_campos[$col], 'decimal') !== false) {

                            if ($val == null) {
                                $val = 0;
                            }

                            $search = array("'", "\n", "\r");
                            $replace = array("''", "\\n", "\\r");
                            $val = \str_replace($search, $replace, $val);
                            $salida .= $sep . "$val";
                        } else if (\strpos($info_campos[$col], 'date') !== false) {

                            if ($val == null) {
                                $val = "1970-01-01";
                            }

                            $search = array("'", "\n", "\r");
                            $replace = array("''", "\\n", "\\r");
                            $val = \str_replace($search, $replace, $val);
                            $salida .= $sep . "'$val'";
                        } else {

                            $search = array("'", "\n", "\r");
                            $replace = array("''", "\\n", "\\r");
                            $val = \str_replace($search, $replace, $val);
                            $val = \mb_convert_encoding($val, "UTF-8", \mb_detect_encoding($val, "UTF-8, ISO-8859-1, ISO-8859-15", true));
                            $salida .= $sep . "'$val'";
                        }
                        $sep = ", ";
                    }
// terminar la fila de datos
                    $salida .= ")";
                    $sep3 = ",\n";
                    $sep2 = "";

                    $c_insert++;
                }
                if ($c_insert > 0) {
                    $salida .= ";&#!#&\n\n";
                }

// terminate insert data
//                $salida .= "ALTER TABLE `$table` ENABLE KEYS;\n";
//                $salida .= "UNLOCK TABLES;\n\n\n";
//                echo $salida;exit;
            }
        }

        $salida .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

//Encriptar el contenido de la base de datos
//Metodo de encriptación
        $method = 'aes-256-cbc';
// Puedes generar una diferente usando la funcion $getIV()
        $iv = \base64_decode("C9fBxl1EWtYTL1/M8jfstw==");
      
        //Encripta el contenido de la variable, enviada como parametro.
        $salida = \openssl_encrypt($salida, $method, $this->clave, false, $iv);
//Escribir el archivo en una carpeta

        $nombreA = $a . $nombre_r . "_" . date('d-m-Y_H-i-s');

        $nombreArchivo = $nombreA . "._bk";
        $ruta = $_SESSION['directorioBase'] . "/../backup";
        $path = $ruta . "/$nombreArchivo";

        if (!\file_exists($ruta)) {
            \mkdir($ruta, 0700);
        }

        $fp = \fopen($path, "w");
        \fputs($fp, $salida);
        \fclose($fp);
        
        //                     GUARDAR A BITACORA
        
        $this->dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter');
        $utl_bitacora = new Bitacora($this->dbAdapter);
        $objetos_model = new Objetos($this->dbAdapter);
        $id_objetos = $objetos_model->getIdobjetoSeguridad();
        $DateAndTime = date('Y-m-d H:i:s');
        $bitacora = ['id_usuario' => $_SESSION['auth']['id_usuario'], 'Fecha' => $DateAndTime, 'id_objeto' => $id_objetos['id'],
            'usuario' => $_SESSION['auth']['usuario'], 'Accion' => 'Respado De La Base De Datos',
            'descripcion' => "La base de datos fue respalda con un nombre de archivo {$nombreArchivo}"];
         

        $utl_bitacora->guardarBitacora($bitacora);

        if ($descargar) {
            // Salida del archivo para el navegador
            header('Content-Description: File Transfer');
            header('Content-type: text/plain');
            header('Content-Disposition: attachment; filename=' . $nombreArchivo);
            header('Content-Transfer-Encoding: base64');
            header('Content-Length: ' . strlen($salida));
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            header('Pragma: public');
            echo $salida;
        }
    }

    public function descargarAction() {
        if (!isset($_SESSION['auth']) || $_SESSION['rol']['crear_backup'] != '1') {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        $archivo = $this->params()->fromRoute("archivo", null);

        if ($this->getRequest()->isPost()) {
            
        } else {

            $path = $_SESSION['directorioBase'] . "/../backupAMDC/" . $archivo . "._bk";
            $texto = file_get_contents($path);
// Salida del archivo para el navegador
            header('Content-Description: File Transfer');
            header('Content-type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $archivo . "._bk");
            header('Content-Transfer-Encoding: binary');
            header('Content-Length: ' . strlen($texto));
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            header('Pragma: public');
            ob_clean();
            flush();
            echo $texto;
            exit;
        }
    }

    public function backupAction() {
        $objeto = 'Seguridad';
        $permiso = 'permiso_consultar';
        if (!isset($_SESSION['auth']) || !isset($_SESSION['permisos'][$objeto]) || $_SESSION['permisos'][$objeto][$permiso] != '1') {
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/");
        }
        if ($this->getRequest()->isXmlHttpRequest()) {
//para AJAX
            exit;
        } else if ($this->getRequest()->isPost()) {
            $datosFormularios = $this->request->getPost()->toArray();
            $descargar = false;
            if (isset($datosFormularios['descargar'])) {
                $descargar = true;
            }

//Leer el archivo con la configuracion de la base de datos
            $config = $this->getEvent()->getApplication()->getServiceManager()->get('Config');

//            $usuarioDB = $config['db']['username'];
//            $hostDB = $config['db']['hostname'];
//            $passDB = $config['db']['password'];
//            $nameDB = $config['db']['database'];


            if (($datosFormularios['database'] != $config['db']['database']) || ($datosFormularios['hostname'] != $config['db']['hostname'])) {
                $_SESSION['mnsError'] = array('titulo' => 'Error!', 'texto' => 'Error en la información de la base de datos.');
                return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/respaldos");
            }


            $usuarioDB = $config['db']['username'];
            $hostDB = $datosFormularios['hostname'];
            $passDB = $config['db']['password'];
            $nameDB = $datosFormularios['database'];
            $nombre_r = $datosFormularios['nombre_bd'];

            $tables = '*';

            $this->respaldarDB($hostDB, $usuarioDB, $passDB, $nameDB, $nombre_r, $descargar, $tables);

            $_SESSION['mnsAutoOK'] = array('titulo' => 'Archivo Creado!', 'texto' => 'Archivo creado exitosamente!!');
            return $this->redirect()->toUrl($this->getRequest()->getBaseUrl() . "/respaldos");
        }
    }

}
