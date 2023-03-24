#!/usr/bin/php

<?php

require_once __DIR__ . '/vendor/autoload.php';



use PhpAmqpLib\Connection\AMQPStreamConnection;

use PhpAmqpLib\Message\AMQPMessage;



// Connect to 2 channels to set one to send and one to recieve from RabbitMQ

// Guest account can be accessed via local host, when connecting from outside, use the test user

// declares, the queue and queue to send to(parameters need to be the same whenever try to use in another code)

$connection = new AMQPStreamConnection('172.28.118.6', 5672, 'test', 'test', 'testHost');

$channel = $connection->channel();

$channel->exchange_declare('testExchange', 'topic', false, true, false);

$channel->queue_declare('meal_requests', false, true, false, false);

$channel2 = $connection->channel();

$channel2->exchange_declare('testExchange', 'topic', false, true, false);

$channel2->queue_declare('meal_response', false, true, false, false);



// Connect to MySQL

$mysqli = new mysqli('localhost', 'admin', 'Sc2aD3.456', 'Project');



// Callback function waits for a message from RabbitMQ and then decodes the message, checks mysql, and sends a message back

$callback = function($msg) use ($mysqli, $channel2) 

{

  // Extract the username and password from the message

  $data = json_decode($msg->body, true);

  $request = $data['request'];

  $query = "peppers";

  



	$curl = curl_init();



         curl_setopt_array($curl, [
        CURLOPT_URL =>  "https://yummly2.p.rapidapi.com/feeds/search?&q=%7B$query%7D&maxResult=10&start=0",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 1,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Host: yummly2.p.rapidapi.com",
            "X-RapidAPI-Key: f3e58460f7msh59bd4ab7d8245aap10982bjsn0b20d69d0da7"
        ],
    ]);
  $response = curl_exec($curl);

  // $err = curl_error($curl);



  curl_close($curl);



  // echo $response;

  

  

  // Send a response back to RabbitMQ 

  $channel2->basic_publish(new AMQPMessage($response), 'testExchange', 'meal_response', true);

};



// Consume the message so it doesn're read it

$channel->basic_consume('meal_requests', '', false, true, false, false, $callback);



// Wait for messages

while(true) 

{

	$channel->wait();

}



// Close connections when done

$channel->close();

$channel2->close();

$connection->close();

$mysqli->close();

?>
