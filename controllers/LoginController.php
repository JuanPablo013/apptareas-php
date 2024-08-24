<?php

namespace Controllers;

use Classes\Email;
use Model\Usuario;
use MVC\Router;

class LoginController {
    public static function login(Router $router) {

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);

            $alertas = $usuario->validarLogin();

            if(empty($alertas)) {
                // Verificar que el Usuario exista
                $usuario = Usuario::where('email', $usuario->email);

                if(!$usuario || !$usuario->confirmado) {
                    Usuario::setAlerta('error', 'El Usuario no existe o no está confirmado');
                } else {
                    // El Usuario existe
                    if(password_verify($_POST['password'], $usuario->password)) {

                        // Inicia la sesión
                        session_start();

                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        // Redireccionar
                        header('Location: /dashboard');
                    } else {
                        Usuario::setAlerta('error', 'Contraseña incorrecta');
                    }
                }
            }
        }

        $alertas = Usuario::getAlertas();

        // Render a la vista
        $router->render('auth/login', [
            'titulo' => 'Iniciar Sesión',
            'alertas' => $alertas
        ]);

    }

    public static function logout() {
        session_start();
        $_SESSION = [];
        header('Location: /');
    }

    public static function crear(Router $router) {

        $alertas = [];
        $usuario = new Usuario;

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            if(empty($alertas)) {
                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario) {
                    Usuario::setAlerta('error', 'El Usuario ya esta registrado');
                    $alertas = Usuario::getAlertas();
                } else {
                    // Hashear la Contraseña
                    $usuario->hashPassword();

                    // Eliminar Contraseña 2
                    unset($usuario->password2);

                    // Generar el Token
                    $usuario->crearToken();

                    // Crear un nuevo Usuario
                    $resultado = $usuario->guardar();

                    // Enviar el Correo Electrónico
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    if($resultado) {
                        header('Location: /mensaje');
                    }
                }
                
            }
        }

        // Render a la vista
        $router->render('auth/crear', [
            'titulo' => 'Crea tu cuenta',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function olvido(Router $router) {
        $alertas = [];
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = new Usuario($_POST);
            $alertas = $usuario->validarEmail();

            if(empty($alertas)) {
                // Buscar el Usuario
                $usuario = Usuario::where('email', $usuario->email);

                if($usuario && $usuario->confirmado) {
                    // Generar un nuevo Token
                    $usuario->crearToken();
                    unset($usuario->password2);

                    // Actualizar el Usuario
                    $usuario->guardar();

                    // Enviar el Correo Electrónico
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarInstrucciones();

                    // Imprimir la alerta
                    Usuario::setAlerta('exito', 'Hemos enviado las instrucciones a tu correo electrónico');
                } else {
                    Usuario::setAlerta('error', 'El Usuario no existe o no está confirmado');
                }
            }
        }

        $alertas = Usuario::getAlertas();

        // Muestra la vista
        $router->render('auth/olvido', [
            'titulo' => 'Olvidaste tu Contraseña',
            'alertas' => $alertas
        ]);
    }

    public static function reestablecer(Router $router) {

        $token = s($_GET['token']);
        $mostrar = true;

        if(!$token) header('Location: /');

        // Identificar el Usuario con este Token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            Usuario::setAlerta('error', 'Token no válido');
            $mostrar = false;
        }

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Añadir la nueva contraseña
            $usuario->sincronizar($_POST);

            // Validar la Contraseña
            $alertas = $usuario->validarContraseña();

            if(empty($alertas)) {
                // Hashear la nueva Contraseña
                $usuario->hashPassword();
                unset($usuario->password2);

                // Eliminar el Token
                $usuario->token = null;

                // Guardar el Usuario en la Base de Datos
                $resultado = $usuario->guardar();

                // Redireccionar
                if($resultado) {
                    header('Location: /');
                }
            }

        }

        $alertas = Usuario::getAlertas();

        // Muestra la vista
        $router->render('auth/reestablecer', [
            'titulo' => 'Reestablecer Contraseña',
            'alertas' => $alertas,
            'mostrar' => $mostrar
        ]);
    }

    public static function mensaje(Router $router) {
        // Muestra la vista
        $router->render('auth/mensaje', [
            'titulo' => 'Cuenta creada correctamente'
        ]);
    }

    public static function confirmar(Router $router) {

        $token = s($_GET['token']);

        if(!$token) header('Location: /');

        // Encontrar al Usuario con el token
        $usuario = Usuario::where('token', $token);

        if(empty($usuario)) {
            // No se encontró un Usuario con este token
            Usuario::setAlerta('error', 'Token no válido');
        } else {
            // Confirmar la cuenta
            $usuario->confirmado = 1;
            $usuario->token = null;
            unset($usuario->password2);

            // Guardar en la Base de Datos
            $usuario->guardar();

            Usuario::setAlerta('exito', 'Cuenta confirmada correctamente');
        }

        $alertas = Usuario::getAlertas();

        // Muestra la vista
        $router->render('auth/confirmar', [
            'titulo' => 'Confirma tu cuenta',
            'alertas' => $alertas
        ]);
    }
}