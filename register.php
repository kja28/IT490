<?php

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
  // Get the form data
  $name = $_POST["name"];
  $email = $_POST["email"];
  $password = $_POST["password"];
  $gender = $_POST["gender"];
  $dietaryRestrictions = $_POST["dietary-restrictions"];

  // Connect to the MySQL database
  $servername = "localhost";
  $username = "username";
  $password = "password";
  $dbname = "mydatabase";

  $conn = mysqli_connect($servername, $username, $password, $dbname);

  // Check connection
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

  // Insert the data into the database
  $sql = "INSERT INTO users (name, email, password, gender, dietary_restrictions) VALUES ('$name', '$email', '$password', '$gender', '$dietaryRestrictions')";

  if (mysqli_query($conn, $sql)) {
    echo "New record created successfully";
  } else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  }

  // Close the database connection
  mysqli_close($conn);
}

?>
