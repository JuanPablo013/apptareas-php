<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {
    protected $email;
    protected $nombre;
    protected $token;

    public function __construct($email, $nombre, $token) 
    {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion() {
        // Crear el objeto de Email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '84f0e71d6f3c64';
        $mail->Password = '1459098b4d0fc5';

        $mail->setFrom('cuentas@apptareas.com');
        $mail->addAddress('cuentas@apptareas.com', 'AppTareas.com');
        $mail->Subject = 'Confirmar cuenta';

        // Set HTML
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Has creado tu cuenta en App Tareas, solo debes confirmarla presionando el siguiente enlace</p>";
        $contenido .= "<p>Presiona aquí: <a href='http://localhost:3000/confirmar?token=" . $this->token . "'>Confirmar Cuenta</a></p>";
        $contenido .= "<p>Si no solicitaste este correo, puedes ignorar el mensaje</p>";
        $contenido .= '</html>';

        $mail->Body = $contenido;

        // Enviar el Email
        $mail->send();
    }

    public function enviarInstrucciones() {
        // Crear el objeto de Email
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth = true;
        $mail->Port = 2525;
        $mail->Username = '84f0e71d6f3c64';
        $mail->Password = '1459098b4d0fc5';

        $mail->setFrom('cuentas@apptareas.com');
        $mail->addAddress('cuentas@apptareas.com', 'AppTareas.com');
        $mail->Subject = 'Reestablece tu Contraseña';

        // Set HTML
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $contenido = '<html>';
        $contenido .= "<p><strong>Hola " . $this->nombre . "</strong> Has olvidado tu contraseña, sigue el siguiente enlace para reestablecerla</p>";
        $contenido .= "<p>Presiona aquí: <a href='http://localhost:3000/reestablecer?token=" . $this->token . "'>Reestablecer Contraseña</a></p>";
        $contenido .= "<p>Si no solicitaste este correo, puedes ignorar el mensaje</p>";
        $contenido .= '</html>';

        $mail->Body = $contenido;

        // Enviar el Email
        $mail->send();
    }
}