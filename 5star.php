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

$channel->queue_declare('5star_request', false, true, false, false);

$channel2 = $connection->channel();

$channel2->exchange_declare('testExchange', 'topic', false, true, false);

$channel2->queue_declare('5star_response', false, true, false, false);



// Connect to MySQL

$mysqli = new mysqli('localhost', 'admin', 'Sc2aD3.456', 'Project');



// Callback function waits for a message from RabbitMQ and then decodes the message, saves the rating and then ssends back the average

$callback = function($msg) use ($mysqli, $channel2) {

  // Extract the rating from the message

  // was using $data to set the decoded json message but switched until we use sessions

  $rating = $msg->body;

  

  // I'm setting the username to something in the database for now

  // and will submit a new entry into the ratings table with the same username.

  // It should get the user name from the session

  //$username = $mysqli->real_escape_string($data['username']);

  $username = 'hi';

  // commenting out because this is used for getting parts from json message

  // will put back json decoding when we use sessions

  //$rating = $channel->real_escape_string($data['rating']);

  

  // Saves the rating in mysql

  $find = "SELECT * FROM Users WHERE username = '$username'";

  $find2 = "SELECT * FROM RATINGS WHERE username = '$username'";

  $sql = "INSERT INTO RATINGS (username, ratings) VALUES ('$username', '$rating')";

  $sql2 = "UPDATE RATINGS SET ratings = '$rating' WHERE username = '$username'";

  

  // Check if the user exists, if yes, find them in the ratings table and insert or update their

  // rating accordingly

  $result = $mysqli->query($find);

  if ($result->num_rows == 1) 

  {

    $result = $mysqli->query($find2);

    if ($result->num_rows == 1) 

    {

      // This should run the sql2 query, I switched for now, since we dont have sessions yet

      if ($mysqli->query($sql) === FALSE) 

      {

        echo "Error saving message: " . $mysqli->error . "\n";

      }

    }

    else

    {

      if ($mysqli->query($sql) === FALSE) 

      {

        echo "Error saving message: " . $mysqli->error . "\n";

      }

    }

  } 

  else 

  {

    echo "Error saving message: " . $mysqli->error . "\n";

  }

  

  



  // Find all numbers in ratings table and send back the average

  $send = "SELECT AVG(ratings) AS avg_rating FROM RATINGS";

  $result = $mysqli->query($send);

  if ($result === FALSE) 

  {

    echo "Error saving message: " . $mysqli->error . "\n";

  }

  $calculated = $result->fetch_assoc();

  $avg = $calculated['avg_rating'];

  

  // Send a response back to RabbitMQ 

  $channel2->basic_publish(new AMQPMessage($avg), 'testExchange', '5star_response', true);

};



// Consume the message so it doesn're read it

$channel->basic_consume('5star_request', '', false, true, false, false, $callback);



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
