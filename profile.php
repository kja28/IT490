<?php
		// Connect to the MySQL database
		$mysqli = new mysqli("localhost", "username", "password", "database");

		// Check for connection errors
		if ($mysqli->connect_errno) {
			echo "Failed to connect to MySQL: " . $mysqli->connect_error;
			exit();
		}

		// Retrieve the user's profile information from the database
		$username = $_SESSION['username'];
		$result = $mysqli->query("SELECT * FROM users WHERE username='$username'");
		$row = $result->fetch_assoc();

		// Display the user's profile information on the page
		echo "<p>Username: " . $row['username'] . "</p>";
		echo "<p>First Name: " . $row['first_name'] . "</p>";
		echo "<p>Last Name: " . $row['last_name'] . "</p>";
		echo "<p>Dietary Restrictions: " . $row['dietary_restrictions'] . "</p>";

		// Close the MySQL connection
		$mysqli->close();
	?>