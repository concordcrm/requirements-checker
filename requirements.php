<?php

return [
    'core' => [
        'minPhpVersion' => '8.1',
    ],

    'requirements' => [
        'php' => [
            'bcmath',
            'ctype',
            'mbstring',
            'openssl',
            'pdo',
            'tokenizer',
            'cURL',
            'iconv',
            'gd',
            'fileinfo',
            'dom',
        ],

        'apache' => [
            'mod_rewrite',
        ],

        'functions' => [
            'symlink',
            'proc_open',
            'proc_close',
        ],

        'recommended' => [
            'php' => [
                'imap',
                'zip',
            ],
        ],
    ],
];
