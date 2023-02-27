<!DOCTYPE html>
<html lang = "en">
        <head>
                <meta charset=utf-8" />
                <title>login page</title>
        </head>
        <body>
                <?php
                      require_once __DIR__ '/vender/autoload.php';
                      use PhpAmqpLib\Connection\AMQPStreamConnection;
                      $username = $_POST['user_box'];
                      $password = $_POST['pass_box'];
                      $query = "SELECT * FROM `Users` WHERE `Usernames` = \"$username\""

                      if (isset($_POST['submit']))
                      {
                            $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
                            $channel = $connection->channel();
                            };
                ?>
                <form method = "post" class = "base">
                        <h1>The Login Page</h1>
                        <label for="login">Login:</label><br>
                        <input type="text" 
                               id="fname"
                               placeholder = "Enter Username"><br>
                        <label for="Password">Password:</label><br>
                        <input type="password" 
                               id="Password"
                               placeholder = "Enter Password">
                        <input type="submit"
                               value="Submit"
                               name = "submit"
                               id = "button1">
                </form>

        </body>
</html>

