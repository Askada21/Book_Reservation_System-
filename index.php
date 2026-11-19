<!--checking the connection-->

<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "auth.php";
require_once "db.php";

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

<nav>
    <a href="index.php">Home</a>
    <a href="search.php">Search Books</a>
    <a href="logout.php">Logout</a>
</nav>

</body>
</html>
