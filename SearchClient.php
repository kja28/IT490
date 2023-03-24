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
$channel->queue_declare('search_requests', false, true, false, false);
$channel2 = $connection->channel();
$channel2->exchange_declare('testExchange', 'topic', false, true, false);
$channel2->queue_declare('search_response', false, true, false, false);
if ($_SERVER['REQUEST_METHOD']==='POST'){
    

// Callback function waits for a message from RabbitMQ and then decodes the message, checks mysql, and sends a message back
$callback = function($msg) use ($mysqli, $channel2) {

    $data = json_decode($msg->body, true);
    $data = json_decode($msg->body, true);
    $id = $data[0]['id'];
    $title = $data[0]['title'];
    $ing = $data[0]['ingredients'];
    $ins = $data[0]['instructions'];
    $time = $data[0]['times'];
    $image = $data[0]['image'];

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
