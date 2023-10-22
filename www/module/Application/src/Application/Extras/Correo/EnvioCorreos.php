<?php

/**
 * Description of EnvioCorreos
 *
 * @author erickrodriguez
 */

namespace Application\Extras\Correo;

use Application\Model\Confmail;
use Application\Model\Confalertas;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Smtp;
use Exception;

class EnvioCorreos {

    public function __construct() {
        
    }

    /**
     * Envio de correos en formato HTML via gmail.
     * 
     * @param string $htmlBody Cuerpo del correo en formato HTML.
     * @param string $subject Asunto del correo.
     * @param string $destino Correo de destino.
     * @return boolean True si se envió exitosamente false en caso contrario.
     */
    public function enviarCorreo($htmlBody, $subject, $destino) {


        //Recuperar de los parametros del sistema
        $correo = $_SESSION['parametros']['mail_salida'];
        $pass = $_SESSION['parametros']['mail_pass'];
        $nombre = $_SESSION['parametros']['mail_name'];
        $smtpServer = $_SESSION['parametros']['mail_smtp'];
        $puerto = $_SESSION['parametros']['mail_puerto'];
        $aplicar_tls = $_SESSION['parametros']['mail_tls'];

        $options_smtp = [
            'name' => $smtpServer,
            'host' => $smtpServer,
            'port' => $puerto,
            'connection_class' => 'login',
            'connection_config' => [
                'username' => $correo,
                'password' => $pass,
            'ssl' => 'tls',
            ],
        ];
        

//        $options_smtp = [
//            //'name' => 'El nombre',
//            'host' => 'smtp.gmail.com',
//            'port' => 587,
//            'connection_class' => 'login',
//            'connection_config' => [
//                'username' => 'fhep22@gmail.com',
//                'password' => 'Amorypaz',
//                'ssl' => 'tls',
//            ],
//        ];

        if (\strtoupper($aplicar_tls) == "SI") {
            $options_smtp['connection_config']['ssl'] = 'tls';
        }

        // configuración de opciones SMTP  
        $options = new Mail\Transport\SmtpOptions($options_smtp);

        //$htmlBody .= "<p style=\"color: blue; text-align: center;\">No responder este mensaje</p>";
// crear el header del html  
        $html = new MimePart($htmlBody);
        $html->type = "text/html";
//            $textPart = new MimePart($textBody);
//            $textPart->type = "text/plain";


        $body = new MimeMessage();
        $body->setParts(array($html));

// instancia mail   
        $mail = new Mail\Message();
        $mail->setBody($body); // will generate our code html from template.phtml  
        $mail->setFrom($correo, $nombre);

        $mail->setSubject($subject);

        //Enviar correo
        $transport = new Smtp($options);

        $mail->setTo($destino);
        //$transport->send($mail);



        $enviado = true;

        try {
            $transport->send($mail);
        } catch (Exception\RuntimeException $e) {
//                echo "ERROR".$e;
//                exit;

            $enviado = false;
        }




        return $enviado;
    }

}
