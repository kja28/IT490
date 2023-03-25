<!DOCTYPE html>
<html>
<head>
    <title>Community Discussion Posts</title>
    <style>
        body {
            background-color: #333;
            color: #fff;
        }

        h1, h2, p, label {
            margin: 0;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 18px;
            margin-bottom: 5px;
        }

        input[type="text"], textarea {
            border: none;
            border-radius: 5px;
            font-size: 16px;
            padding: 10px;
            margin-bottom: 10px;
            width: 100%;
        }

        input[type="submit"] {
            background-color: #f44336;
            border: none;
            border-radius: 5px;
            color: #fff;
            cursor: pointer;
            font-size: 16px;
            padding: 10px 20px;
        }

        input[type="submit"]:hover {
            background-color: #ff6659;
        }

        hr {
            border: none;
            border-top: 1px solid #fff;
            margin: 20px 0;
        }
        button {
  background-color: #4CAF50;
  border: none;
  border-radius: 5px;
  color: #fff;
  cursor: pointer;
  font-size: 16px;
  padding: 10px 20px;
  margin-top: 20px;
  display: block;
margin: 0 auto;
}

button:hover {
  background-color: #ff6659;
}
    </style>
</head>
<body>
    

    <h1>Community Discussion Posts</h1>

    <!-- HTML form to submit new posts -->
    <form action="add_post.php" method="POST">
        <label for="title">Title:</label>
        <input type="text" id="title" name="title">
        
        <label for="content">Content:</label>
        <textarea id="content" name="content"></textarea>
        
        <label for="author">Author:</label>
        <input type="text" id="author" name="author">
        
        <input type="submit" value="Submit">
    </form>

    <hr>

    <?php
// start the session
session_start();

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
$channel->queue_declare('com_requests', false, true, false, false);
$channel2->exchange_declare('testExchange', 'topic', false, true, false);
$channel2->queue_declare('com_response', false, true, false, false);

if ($_SERVER["REQUEST_METHOD"] == "POST") 
{
  // gets the username and password from what the user types in and encodes it as a json
  // For security purposes the password should be hashed first and store only hashed passwords in mysql	
  $post = $_POST["content"];
  $time = date("Y-m-d H:i:s");
  $author = $_POST["author"];
  $title = $_POST["title"];
  $post_request = array(
    'post' => $post,
    'time' => $time,
    'author' => $author,
    'title' => $title
  );
}
else
{
  $post_request = array(
    'request'=> "hi"
  );
}
  $msg = new AMQPMessage(json_encode($post_request));
  // sends the message out to rabbitmq from channel and waits for a response on channel2
  $channel->basic_publish($msg, 'testExchange', 'com_requests', true);
  $response = null;
  $callback = function ($msg) use ($connection, &$response) 
  {
    $response = $msg->body;
  };
  $channel2->basic_consume('com_response', '', false, true, false, false, $callback);

  while ($response === null) 
    {
      $channel2->wait();
      if ($response !==null) 
      {
        break;
      }
    }
  // check response message
  if ($response !== null) 
  {
    echo "<p> $response </p>";
  } 
  else 
  {
  }

// close the RabbitMQ connection
$channel->close();
$channel2->close();
$connection->close();



// display login form

?>
    <br>
    <br>
    <button onclick="location.href='cookingpage.html'">Go back to cooking page</button>
</body>
</html>
