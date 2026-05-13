<?php

// Traducciones personalizadas de Filament para Mundo Asiático
// Estas claves sobreescriben las traducciones por defecto del sistema.

return [
    'pages' => [
        'dashboard' => [
            'title' => 'Panel Principal',
        ],
    ],

    'layout' => [
        'actions' => [
            'logout' => [
                'label' => 'Cerrar Sesión',
            ],
            'open_sidebar' => [
                'label' => 'Abrir barra lateral',
            ],
            'close_sidebar' => [
                'label' => 'Cerrar barra lateral',
            ],
            'toggle_theme' => [
                'label' => 'Cambiar tema',
            ],
        ],
        'account' => [
            'profile' => 'Mi Perfil',
        ],
    ],

    'auth' => [
        'login' => [
            'title' => 'Iniciar Sesión',
            'heading' => 'Ingresar a Mundo Asiático',
            'buttons' => [
                'login' => [
                    'label' => 'Ingresar al Panel',
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
            ],
            'messages' => [
                'failed' => 'Las credenciales ingresadas son incorrectas.',
            ],
        ],
        'profile' => [
            'heading' => 'Mi Perfil',
            'form' => [
                'name' => [
                    'label' => 'Nombre Completo',
                ],
                'email' => [
                    'label' => 'Correo Electrónico',
                ],
                'current_password' => [
                    'label' => 'Contraseña Actual',
                ],
                'new_password' => [
                    'label' => 'Nueva Contraseña',
                ],
                'new_password_confirmation' => [
                    'label' => 'Confirmar Nueva Contraseña',
                ],
            ],
            'actions' => [
                'save' => [
                    'label' => 'Guardar Cambios',
                ],
            ],
            'notifications' => [
                'saved' => [
                    'title' => 'Perfil actualizado correctamente',
                ],
            ],
        ],
    ],
];
