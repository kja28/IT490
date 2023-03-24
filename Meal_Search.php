#!/usr/bin/php

<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// The api search 
function search($queryx){
    $query = urlencode($queryx);
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL =>  "https://yummly2.p.rapidapi.com/feeds/auto-complete?q={$query}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "X-RapidAPI-Host: yummly2.p.rapidapi.com",
            "X-RapidAPI-Key: f3e58460f7msh59bd4ab7d8245aap10982bjsn0b20d69d0da7"
        ],
    ]);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

//The search form

if(isset($_GET['search'])){
    $query = $_GET['search'];
    
    $response = search($query);
    echo $response;
} else{
    echo '<form method= "GET">';
    echo '<label for="search"> Search Here:</label>';
    echo '<input type="text" name="search" id="search">';
    echo '<input type ="submit" value="Search">';
    echo '</form>';
}

        

// Connect to RabbitMQ
$connection = new AMQPStreamConnection('172.28.118.6', 5672, 'test', 'test', 'testHost');
$channel = $connection->channel();

$channel->exchange_declare('testExchange', 'topic', false, true, false);
$channel->queue_declare('meal_requests', false, true, false, false);
$channel2 = $connection->channel();
$channel2->exchange_declare('testExchange', 'topic', false, true, false);
$channel2->queue_declare('meal_response', false, true, false, false);

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
$channel->basic_consume('meal_requests', '', false, true, false, false, $callback);

// Wait for messages
while(true) {
    $channel->wait();
}

// Close connections when done
$channel->close();
$channel2->close();
$connection->close();
$mysqli->close();

?>
