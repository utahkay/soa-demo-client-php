<?php

require_once __DIR__ . '/../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

const HOST = '54.149.117.183';
const USERNAME = 'craftsman';
const PASSWORD = 'utahsc2015';
const PIZZA_EXCHANGE = 'pizzarequested.v1';
const COUPON_EXCHANGE = 'couponissued.v1';
const COUPON_QUEUE = 'couponissued.v1.kay';

function orderPizza($channel, $coupon = '') {
	$instance = new stdClass;
	$instance->orderId = 'kay123';
	$instance->name = 'Kay';
	$instance->address = 'Lehi, UT';
	$instance->toppings = array('pepperoni', 'mushrooms');
	$instance->coupon = $coupon;
	
	$json = json_encode( (array)$instance );
	print $json . "\n";
	$msg = new AMQPMessage($json);
	$channel->basic_publish($msg, PIZZA_EXCHANGE);	
	
	return $instance->orderId;
}

function listenForCoupon($channel, $callback) {
	$channel->queue_declare(COUPON_QUEUE, false, false, false, false);
	$channel->queue_bind(COUPON_QUEUE, COUPON_EXCHANGE);
	
	echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";	
	$channel->basic_consume(COUPON_QUEUE, '', false, true, false, false, $callback);
	while(count($channel->callbacks)) {
		$channel->wait();
	}
}

function handleCouponIssued($channel, $msg, $order_id) {
	print "received: ".$msg->body."\n";
	$couponMsg = json_decode($msg->body, true);
	
	if ($couponMsg["orderId"] == $order_id) {
	  	print $couponMsg["coupon"]."\n";
		orderPizza($channel, $couponMsg["coupon"]);
	}	
}

$connection = new AMQPConnection(HOST, 5672, USERNAME, PASSWORD);
$channel = $connection->channel();

$order_id = orderPizza($channel);

listenForCoupon($channel, function ($msg) use ($channel, $order_id) {
	handleCouponIssued($channel, $msg, $order_id);
});

$channel->close();
$connection->close();

?>