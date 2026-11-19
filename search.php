
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Generate a security token
if (!isset($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

// Retrieve all categories from the database
$sql = "select * from Categories order by CategoryDescription";
$categories = $conn->query($sql);

// Retrieve search parameters
$title = trim($_GET["title"] ?? "");
$author = trim($_GET["author"] ?? "");
$categoryID = $_GET["category"] ?? "";

// Pagination settings
$booksPerPage = 5;
$page = max(1, (int) ($_GET["page"] ?? 1));

// Count matching books
$titleSearch = "%" . $title . "%";
$authorSearch = "%" . $author . "%";

$countSql = "select count(*) as TotalBooks
             from Books
             inner join Categories
             on Books.CategoryID = Categories.CategoryID
             where BookTitle like ?
             and Author like ?
             and (? = '' or Books.CategoryID = ?)";

$countStmt = $conn->prepare($countSql);

$countStmt->bind_param(
    "ssss",
    $titleSearch,
    $authorSearch,
    $categoryID,
    $categoryID
);

$countStmt->execute();

$totalBooks = $countStmt->get_result()
                       ->fetch_assoc()["TotalBooks"];

$countStmt->close();

$totalPages = (int) ceil($totalBooks / $booksPerPage);

$page = min($page, max(1, $totalPages));

$offset = ($page - 1) * $booksPerPage;

// Search for books
$sql = "select Books.*, Categories.CategoryDescription
        from Books
        inner join Categories
        on Books.CategoryID = Categories.CategoryID
        where BookTitle like ?
        and Author like ?
        and (? = '' or Books.CategoryID = ?)
        order by BookTitle, ISBN
        limit ? offset ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssii",
    $titleSearch,
    $authorSearch,
    $categoryID,
    $categoryID,
    $booksPerPage,
    $offset
);

$stmt->execute();

$books = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Search Books</title>
</head>

<body>

    <h1>Search Books</h1>

    <nav>
        <a href="index.php">Home</a>
        <a href="search.php">Search Books</a>
        <a href="logout.php">Logout</a>
    </nav>

    <hr>

    <!-- Search form -->

    <form method="GET" action="search.php">

        <label>Book Title:</label>
        <input type="text" name="title"
               value="<?php echo htmlspecialchars($title); ?>">
        <br><br>

        <label>Author:</label>
        <input type="text" name="author"
               value="<?php echo htmlspecialchars($author); ?>">
        <br><br>

        <label>Category:</label>

        <select name="category">
            <option value="">All categories</option>

            <?php while ($category = $categories->fetch_assoc()) { ?>

                <option value="<?php echo htmlspecialchars($category["CategoryID"]); ?>"
                    <?php if ($categoryID === $category["CategoryID"]) echo "selected"; ?>>

                    <?php echo htmlspecialchars($category["CategoryDescription"]); ?>

                </option>

            <?php } ?>

        </select>

        <br><br>

        <button type="submit">Search</button>

    </form>

    <hr>

    <!-- Display search results -->

    <h2>Search Results</h2>

    <p>Total books found: <?php echo $totalBooks; ?></p>

    <?php if ($books->num_rows > 0) { ?>

        <table border="1">

            <tr>
                <th>ISBN</th>
                <th>Book Title</th>
                <th>Author</th>
                <th>Edition</th>
                <th>Year</th>
                <th>Category</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while ($book = $books->fetch_assoc()) { ?>

                <tr>
                    <td><?php echo htmlspecialchars($book["ISBN"]); ?></td>
                    <td><?php echo htmlspecialchars($book["BookTitle"]); ?></td>
                    <td><?php echo htmlspecialchars($book["Author"]); ?></td>
                    <td><?php echo htmlspecialchars($book["Edition"] ?? ""); ?></td>
                    <td><?php echo htmlspecialchars($book["Year"] ?? ""); ?></td>
                    <td><?php echo htmlspecialchars($book["CategoryDescription"]); ?></td>

                    <!-- Book status -->

                    <td>
                        <?php
                        if ($book["Reserved"] == "N") {
                            echo "Available";
                        } else {
                            echo "Reserved";
                        }
                        ?>
                    </td>

                    <!-- Reservation button -->

                    <td>

                        <?php if ($book["Reserved"] == "N") { ?>

                            <form method="POST" action="reserve.php">

                                <input type="hidden" name="isbn"
                                       value="<?php echo htmlspecialchars($book["ISBN"]); ?>">

                                <input type="hidden" name="csrf_token"
                                       value="<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>">

                                <button type="submit">Reserve</button>

                            </form>

                        <?php } else { ?>

                            Unavailable

                        <?php } ?>

                    </td>

                </tr>

            <?php } ?>

        </table>

    <?php } else { ?>

        <p>No books found.</p>

    <?php } ?>

    <!-- Pagination -->

    <?php if ($totalPages > 1) { ?>

        <br>

        <div>

            <?php for ($i = 1; $i <= $totalPages; $i++) { ?>

                <?php
                $url = "search.php?" . http_build_query([
                    "title" => $title,
                    "author" => $author,
                    "category" => $categoryID,
                    "page" => $i
                ]);
                ?>

                <?php if ($i == $page) { ?>

                    <strong><?php echo $i; ?></strong>

                <?php } else { ?>

                    <a href="<?php echo htmlspecialchars($url); ?>">
                        <?php echo $i; ?>
                    </a>

                <?php } ?>

            <?php } ?>

        </div>

        <p>
            Page <?php echo $page; ?>
            of <?php echo $totalPages; ?>
        </p>

    <?php } ?>

</body>

</html>
