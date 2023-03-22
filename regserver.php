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

$channel->queue_declare('profile_requests', false, true, false, false);

$channel2 = $connection->channel();

$channel2->exchange_declare('testExchange', 'topic', false, true, false);

$channel2->queue_declare('profile_response', false, true, false, false);



// Connect to MySQL

$mysqli = new mysqli('localhost', 'admin', 'Sc2aD3.456', 'Project');

if (!$mysqli)

{

  die ("Connection failed " . mysqli_connect_error());

}

// Callback function waits for a message from RabbitMQ and then decodes the message, checks mysql, and sends a message back

$callback = function($msg) use ($mysqli, $channel2) {

  // Extract the username and password from the message

  $data = json_decode($msg->body, true);

  $username = $mysqli->real_escape_string($data['username']);

  $password = $mysqli->real_escape_string($data['password']);

  $firstname = $mysqli->real_escape_string($data['firstname']);

  $lastname = $mysqli->real_escape_string($data['lastname']);

  $diet = $mysqli->real_escape_string($data['diet']);

 

  // Search for the user in the MySQL database

  $sql = "SELECT * FROM Users WHERE username = '$username'";

  $result = $mysqli->query($sql);



  // Check if the user exists

  if ($result->num_rows == 1) 

  {

    $response = "Username name already taken.";

  } 

  else 

  {

   $sqlinsert = "INSERT INTO Users (username, password, firstname, lastname, diet) VALUES ($username, $password, $firstname, $lastname, $diet);"

   $result = $mysqli->query($sqlinsert);

   if ($result)

   {

     $response = "success";

   }

   else

   {

     $response = "Failed to insert");

   }



  // Send a response back to RabbitMQ 

  $channel2->basic_publish(new AMQPMessage($response), 'testExchange', 'login_response', true);

};



// Consume the message so it doesn're read it

$channel->basic_consume('login_requests', '', false, true, false, false, $callback);



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
