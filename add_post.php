<?php
// Connect to MySQL database
$conn = mysqli_connect("localhost", "username", "password", "database_name");

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve the discussion posts from the MySQL database
$sql = "SELECT * FROM posts ORDER BY post_id DESC";
$result = mysqli_query($conn, $sql);

// Display the discussion posts on the page
while ($row = mysqli_fetch_assoc($result)) {
    echo "<h2>" . $row["title"] . "</h2>";
    echo "<p>" . $row["content"] . "</p>";
    echo "<p>Posted by " . $row["author"] . " on " . $row["post_date"] . "</p>";
    echo "<hr>";
}

// Close the MySQL connection
mysqli_close($conn);
?>
