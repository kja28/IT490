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
$channel->queue_declare('com_requests', false, true, false, false);
$channel2 = $connection->channel();
$channel2->exchange_declare('testExchange', 'topic', false, true, false);
$channel2->queue_declare('com_response', false, true, false, false);

// Connect to MySQL
$mysqli = new mysqli('localhost', 'admin', 'Sc2aD3.456', 'Project');

// Callback function waits for a message from RabbitMQ and then decodes the message, checks mysql, and sends a message back
$post = null;
$callback = function($msg) use ($mysqli, $channel2) 
{
  // Extract the username and password from the message
  $data = json_decode($msg->body, true);
  $username = "first";

  if (isset($data['post']))
  {
    $username = $mysqli->real_escape_string($data['author']);
    $post = $mysqli->real_escape_string($data['post']);
    $time = $mysqli->real_escape_string($data['time']);
    $title = $mysqli->real_escape_string($data['title']);  
  }
	
  // Search for the user in the MySQL database
  $sql = 'SELECT * FROM Messages ORDER BY timestamp DESC LIMIT 1';
  $result = $mysqli->query($sql);

  // Check if the user exists
  if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
    $response = $row['messages'];
  } 
  else 
  {
    $response = "empty";
  }

  // Send a response back to RabbitMQ 
  $channel2->basic_publish(new AMQPMessage($response), 'testExchange', 'com_response', true);
  
  if ($post !== null)
  {
    $sql2 = "Insert INTO Messages (username, timestamp, messages) Values ('$username', '$time', '$post')";
    $check = $mysqli->query($sql2);
  }
};

// Consume the message so it doesn're read it
$channel->basic_consume('com_requests', '', false, true, false, false, $callback);

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
