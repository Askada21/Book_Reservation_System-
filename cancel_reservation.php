
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Check request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my_reservations.php");
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

    // Lock the book record
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

    // Delete the user's reservation
    $sql = "delete from Reservations
            where ISBN = ?
            and Username = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $isbn, $username);
    $stmt->execute();

    $deletedRows = $stmt->affected_rows;

    $stmt->close();

    // Verify reservation ownership
    if ($deletedRows !== 1) {
        throw new RuntimeException("Reservation not found or access denied.", 1002);
    }

    // Make the book available again
    $sql = "update Books
            set Reserved = 'N'
            where ISBN = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $isbn);
    $stmt->execute();
    $stmt->close();

    // Save database changes
    $conn->commit();

    $message = "Reservation cancelled successfully!";

} catch (Exception $e) {

    // Undo database changes
    $conn->rollback();

    if (in_array($e->getCode(), [1001, 1002], true)) {
        $message = $e->getMessage();
    } else {
        error_log($e->getMessage());
        $message = "Unable to cancel the reservation.";
    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cancel Reservation</title>
</head>

<body>

    <h1>Cancel Reservation</h1>

    <p><?php echo htmlspecialchars($message); ?></p>

    <a href="my_reservations.php">My Reservations</a>
    <a href="search.php">Search Books</a>

</body>

</html>
