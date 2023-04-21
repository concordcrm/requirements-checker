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
            'tmpfile',
            'ignore_user_abort',
            'fpassthru',
            'highlight_file',
        ],

        'recommended' => [
            'php' => [
                'imap',
                'zip',
            ],

            'functions'=>[
                'proc_open',
                'proc_close',
            ]
        ],
    ],
];