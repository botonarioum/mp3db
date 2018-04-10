<?php

require __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$dotenv->load('.env');

$amqpConnectSettings = [
    'queue' => getenv('QUEUE_NAME')
];

$url = parse_url(getenv('CLOUDAMQP_URL'));
$connection = new AMQPConnection($url['host'], 5672, $url['user'], $url['pass'], substr($url['path'], 1));
$channel = $connection->channel();

$properties = array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT);
$examplePayload = json_encode(['payload' => ['url' => 'http://cdndl.zaycev.net/play/7059615/X84lQRZH3ZHl_e0BCTnCTKlOX0CXB5j-V8weU9Lm8gWINAOzv63N3q8bMrwHntoF5U5nDQcM7BVGdumCmSI-CKO5ntXYd7Ddy64CUYPvBvNxy91JBKdypjE3xAwR_YQKio6uCGqEaKPbNRO_SGwVCW9rVJTRQKbJt4z_Jz98Bgw9wxndE10_153BXUkqBdhDE9XkrA?dlKind=play&format=json']]);
$msg = new AMQPMessage($examplePayload, $properties);


$channel->basic_publish($msg, '', $amqpConnectSettings['queue']);

$channel->close();
$connection->close();