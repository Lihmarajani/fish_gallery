<?php

require_once "database.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid fish ID.");
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare(
    "SELECT id, name, description, image, created_at
     FROM fish
     WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Fish not found.");
}

$fish = $result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php echo htmlspecialchars($fish["name"]); ?> - Fish Details
    </title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7f9;
        }

        .header {
            background: #126782;
            color: white;
            padding: 25px;
            text-align: center;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 40px auto;
        }

        .fish-details {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .fish-details img {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 8px;
        }

        .fish-details h1 {
            color: #126782;
            margin-bottom: 10px;
        }

        .fish-details p {
            line-height: 1.8;
            color: #555;
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            background: #126782;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back-button:hover {
            background: #0d536b;
        }

    </style>

</head>

<body>

<div class="header">

    <h1>Fish Details</h1>

</div>

<div class="container">

    <div class="fish-details">

        <img
            src="uploads/<?php echo htmlspecialchars($fish["image"]); ?>"
            alt="<?php echo htmlspecialchars($fish["name"]); ?>"
        >

        <h1>
            <?php echo htmlspecialchars($fish["name"]); ?>
        </h1>

        <p>
            <?php echo nl2br(htmlspecialchars($fish["description"])); ?>
        </p>

        <a href="index.php" class="back-button">
            Back to Fish List
        </a>

    </div>

</div>

</body>

</html>