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

$channel->queue_declare('login_requests', false, true, false, false);

$channel2 = $connection->channel();

$channel2->exchange_declare('testExchange', 'topic', false, true, false);

$channel2->queue_declare('login_response', false, true, false, false);



// Connect to MySQL

$mysqli = new mysqli('localhost', 'admin', 'Sc2aD3.456', 'Project');



// Callback function waits for a message from RabbitMQ and then decodes the message, checks mysql, and sends a message back

$callback = function($msg) use ($mysqli, $channel2) {

  // Extract the username and password from the message

  $data = json_decode($msg->body, true);

  $username = $mysqli->real_escape_string($data['username']);

  $password = $mysqli->real_escape_string($data['password']);
	

  // Search for the user in the MySQL database

  $sql = "SELECT password FROM Users WHERE username = '$username'";

  $result = $mysqli->query($sql);



  // Check if the user exists

  if ($result->num_rows == 1) 
  {
	
    $save = $result->fetch_assoc();

    $check = $save["password"];
    
    if (password_verify($password, $check))
    {
      $code = rand(100000, 999999);

      $response = "success";
    
      $find = "SELECT email FROM Users WHERE username = '$username'"	
	    
      $result2 = $mysqli->query($find);	
	  
      $found = $result->fetch_assoc();
  
      $email = $found["email"];
	  
      $info = array( 'response' => $response,

                     'code' => $code,

                     'email' => $email
                   );
	  
      $send = new AMQPMessage(json_encode($info));
    }
    
    else
	    
    {
      $response = "Invalid username or password";
	  
      $info = array( 'response' => $response,

                     'code' => 0,

                     'email' => 0
                    );
	  
      $send = new AMQPMessage(json_encode($info));
    }
	  
    

  } 
  else 
  {

    $response = "Invalid username or password";
	  
    $info = array( 'response' => $response,

                   'code' => 0,

                   'email' => 0
                  );
	  
    $send = new AMQPMessage(json_encode($info));

  }



  // Send a response back to RabbitMQ 

  $channel2->basic_publish($send, 'testExchange', 'login_response', true);


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
