

<?php

//want to later put the CSS code and PHP code into seperate files, to make everything neater

require_once __DIR__ . '/vendor/autoload.php';



use PhpAmqpLib\Connection\AMQPStreamConnection;

use PhpAmqpLib\Message\AMQPMessage;



$rating = $_POST['rating'];



// establish a RabbitMQ connection

// Use test as the vhost, can only connect to gues via local host

$connection = new AMQPStreamConnection('172.28.118.6', 5672, 'test', 'test', 'testHost');

$channel = $connection->channel();

$channel2 = $connection->channel();



//Created 2 channels and specified the exchange and queues for them to send and recieve messages

$channel->exchange_declare('testExchange', 'topic', false, true, false);

$channel->queue_declare('5star_request', false, true, false, false);

$channel2->exchange_declare('testExchange', 'topic', false, true, false);

$channel2->queue_declare('5star_response', false, true, false, false);



$channel->basic_publish(new AMQPMessage($rating), 'testExchange', '5star_request', true);



$avgRating = null;

$done = false;

$callback = function ($msg) use ($connection, &$avgRating, &$done)

{

  $avgRating = $msg->body;

  $done = true;

  echo $avgRating;

};



// Send the rating to the queue and wait for a response





$channel2->basic_consume('5star_response', '', false, true, false, false, $callback);



$timeout = 10; 

$start_time = time();



while (true) 

{

    $channel2->wait(null, false, $timeout);

    if($done)

    {

      break;

    }

    if ($avgRating !==null) 

    {



      break;



    }

    if (time() - $start_time > $timeout) 

    {

        break;

    }

}





// close the RabbitMQ connection

$channel->close();

$channel2->close();

$connection->close();



?>
