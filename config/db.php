<?php

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:host=localhost;port=5432;dbname=vksproject;',
    'username' => 'postgres',
    'password' => 'root',
    'charset' => 'utf8',
    'tablePrefix' => 'vks_',
    'schemaMap' => [
        'pgsql'=> [
            'class'=>'yii\db\pgsql\Schema',
            'defaultSchema' => 'public'
        ]
    ],
];