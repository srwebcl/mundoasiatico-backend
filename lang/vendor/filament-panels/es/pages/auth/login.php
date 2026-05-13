<?php

return [

    'title' => 'Mundo Asiático — Acceso',

    'heading' => 'Ingresar al Panel',

    'actions' => [

        'register' => [
            'before' => 'o',
            'label' => 'Crear cuenta',
        ],

        'request_password_reset' => [
            'label' => '¿Olvidaste tu contraseña?',
        ],

    ],

    'form' => [

        'email' => [
            'label' => 'Correo Electrónico',
        ],

        'password' => [
            'label' => 'Contraseña',
        ],

        'remember' => [
            'label' => 'Mantener sesión activa',
        ],

        'actions' => [

            'authenticate' => [
                'label' => 'Ingresar',
            ],

        ],

    ],

    'messages' => [

        'failed' => 'Las credenciales ingresadas son incorrectas.',

    ],

    'notifications' => [

        'throttled' => [
            'title' => 'Demasiados intentos de acceso.',
            'body' => 'Intenta de nuevo en :seconds segundos.',
        ],

    ],

];
