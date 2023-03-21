<?php

//want to later put the CSS code and PHP code into seperate files, to make everything neater

require_once __DIR__ . '/vendor/autoload.php';



use PhpAmqpLib\Connection\AMQPStreamConnection;

use PhpAmqpLib\Message\AMQPMessage;



$rating = $_POST['rating'];



// establish a RabbitMQ connection

// Use test as the vhost, can only connect to gues via local host

$connection = new AMQPStreamConnection('172.28.118.6', 5672, 'test', 'test', 'testHost');

$channel = $connection->channel();

$channel2 = $connection->channel();



//Created 2 channels and specified the exchange and queues for them to send and recieve messages

$channel->exchange_declare('testExchange', 'topic', false, true, false);

$channel->queue_declare('5star_request', false, true, false, false);

$channel2->exchange_declare('testExchange', 'topic', false, true, false);

$channel2->queue_declare('5star_response', false, true, false, false);



$callback = function ($msg) use ($connection) {

  $response = json_decode($msg->body);

  $data = $response->average_rating;

};



// Send the rating to the queue and wait for a response



$channel->basic_publish(new AMQPMessage($rating), 'testExchange', '5star_request', true);

$channel2->basic_consume($queue, '', false, true, false, false, $callback);



while (count($channel2->callbacks)) {

  $channel2->wait();

}



// close the RabbitMQ connection

$channel->close();

$channel2->close();

$connection->close();



// display login form

?>
