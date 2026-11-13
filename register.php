

<?php

require_once "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
    $firstname = trim($_POST["firstname"] ?? "");
    $surname = trim($_POST["surname"] ?? "");
    $address1 = trim($_POST["address1"] ?? "");
    $address2 = trim($_POST["address2"] ?? "");
    $city = trim($_POST["city"] ?? "");
    $telephone = trim($_POST["telephone"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");

    if (
        $username == "" ||
        $password == "" ||
        $firstname == "" ||
        $surname == "" ||
        $address1 == "" ||
        $city == "" ||
        $mobile == ""
    ) {
        $error = "Please fill in all required fields.";
    }
    elseif (strlen($password) != 6) {
        $error = "Password must be exactly 6 characters.";
    }
    elseif ($password != $confirm_password) {
        $error = "Passwords do not match.";
    }
    elseif (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $error = "Mobile must contain exactly 10 digits.";
    }
    else {

        $stmt = $conn->prepare(
            "select Username from Users where Username = ?"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Username already exists.";
        }
        else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $insert = $conn->prepare(
                "insert into Users
                (Username, Password, FirstName, Surname,
                 AddressLine1, AddressLine2, City,
                 Telephone, Mobile)
                values (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $insert->bind_param(
                "sssssssss",
                $username,
                $hashed_password,
                $firstname,
                $surname,
                $address1,
                $address2,
                $city,
                $telephone,
                $mobile
            );

            if ($insert->execute()) {
                $success = "Registration successful!";
            }
            else {
                $error = "Registration failed. Please try again.";
            }

            $insert->close();
        }

        $stmt->close();
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - Library</title>
</head>
<body>

<h1>Create an Account</h1>


<?php if ($error != ""): ?>
    <p style="color: red;">
        <?php echo htmlspecialchars($error); ?>
    </p>
<?php endif; ?>

<?php if ($success != ""): ?>
    <p style="color: green;">
        <?php echo htmlspecialchars($success); ?>
    </p>
<?php endif; ?>


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
