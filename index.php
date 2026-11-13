<!--checking the connection-->

<?php

session_start();
require_once "db.php";

if (!isset($_SESSION["username"])) {
    header("Location: login.php");
    exit();
}

$username = htmlspecialchars($_SESSION["username"]);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Library Home</title>
</head>
<body>

<h1>My Book Reservation System</h1>

<p>Welcome, <?php echo $username; ?>!</p>

<p>You are successfully logged in.</p>

<a href="logout.php">Logout</a>

</body>
</html>

