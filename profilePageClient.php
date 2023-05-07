<?php
session_start();
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html>
<head>
	<title>User Registration Form</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<style>
	/* Global Styles */

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  background-color: #333;
  font-family: 'Roboto', sans-serif;
  font-size: 16px;
  color: #ffffff;
}

h2 {
  text-align: center;
  margin: 40px 0;
}

h1 {
  text-align: center;
  margin: 40px 0;
}

button {
  font-family: 'Roboto', sans-serif;
  font-size: 20px;
  background-color: #4CAF50;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 12px 20px;
  cursor: pointer;
  display: block;
margin: 0 auto;
}

button:hover {
  background-color: #45a049;
}

/* Popup Styles */

.popup {
  display: none;
  position: fixed;
  z-index: 1;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0, 0, 0, 0.4);
}

.popup-content {
  background-color: #333;
  margin: 100px auto;
  padding: 20px;
  border-radius: 4px;
  width: 60%;
  max-width: 600px;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
  position: relative;
}

.close {
  position: absolute;
  top: 10px;
  right: 10px;
  font-size: 24px;
  font-weight: bold;
  color: #888;
  cursor: pointer;
}

.close:hover {
  color: #000;
}

form {
  margin-top: 40px;
  display: flex;
  flex-direction: column;
}

label {
  font-size: 20px;
  font-weight: bold;
  margin-bottom: 5px;
}

input[type="text"],
input[type="password"],
select {
  padding: 12px 20px;
  margin-bottom: 10px;
  border: none;
  border-radius: 4px;
  font-size: 16px;
}

input[type="text"],
input[type="password"] {
  background-color: #f2f2f2;
}

input[type="text"]:focus,
input[type="password"]:focus {
  background-color: #ddd;
}

select {
  background-color: #f2f2f2;
}

select:focus {
  background-color: #ddd;
}

input[type="submit"] {
  background-color: #4CAF50;
  color: #fff;
  border: none;
  border-radius: 4px;
  padding: 12px 20px;
  font-size: 20px;
  cursor: pointer;
}

input[type="submit"]:hover {
  background-color: #45a049;
}

.error {
  color: red;
  font-weight: bold;
  margin-bottom: 10px;
}
.register-btn {
    display: block;
    margin: 0 auto;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 12px 24px;
    font-size: 20px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.register-btn:hover {
    background-color: #45a049;
}

	</style>
</head>

<body>

    <h1>Profile Page</h1>

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
$channel->queue_declare('page_requests', false, true, false, false);
$channel2->exchange_declare('testExchange', 'topic', false, true, false);
$channel2->queue_declare('page_response', false, true, false, false);


  $profile_request = array(
    'username' => $username,
  );

  $msg = new AMQPMessage(json_encode($profile_request));

  // sends the message out to rabbitmq from channel and waits for a response on channel2
  $channel->basic_publish($msg, 'testExchange', 'page_requests', true);

  $response = null;
  $callback = function ($msg) use ($connection, &$response) 
  {
    $data = json_decode($msg->body, true);
    $user = $data['username'];
    $first = $data['firstname'];
    $last = $data['lastname'];
    $pass = $data['password'];
    $diet = $data['diet'];

 // Display the message on the webpage

    echo "<p>Username: " . $user . "</p>";
    echo "<p>First Name: " . $first . "</p>";
    echo "<p>Last Name: " . $last . "</p>";
    echo "<p>Dietary Restrictions: " . $diet . "</p>";

    $response = "done";
  };

  $channel2->basic_consume('page_response', '', false, true, false, false, $callback);
  while ($response === null) 
  {
    $channel2->wait();
    if ($response !==null) 
    {
    break;
    }
  }

// close the RabbitMQ connection
$channel->close();
$channel2->close();
$connection->close();
?>
    <button onclick="location.href='cookingpage.html'">Go back to cooking page</button>
</body>
</html>