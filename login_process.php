<?php
session_start();

// Connect to database
$servername = "sql1.njit.edu";
$username = "pk355";
$password = "";
$dbname = "pk355";

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form is submitted
if(isset($_POST['email']) && isset($_POST['password'])){
    // Store data in variables
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if email and password are correct
    $sql = "SELECT * FROM lusers WHERE email='$email' AND password='$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        // Generate and store verification code
        $code = rand(100000, 999999);
        $_SESSION['verification_code'] = $code;
        $_SESSION['email'] = $email;

        // Send verification code to user's email
        $to = $email;
        $subject = "Verification Code for Login";
        $message = "Your verification code is: $code";
        $headers = "From: PK355@NJIT.EDU"; // Replace with your own email address
        if(mail($to, $subject, $message, $headers)){
            // Redirect to verification page
            header("Location: verify.php");
            exit();
        } else {
            // Error sending email, show error message
            echo "Error sending email";
        }
    } else {
        // Login failed, show error message
        echo "Invalid email or password";
    }
}

// Close database connection
mysqli_close($conn);
?>
