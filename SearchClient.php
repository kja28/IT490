<!DOCTYPE html>
<html>
    <head>
        <title>Search</title>
    </head>
    <body>
     
<form method="POST">
    <label for="search">search:</label>
    <input type="text" id="search" name="qury">
    <input type="submit" value="submit">
        </form>
    </body>
</html>
        
<?php
   
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;       


//The search form
//Resource: https://www.w3schools.com/html/html_forms.asp
//https://www.w3schools.com/php/php_forms.asp
        
// Connect to RabbitMQ
$connection = new AMQPStreamConnection('172.28.118.6', 5672, 'test', 'test', 'testHost');
$channel = $connection->channel();

        //if server request method }before closing the channels
$channel->exchange_declare('testExchange', 'topic', false, true, false);
$channel->queue_declare('search', false, true, false, false);
$channel2 = $connection->channel();
$channel2->exchange_declare('testExchange', 'topic', false, true, false);
$channel2->queue_declare('search', false, true, false, false);
if ($_SERVER['REQUEST_METHOD']==='POST'){
    

// Callback function waits for a message from RabbitMQ and then decodes the message, checks mysql, and sends a message back
$callback = function($msg) use ($mysqli, $channel2) {
    $data = json_decode($msg->body, true);
    $request = $data['request'];

    // Call the search function to make API call using cURL
    $response = search($request);

    // Send a response back to RabbitMQ 
    $channel2->basic_publish(new AMQPMessage($response), 'testExchange', 'meal_response', true);
};

// Consume the message so it doesn're read it
$channel->basic_consume('search', '', false, true, false, false, $callback);

// Wait for messages
while(true) {
    $channel->wait();
}
}
// Close connections when done
$channel->close();
$channel2->close();
$connection->close();


$mysqli->close();

?>
