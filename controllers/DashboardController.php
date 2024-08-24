<?php

namespace Controllers;

use Model\Proyecto;
use MVC\Router;
use Model\Usuario;

class DashboardController {
    public static function index(Router $router) {

        session_start();
        isAuth();

        $id = $_SESSION['id'];

        $proyectos = Proyecto::belongsTo('propietarioId', $id);

        $router->render('dashboard/index', [
            'titulo' => 'Proyectos',
            'proyectos' => $proyectos
        ]);
    }

    public static function crear_proyecto(Router $router) {

        session_start();
        isAuth();

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $proyecto = new Proyecto($_POST);

            // Validación
            $alertas = $proyecto->validarProyecto();

            if(empty($alertas)) {
                // Generar una URL única
                $hash = md5(uniqid());
                $proyecto->url = $hash;

                // Almacenar el creador del proyecto
                $proyecto->propietarioId = $_SESSION['id'];

                // Guardar el proyecto
                $proyecto->guardar();

                // Redireccionar
                header('Location: /proyecto?id=' . $proyecto->url);
            }
        }

        $router->render('dashboard/crear-proyecto', [
            'titulo' => 'Crear Proyecto',
            'alertas' => $alertas
        ]);
    }

    public static function proyecto(Router $router) {
        session_start();
        isAuth();

        $urlToken = $_GET['id'];
        if(!$urlToken) header('Location: /dashboard');
        // Revisar que el usuario que visita el proyecto sea el que lo creo
        $proyecto = Proyecto::where('url', $urlToken);
        if($proyecto->propietarioId !== $_SESSION['id']) {
            header('Location: /dashboard');
        }

        $router->render('dashboard/proyecto', [
            'titulo' => $proyecto->proyecto
        ]);
    }

    public static function perfil(Router $router) {

        session_start();
        isAuth();
        $alertas = [];

        $usuario = Usuario::find($_SESSION['id']);

        if($_SERVER['REQUEST_METHOD'] === 'POST') {

            $usuario->sincronizar($_POST);

            $alertas = $usuario->validar_perfil();

            if(empty($alertas)) {

                $existeUsuario = Usuario::where('email', $usuario->email);

                if($existeUsuario && $existeUsuario->id !== $usuario->id ) {
                    
                    // Mensaje de error
                    Usuario::setAlerta('error', 'Cuenta ya registrada');
                    $alertas = $usuario->getAlertas();
                } else {
                    // Guardar el registro
                    $usuario->guardar();

                    Usuario::setAlerta('exito', 'Guardado correctamente');
                    $alertas = $usuario->getAlertas();

                    // Asignar el nombre nuevo a la barra
                    $_SESSION['nombre'] = $usuario->nombre;
                }
            }
        }
        
        $router->render('dashboard/perfil', [
            'titulo' => 'Perfil',
            'usuario' => $usuario,
            'alertas' => $alertas
        ]);
    }

    public static function cambiar_contraseña(Router $router) {
        session_start();
        isAuth();

        $alertas = [];

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario = Usuario::find($_SESSION['id']);

            // Sincronizar con los datos del usuario
            $usuario->sincronizar($_POST);

            $alertas = $usuario->nueva_contraseña();

            if(empty($alertas)) {
                $resultado = $usuario->comprobar_contraseña();

                if($resultado) {
                    $usuario->password = $usuario->contraseña_nueva;

                    // Eliminar propiedades no necesarias
                    unset($usuario->contraseña_actual);
                    unset($usuario->contraseña_nueva);

                    // Hashear la nueva contraseña
                    $usuario->hashPassword();

                    // Actualizar
                    $resultado = $usuario->guardar();

                    if($resultado) {
                        Usuario::setAlerta('exito', 'Contraseña guardada correctamente');
                        $alertas = $usuario->getAlertas();
                    }
                } else {
                    Usuario::setAlerta('error', 'Contraseña Incorrecta');
                    $alertas = $usuario->getAlertas();
                }
            }
        }

        $router->render('dashboard/cambiar-contraseña', [
            'titulo' => 'Cambiar contraseña',
            'alertas' => $alertas
        ]);
    }

}