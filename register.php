
<?php
require_once "db.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Library</title>
</head>
<body>

<h1>Create an Account</h1>

<form action="register.php" method="post">

    <label>Username:</label>
    <input type="text" name="username" required>
    <br><br>

    <label>Password:</label>
    <input type="password" name="password"
           minlength="6" maxlength="6" required>
    <br><br>

    <label>Confirm Password:</label>
    <input type="password" name="confirm_password" required>
    <br><br>

    <label>First Name:</label>
    <input type="text" name="firstname" required>
    <br><br>

    <label>Surname:</label>
    <input type="text" name="surname" required>
    <br><br>

    <label>Address Line 1:</label>
    <input type="text" name="address1" required>
    <br><br>

    <label>Address Line 2:</label>
    <input type="text" name="address2">
    <br><br>

    <label>City:</label>
    <input type="text" name="city" required>
    <br><br>

    <label>Telephone:</label>
    <input type="tel" name="telephone">
    <br><br>

    <label>Mobile:</label>
    <input type="tel" name="mobile"
           pattern="[0-9]{10}" maxlength="10" required>
    <br><br>

    <button type="submit">Register</button>

</form>

</body>
</html>
