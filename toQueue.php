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
    'exchanger' => getenv('EXCHANGER'),
    'queue' => getenv('QUEUE')
];

$connection = new AMQPStreamConnection($amqpCredentials['host'], $amqpCredentials['port'], $amqpCredentials['user'], $amqpCredentials['password'], $amqpCredentials['vhost']);
$channel = $connection->channel();

$examplePayload = ['payload' => ['url' => 'http://cdndl.zaycev.net/play/7059615/X84lQRZH3ZHl_e0BCTnCTKlOX0CXB5j-V8weU9Lm8gWINAOzv63N3q8bMrwHntoF5U5nDQcM7BVGdumCmSI-CKO5ntXYd7Ddy64CUYPvBvNxy91JBKdypjE3xAwR_YQKio6uCGqEaKPbNRO_SGwVCW9rVJTRQKbJt4z_Jz98Bgw9wxndE10_153BXUkqBdhDE9XkrA?dlKind=play&format=json']];
$message = new AMQPMessage(json_encode($examplePayload));

$channel->batch_basic_publish($message, $amqpConnectSettings['exchanger']);
$channel->publish_batch();

$channel->close();
$connection->close();