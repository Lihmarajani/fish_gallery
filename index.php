<?php

require_once "database.php";

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if ($search !== "") {

    $searchTerm = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT id, name, description, image
         FROM fish
         WHERE name LIKE ?
         ORDER BY id DESC"
    );

    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();

    $result = $stmt->get_result();

} else {

    $result = $conn->query(
        "SELECT id, name, description, image
         FROM fish
         ORDER BY id DESC"
    );
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Fish Management System</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7f9;
            color: #333;
        }

        .header {
            background: #126782;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
        }

        .header p {
            margin-bottom: 0;
        }

        .container {
            width: 90%;
            max-width: 1100px;
            margin: 30px auto;
        }

        .search-box {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .search-box form {
            display: flex;
            gap: 10px;
        }

        .search-box input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .search-box button {
            padding: 12px 20px;
            background: #126782;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .search-box button:hover {
            background: #0d536b;
        }

        .clear-search {
            display: inline-block;
            margin-top: 10px;
            color: #126782;
            text-decoration: none;
        }

        .fish-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .fish-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .fish-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .fish-content {
            padding: 20px;
        }

        .fish-content h2 {
            margin-top: 0;
            color: #126782;
        }

        .fish-content p {
            line-height: 1.6;
            color: #666;
        }

        .details-button {
            display: inline-block;
            padding: 10px 15px;
            background: #126782;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .details-button:hover {
            background: #0d536b;
        }

        .no-fish {
            background: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px;
        }

        .admin-link {
            display: block;
            margin-top: 30px;
            text-align: center;
            color: #126782;
            text-decoration: none;
        }

    </style>
</head>

<body>

<div class="header">

    <h1>Fish Management System</h1>

    <p>Explore Our Fish Collection</p>

</div>

<div class="container">

    <div class="search-box">

        <form method="GET" action="index.php">

            <input
                type="text"
                name="search"
                placeholder="Search fish by name..."
                value="<?php echo htmlspecialchars($search); ?>"
            >

            <button type="submit">Search</button>

        </form>

        <?php if ($search !== ""): ?>

            <a class="clear-search" href="index.php">
                Clear Search
            </a>

        <?php endif; ?>

    </div>


    <?php if ($result->num_rows > 0): ?>

        <div class="fish-grid">

            <?php while ($fish = $result->fetch_assoc()): ?>

                <div class="fish-card">

                    <img
                        src="uploads/<?php echo htmlspecialchars($fish["image"]); ?>"
                        alt="<?php echo htmlspecialchars($fish["name"]); ?>"
                    >

                    <div class="fish-content">

                        <h2>
                            <?php echo htmlspecialchars($fish["name"]); ?>
                        </h2>

                        <p>
                            <?php
                            $description = $fish["description"];

                            if (strlen($description) > 100) {
                                echo htmlspecialchars(substr($description, 0, 100)) . "...";
                            } else {
                                echo htmlspecialchars($description);
                            }
                            ?>
                        </p>

                        <a
                            class="details-button"
                            href="fish_details.php?id=<?php echo $fish["id"]; ?>"
                        >
                            View Details
                        </a>

                    </div>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="no-fish">

            <?php if ($search !== ""): ?>

                <h2>No fish found</h2>

                <p>
                    No fish matched your search.
                </p>

            <?php else: ?>

                <h2>No fish available</h2>

                <p>
                    The administrator has not added any fish yet.
                </p>

            <?php endif; ?>

        </div>

    <?php endif; ?>


    <a class="admin-link" href="login.php">
        Admin Login
    </a>

</div>

</body>
</html>