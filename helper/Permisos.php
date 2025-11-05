<?php
return [
    'PreguntasController' => [
        'index' => [UserRole::JUGADOR],
        'createQuestion' => [UserRole::JUGADOR],
    ],
    'UsuarioController' => [
        'perfil' => [UserRole::JUGADOR],
        'getCountryAndCity' => [UserRole::ADMIN],
    ],
    'CategoriaController' => [
        'getAll' => [UserRole::ADMIN],
    ],
    'AdminController' => [
        'index' => [UserRole::ADMIN],
    ],
    'EditorController' => [
        'index' => [UserRole::EDITOR],
    ],
    'HomeController' => [
        'index' => [UserRole::JUGADOR]
    ],
    'LoginController' => [
        'index' => [UserRole::JUGADOR, UserRole::ADMIN, UserRole::EDITOR],
        'logout' => [UserRole::JUGADOR, UserRole::ADMIN, UserRole::EDITOR],
    ],
    'RegisterController' => [
        'index' => [UserRole::JUGADOR]
    ],

];
