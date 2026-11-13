
<?php

session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username == "" || $password == "") {
        $error = "Please enter username and password.";
    }
    else {
        $stmt = $conn->prepare(
            "select Password from Users where Username = ?"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($hashed_password);

        if ($stmt->fetch() && password_verify($password, $hashed_password)) {
            session_regenerate_id(true);
            $_SESSION["username"] = $username;

            $stmt->close();
            header("Location: index.php");
            exit();
        }
        else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Library</title>
</head>
<body>

<h1>Login</h1>

<?php if ($error != ""): ?>
    <p style="color: red;">
        <?php echo htmlspecialchars($error); ?>
    </p>
<?php endif; ?>

<form action="login.php" method="post">

    <label>Username:</label>
    <input type="text" name="username" required>
    <br><br>

    <label>Password:</label>
    <input type="password" name="password" required>
    <br><br>

    <button type="submit">Login</button>

</form>

<p>Don't have an account?
    <a href="register.php">Register here</a>
</p>

</body>
</html>
