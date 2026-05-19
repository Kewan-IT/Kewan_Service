<?php
return [
    'max_size'       => (int) ($_ENV['UPLOAD_MAX_SIZE'] ?? 5242880),
    'allowed_images' => ['image/jpeg', 'image/png', 'image/webp'],
    'allowed_docs'   => ['application/pdf'],
    'paths' => [
        'funcionarios_fotos' => 'funcionarios/fotos/',
        'funcionarios_docs'  => 'funcionarios/docs/',
        'receitas'           => 'receitas/',
        'produtos'           => 'produtos/',
    ],
];
