<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load('.env');


$amqpCredentials = [
    'host' => getenv('HOST'),
    'port' => getenv('PORT'),
    'user' => getenv('USER'),
    'password' => getenv('PASSWORD'),
    'vhost' => getenv('VHOST')
];

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$amqpConnectSettings = [
    'exchanger' => getenv('EXCHNANGER'),
    'queue' => getenv('QUEUE')
];

$connection = new AMQPStreamConnection($amqpCredentials['host'], $amqpCredentials['port'], $amqpCredentials['user'], $amqpCredentials['password'], $amqpCredentials['vhost']);
$channel = $connection->channel();

$examplePayload = ['payload' => ['url' => 'http://example.com']];
$message = new AMQPMessage(json_encode($examplePayload));
$channel->batch_basic_publish($message, $amqpConnectSettings['exchanger']);
$channel->publish_batch();