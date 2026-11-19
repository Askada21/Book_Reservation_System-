<?php

require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/db.php";

// Retrieve all categories from the database
$sql = "select * from Categories order by CategoryDescription";
$categories = $conn->query($sql);

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
        <input type="text" name="title">
        <br><br>

        <label>Author:</label>
        <input type="text" name="author">
        <br><br>

        <label>Category:</label>

        <select name="category">
            <option value="">All categories</option>

            <?php while ($category = $categories->fetch_assoc()) { ?>

                <option value="<?php echo htmlspecialchars($category["CategoryID"]); ?>">
                    <?php echo htmlspecialchars($category["CategoryDescription"]); ?>
                </option>

            <?php } ?>

        </select>

        <br><br>

        <button type="submit">Search</button>

    </form>

</body>

</html>