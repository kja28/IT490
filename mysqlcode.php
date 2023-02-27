#!/usr/bin/php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// Connect to RabbitMQ
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('login_requests', false, true, false, false);

// Connect to MySQL
$mysqli = new mysqli('localhost', 'admin', 'Sc2aD3.456', 'Project');

// Wait for a message from RabbitMQ
$callback = function($msg) use ($mysqli, $channel) {
  // Extract the username and password from the message
  $data = json_decode($msg->body, true);
  $username = $mysqli->real_escape_string($data['username']);
  $password = $mysqli->real_escape_string($data['password']);

  // Search for the user in the MySQL database
  $sql = "SELECT * FROM Users WHERE username = '$username' AND password = '$password'";
  $result = $mysqli->query($sql);

  // Check if the user exists
  if ($result->num_rows == 1) {
    $response = "success";
  } else {
    $response = "Invalid username or password";
  }

  // Send a response back to RabbitMQ
  $channel->basic_publish(new AMQPMessage($response), '', 'login_response');
  $channel->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_consume('login_requests', '', false, false, false, false, $callback);

// Wait for messages
while(count($channel->callbacks)) {
  $channel->wait();
}

// Close connections
$channel->close();
$connection->close();
$mysqli->close();
