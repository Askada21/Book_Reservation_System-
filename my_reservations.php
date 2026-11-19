
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Generate a security token
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Retrieve the current username
$username = $_SESSION["username"];

// Retrieve the user's reservations
$sql = "select Books.ISBN,
               Books.BookTitle,
               Books.Author,
               Books.Edition,
               Books.Year,
               Categories.CategoryDescription,
               Reservations.ReservedDate
        from Reservations
        inner join Books
        on Reservations.ISBN = Books.ISBN
        inner join Categories
        on Books.CategoryID = Categories.CategoryID
        where Reservations.Username = ?
        order by Reservations.ReservedDate desc, Books.BookTitle";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();

$reservations = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Reservations</title>
</head>

<body>

    <h1>My Reservations</h1>

    <nav>
        <a href="index.php">Home</a>
        <a href="search.php">Search Books</a>
        <a href="my_reservations.php">My Reservations</a>
        <a href="logout.php">Logout</a>
    </nav>

    <hr>

    <p>
        Logged in as:
        <?php echo htmlspecialchars($username); ?>
    </p>

    <p>
        Total reservations:
        <?php echo $reservations->num_rows; ?>
    </p>

    <?php if ($reservations->num_rows > 0) { ?>

        <table border="1">

            <tr>
                <th>ISBN</th>
                <th>Book Title</th>
                <th>Author</th>
                <th>Edition</th>
                <th>Year</th>
                <th>Category</th>
                <th>Reservation Date</th>
            </tr>

            <?php while ($book = $reservations->fetch_assoc()) { ?>

                <tr>
                    <td><?php echo htmlspecialchars($book["ISBN"]); ?></td>
                    <td><?php echo htmlspecialchars($book["BookTitle"]); ?></td>
                    <td><?php echo htmlspecialchars($book["Author"]); ?></td>
                    <td><?php echo htmlspecialchars($book["Edition"] ?? ""); ?></td>
                    <td><?php echo htmlspecialchars($book["Year"] ?? ""); ?></td>
                    <td><?php echo htmlspecialchars($book["CategoryDescription"]); ?></td>
                    <td><?php echo htmlspecialchars($book["ReservedDate"]); ?></td>
                </tr>

            <?php } ?>

        </table>

    <?php } else { ?>

        <p>You have no reservations.</p>

        <a href="search.php">Search for books</a>

    <?php } ?>

</body>

</html>
