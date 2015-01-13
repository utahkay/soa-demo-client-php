<?php

include __DIR__ . '/videlalvaro/php-amqplib/PhpAmqpLib/Connection/AMQPConnection.php';
include __DIR__ . '/videlalvaro/php-amqplib/PhpAmqpLib/Connection/AMQPStreamConnection.php';

use PhpAmqpLib\Connection\AMQPConnection;

$connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

?>

