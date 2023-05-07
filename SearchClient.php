<!DOCTYPE html>
<html>
    <head>
        <title>Search</title>
    </head>
    <body>
     
<form method="POST">
    <label for="search">search:</label>
    <input type="text" id="search" name="search">
    <input type="submit" value="submit">
        </form>
    </body>
</html>
        
<?php
// start the session
session_start();
   
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
$channel->queue_declare('search_requests', false, true, false, false);
$channel2 = $connection->channel();
$channel2->exchange_declare('testExchange', 'topic', false, true, false);
$channel2->queue_declare('search_response', false, true, false, false);
if ($_SERVER['REQUEST_METHOD']==='POST'){
    
$search = $_POST['search'];
$search_request = array(
    'search' => $search,
  );
    $response = null;
  $msg = new AMQPMessage(json_encode($search_request));

  $channel2->basic_publish($msg, 'testExchange', 'search_requests', true);

// Callback function waits for a message from RabbitMQ and then decodes the message, checks mysql, and sends a message back
$callback = function($msg) use ($channel2, &$response) {
    $data = json_decode($msg->body, true);
    foreach ($data['hits'] as $hit) {
        $recipe = $hit['recipe'];
        $recipe_url = $recipe['url'];
        echo $recipe_url . "<br>";
    }
    $response = "hi";
};

// Consume the message so it doesn're read it
$channel->basic_consume('search_response', '', false, true, false, false, $callback);

// Wait for messages
while(true) {
    $channel->wait();
    if ($response !== null)
    {
        break;
    }
}
}
// Close connections when done
$channel->close();
$channel2->close();
$connection->close();


$mysqli->close();

?>