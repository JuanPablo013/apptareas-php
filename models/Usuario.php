<?php

namespace Model;

class Usuario extends ActiveRecord {
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'email', 'password', 'token', 'confirmado'];

    public function __construct($args = []) 
    {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->password = $args['password'] ?? '';
        $this->password2 = $args['password2'] ?? '';
        $this->contraseña_actual = $args['contraseña_actual'] ?? '';
        $this->contraseña_nueva = $args['contraseña_nueva'] ?? '';
        $this->token = $args['token'] ?? '';
        $this->confirmado = $args['confirmado'] ?? 0;
    }

    // Validar el Login de Usuarios
    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El Correo electrónico es obligatorio';
        } 
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'El Correo electrónico no es válido';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'La Contraseña es obligatoria';
        }

        return self::$alertas;
    }

    // Validación para cuentas nuevas
    public function validarNuevaCuenta() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre es obligatorio';
        } 
        if(!$this->email) {
            self::$alertas['error'][] = 'El Correo electrónico es obligatorio';
        } 
        if(!$this->password) {
            self::$alertas['error'][] = 'La Contraseña es obligatoria';
        }        
        if(strlen($this->password) < 8) {
            self::$alertas['error'][] = 'La Contraseña debe contener al menos 8 caracteres';
        }     
        if($this->password !== $this->password2 ) {
            self::$alertas['error'][] = 'Las Contraseñas son diferentes';
        }     

        return self::$alertas;
    }

    // Hashea la Contraseña
    public function hashPassword() : void {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    // Validar la Contraseña
    public function validarContraseña() {
        if(!$this->password) {
            self::$alertas['error'][] = 'La Contraseña es obligatoria';
        }        
        if(strlen($this->password) < 8) {
            self::$alertas['error'][] = 'La Contraseña debe contener al menos 8 caracteres';
        }      

        return self::$alertas;
    }

    // Generar un Token
    public function crearToken() : void {
        $this->token = md5(uniqid());
    }

    // Valida un Correo Electrónico
    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El Correo electrónico es obligatorio';
        }
        if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            self::$alertas['error'][] = 'El Correo electrónico no es válido';
        }
        return self::$alertas;
    }

    public function validar_perfil() {
        if(!$this->nombre) {
            self::$alertas['error'][] = 'El Nombre es obligatorio';
        }
        if(!$this->email) {
            self::$alertas['error'][] = 'El Correo electrónico es obligatorio';
        }
        return self::$alertas;
    }

    public function nueva_contraseña() : array {
        if(!$this->contraseña_actual) {
            self::$alertas['error'][] = 'La contraseña actual no puede ir vacía';
        }
        if(!$this->contraseña_nueva) {
            self::$alertas['error'][] = 'La contraseña nueva no puede ir vacía';
        }
        if(strlen($this->contraseña_nueva) < 8) {
            self::$alertas['error'][] = 'La contraseña debe contener al menos 8 caracteres';
        }
        return self::$alertas;
    }

    // Comprobar la contraseña
    public function comprobar_contraseña() : bool {
        return password_verify($this->contraseña_actual, $this->password );
    }

}