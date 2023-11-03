<?php

return [
    'editPermission' => [
        'type' => 2,
        'description' => 'Edit any entity',
    ],
    'viewPermission' => [
        'type' => 2,
        'description' => 'View any entity',
    ],
    'admin' => [
        'type' => 1,
        'children' => [
            'editPermission',
            'viewPermission',
        ],
    ],
    'observer' => [
        'type' => 1,
        'children' => [
            'viewPermission',
        ],
    ],
];
