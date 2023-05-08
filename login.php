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
$channel->queue_declare('login_requests', false, true, false, false);
$channel2->exchange_declare('testExchange', 'topic', false, true, false);
$channel2->queue_declare('login_response', false, true, false, false);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // gets the username and password from what the user types in and encodes it as a json
  // For security purposes the password should be hashed first and store only hashed passwords in mysql	
  $username = $_POST["username"];
  $password = $_POST["password"];


  $login_request = array(
    'username' => $username,
    'password' => $password
    
  );
  $msg = new AMQPMessage(json_encode($login_request));

  // sends the message out to rabbitmq from channel and waits for a response on channel2
  $channel->basic_publish($msg, 'testExchange', 'login_requests', true);

  $response = null;
  $callback = function ($msg) use (&$response) {
    $data = json_decode($msg->body, true);

    $response = $data['response'];

    $code = $data['code'];

    $email = $data['email'];

  };
  $channel2->basic_consume('login_response', '', false, true, false, false, $callback);
  while ($response === null) {
    $channel2->wait();
    if ($response !==null) {
    break;
    }

  }

  // check response message
  if ($response == 'success') 
  {
    // user is valid, start a session and redirect to home page
    session_start();
    $_SESSION['username'] = $username;
    $_SESSION['code'] = $code;
    $_SESSION['email'] = $email;

        // Send verification code to user's email
        $to = $email;
        $subject = "Verification Code for Login";
        $message = "Your verification code is: $code";
        $headers = "From: PK355@NJIT.EDU"; // Replace with your own email address
        if(mail($to, $subject, $message, $headers)){
            // Redirect to verification page
            header("Location: verify.php");
            exit();
      } else {
            // Error sending email, show error message
            echo "Error sending email";
        }
    } else {
        // Login failed, show error message
        echo "Invalid email or password";
    }
}

// close the RabbitMQ connection
$channel->close();
$channel2->close();
$connection->close();

// start the session
session_start();

// display login form
?>
