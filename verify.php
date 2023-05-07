<?php
session_start();

// Redirect user to login page if email session variable is not set
if(!isset($_SESSION['email'])){
    header("Location: cooklogin.php");
    exit();
}

// Check if verification code is submitted
if(isset($_POST['code'])){
    // Check if verification code matches
    $code = $_POST['code'];
    if ($code == $_SESSION['verification_code']) {
        // Verification successful, set email session variable
        $_SESSION['authenticated'] = true;

        // Redirect to home page
        header("Location: cookingpage.html");
        exit();
    } else {
        // Verification failed, show error message
        $error = "Invalid verification code";
    }
}
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

input[type="email"] {
  width: 100%;
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
<head>
    <title><c>Verification Code</c></title>
</head>
<body>
    <h1>Verification Code</h1>
  
    <form method="post" action="">
        <label>Verification Code:</label>
        <input type="text" name="code" required>
        <input type="hidden" name="email" value="<?php echo $_SESSION['email']; ?>">
        <button type="submit">Verify</button>
    </form>
</body>

<br>
    
    <button onclick="location.href='cookingpage.html'">Go back to cooking page</button>
</html>