<!DOCTYPE html>
<html lang = "en">
<head>
    <meta charset="utf-8" />
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        label {
            display: block;
            margin-bottom: 10px;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            margin-bottom: 20px;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

            input[type="submit"]:hover {
                background-color: #45a049;
            }
    </style>
</head>
<body>
<?php
//want to later put the CSS code and PHP code into seperate files, to make everything neater
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
    $response = $msg->body;
  };
  $channel2->basic_consume('login_response', '', false, true, false, false, $callback);
  while ($response === null) {
    $channel2->wait();
    if ($response !==null) {
    break;
    }

  }

  // check response message
  if ($response == 'success') {
    $error = "You logged in";
    // user is valid, start a session and redirect to home page
    // session_start();
    // $_SESSION['username'] = $username;
    // header("Location: home.php");
    // exit();
  } else {
    // user is invalid, display error message
    $error = "Invalid username or password.";
  }
}

// close the RabbitMQ connection
$channel->close();
$channel2->close();
$connection->close();

// display login form
?>

    <h1>Login Page</h1>
    <form method="post">
	<?php if(isset($error)) { echo '<p style="color:red">'.$response.'</p>'; } ?>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" placeholder="Enter your username">

        <label for="password">Password:</label>
        <input type="text" id="password" name="password" placeholder="Enter your password">

        <input type="submit" value="Login">
    </form>
</body>
</html>
