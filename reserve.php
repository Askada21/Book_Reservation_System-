
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Check request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: search.php");
    exit();
}

// Validate security token
if (
    !isset($_POST["csrf_token"], $_SESSION["csrf_token"]) ||
    !is_string($_POST["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $_POST["csrf_token"])
) {
    exit("Invalid security token.");
}

// Retrieve reservation details
$isbn = $_POST["isbn"] ?? "";
$username = $_SESSION["username"];

if (!is_string($isbn) || trim($isbn) === "") {
    exit("Invalid book ISBN.");
}

// Enable database exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Start database transaction
$conn->begin_transaction();

try {

    // Check if the book is available
    $sql = "select Reserved from Books
            where ISBN = ?
            for update";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $isbn);
    $stmt->execute();

    $book = $stmt->get_result()->fetch_assoc();

    $stmt->close();

    if (!$book) {
        throw new RuntimeException("Book not found.", 1001);
    }

    if ($book["Reserved"] !== "N") {
        throw new RuntimeException("This book is already reserved.", 1002);
    }

    // Insert a new reservation
    $sql = "insert into Reservations
            (ISBN, Username, ReservedDate)
            values (?, ?, curdate())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $isbn, $username);
    $stmt->execute();
    $stmt->close();

    // Update the book status
    $sql = "update Books
            set Reserved = 'Y'
            where ISBN = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $stmt->close();

    // Save database changes
    $conn->commit();

    $message = "Book reserved successfully!";

} catch (Exception $e) {

    // Undo database changes if an error occurs
    $conn->rollback();

    if (in_array($e->getCode(), [1001, 1002], true)) {
        $message = $e->getMessage();
    } else {
        error_log($e->getMessage());
        $message = "Unable to reserve the book.";
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Book Reservation</title>
</head>

<body>

    <h1>Book Reservation</h1>

    <p><?php echo htmlspecialchars($message); ?></p>

    <a href="search.php">Back to Search</a>
    <a href="my_reservations.php">My Reservations</a>

</body>

</html>
