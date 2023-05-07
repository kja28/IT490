<?php
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
$channel->queue_declare('profile_requests', false, true, false, false);
$channel2->exchange_declare('testExchange', 'topic', false, true, false);
$channel2->queue_declare('profile_response', false, true, false, false);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // gets the username and password from what the user types in and encodes it as a json
  // For security purposes the password should be hashed first and store only hashed passwords in mysql	
  $username = $_POST["username"];
  $firstname = $_POST["firstname"];
  $lastname = $_POST["lastname"];
  $password = $_POST["password"];
  $diet = $_POST["dietary"];
  $email = $_POST["email"];


  $profile_request = array(
    'username' => $username,
    'firstname' => $firstname,
    'lastname' => $lastname,
    'password' => $password,
    'diet' => $diet, 
	'email' => $email
  );
  $msg = new AMQPMessage(json_encode($profile_request));

  // sends the message out to rabbitmq from channel and waits for a response on channel2
  $channel->basic_publish($msg, 'testExchange', 'profile_requests', true);

  $response = null;
  $callback = function ($msg) use (&$response) {
    $response = $msg->body;
  };
  $channel2->basic_consume('profile_response', '', false, true, false, false, $callback);
  while ($response === null) {
    $channel2->wait();
    if ($response !==null) {
    break;
    }

  }

  // check response message
  if ($response == 'success') {
      header('Location: loginpage.html');
    exit();
  } else {
    // user is invalid, display error message
    header('Location: registerPage.html');
  }
  
}

// close the RabbitMQ connection
$channel->close();
$channel2->close();
$connection->close();
?>
