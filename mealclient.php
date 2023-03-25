<?php
// start the session
session_start();

require_once __DIR__ . '/vendor/autoload.php';



use PhpAmqpLib\Connection\AMQPStreamConnection;

use PhpAmqpLib\Message\AMQPMessage;



// establish a RabbitMQ connection

// Use test as the vhost, can only connect to gues via local host

$connection = new AMQPStreamConnection('172.28.118.6', 5672, 'test', 'test', 'testHost');

$channel = $connection->channel();

$channel2 = $connection->channel();



//Created 2 channels and specified the exchange and queues for them to send and recieve messages

$channel->exchange_declare('testExchange', 'topic', false, true, false);

$channel->queue_declare('meal_requests', false, true, false, false);

$channel2->exchange_declare('testExchange', 'topic', false, true, false);

$channel2->queue_declare('meal_response', false, true, false, false);

	

$request = "hi";

 

$meal_request = array(

  'request' => $request

);

$msg = new AMQPMessage(json_encode($meal_request));



// sends the message out to rabbitmq from channel and waits for a response on channel2

$channel->basic_publish($msg, 'testExchange', 'meal_requests', true);



$response = null;

$callback = function ($msg) use (&$response) 

{

  $data = json_decode($msg->body, true);

  $id = $data[0]['id'];

  $title = $data[0]['title'];

  $ing = $data[0]['ingrediants'];

  $ins = $data[0]['instructions'];

  $time = $data[0]['times'];

  $image = $data[0]['image'];
	
  $response = true;

// Display the message on the webpage

    echo "<h1>$title</h1>";

    echo "<h2>Ingredients:</h2>";

    echo "<ul>";

    foreach ($ing as $x) {

        echo "<li> $x </li>";

    }

    echo "</ul>";

    echo "<h2>Instructions:</h2>";

    echo "<ol>";

    foreach ($ins as $y) {

        echo "<li> $y[text] </li>";

    }

    echo "</ol>";

};

$channel2->basic_consume('meal_response', '', false, true, false, false, $callback);



while ($response === null) {

  $channel2->wait();

  if ($response !==null) {

    break;

  }

}





// close the RabbitMQ connection

$channel->close();

$channel2->close();

$connection->close();


?>
