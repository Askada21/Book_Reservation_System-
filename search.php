
<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Retrieve all categories from the database
$sql = "select * from Categories order by CategoryDescription";
$categories = $conn->query($sql);

// Retrieve search parameters
$title = trim($_GET["title"] ?? "");
$author = trim($_GET["author"] ?? "");
$categoryID = $_GET["category"] ?? "";

// Search for books
$sql = "select Books.*, Categories.CategoryDescription
        from Books
        inner join Categories
        on Books.CategoryID = Categories.CategoryID
        where BookTitle like ?
        and Author like ?
        and (? = '' or Books.CategoryID = ?)
        order by BookTitle";

$titleSearch = "%" . $title . "%";
$authorSearch = "%" . $author . "%";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssss",
    $titleSearch,
    $authorSearch,
    $categoryID,
    $categoryID
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
            </tr>

            <?php while ($book = $books->fetch_assoc()) { ?>

                <tr>
                    <td><?php echo htmlspecialchars($book["ISBN"]); ?></td>
                    <td><?php echo htmlspecialchars($book["BookTitle"]); ?></td>
                    <td><?php echo htmlspecialchars($book["Author"]); ?></td>
                    <td><?php echo htmlspecialchars($book["Edition"]); ?></td>
                    <td><?php echo htmlspecialchars($book["Year"]); ?></td>
                    <td><?php echo htmlspecialchars($book["CategoryDescription"]); ?></td>
                    <td>
                        <?php
                        if ($book["Reserved"] == "N") {
                            echo "Available";
                        } else {
                            echo "Reserved";
                        }
                        ?>
                    </td>
                </tr>

            <?php } ?>

        </table>

    <?php } else { ?>

        <p>No books found.</p>

    <?php } ?>

</body>

</html>
