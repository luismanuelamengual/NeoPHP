<?php

namespace NeoPHP\Mail;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class Mailer
 * @package NeoPHP\Utils
 */
abstract class Mailer {

    /**
     * Crea una instancia del objeto para envio de mails
     * Ejemplo de utilización
     *
     * $mailer = Mailer::create();
     * $mailer->addAddress("luis.amengual@sitrack.com");
     * $mailer->Subject = "Pruebuili";
     * $mailer->Body = "Este es el contenido de la pruebili";
     * if(!$mailer->send()) {
     *     echo "Mailer Error: " . $mailer->ErrorInfo;
     * }
     * else {
     *     echo "Mensaje enviado (7) !!";
     * }
     * @param string $mailServerName
     * @return PHPMailer
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public static function create($mailServerName = null) {
        if (empty($mailServerName)) {
            $mailServerName = get_property("mail.default");
        }
        $mailServers = get_property("mail.servers");
        $mailServer = $mailServers[$mailServerName];

        $mailer = new PHPMailer;
        $mailer->IsSMTP();
        $mailer->SMTPAuth = true;
        $mailer->SMTPAutoTLS = false;
        $mailer->Host = $mailServer["host"];

        if (!empty($mailServer["port"])) {
            $mailer->Port = $mailServer["port"];
        }
        if (!empty($mailServer["username"])) {
            $mailer->Username = $mailServer["username"];
        }
        if (!empty($mailServer["password"])) {
            $mailer->Password = $mailServer["password"];
        }
        if (!empty($mailServer["from_address"])) {
            $fromName = !empty($mailServer["from_name"])? $mailServer["from_name"] : '';
            $mailer->setFrom($mailServer["from_address"], $fromName);
        }
        return $mailer;
    }
}
