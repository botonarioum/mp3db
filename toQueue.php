<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load('.env');


$credentials = [
    'host' => getenv('HOST'),
    'port' => getenv('PORT'),
    'user' => getenv('USER'),
    'password' => getenv('PASSWORD'),
    'vhost' => getenv('VHOST')
];

