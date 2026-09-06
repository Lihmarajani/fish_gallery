<?php

session_start();

require_once "database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$error = "";

$name = "";
$description = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if ($name === "" || $description === "") {

        $error = "Please fill in all fields.";

    } elseif (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

        $error = "Please select an image.";

    } else {

        $image = $_FILES["image"];

        $allowedTypes = [
            "image/jpeg",
            "image/png"
        ];

        $imageInfo = getimagesize($image["tmp_name"]);

        if ($imageInfo === false) {

            $error = "The uploaded file is not a valid image.";

        } elseif (!in_array($imageInfo["mime"], $allowedTypes)) {

            $error = "Only JPG, JPEG, and PNG images are allowed.";

        } elseif ($image["size"] > 5 * 1024 * 1024) {

            $error = "Image size must not exceed 5MB.";

        } else {

            $extension = strtolower(
                pathinfo($image["name"], PATHINFO_EXTENSION)
            );

            if ($extension === "jpeg") {
                $extension = "jpg";
            }

            $uniqueName = uniqid("fish_", true) . "." . $extension;

            $uploadDirectory = __DIR__ . "/uploads/";

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }

            $uploadPath = $uploadDirectory . $uniqueName;

            if (move_uploaded_file($image["tmp_name"], $uploadPath)) {

                $stmt = $conn->prepare(
                    "INSERT INTO fish (name, description, image)
                     VALUES (?, ?, ?)"
                );

                $stmt->bind_param(
                    "sss",
                    $name,
                    $description,
                    $uniqueName
                );

                if ($stmt->execute()) {

                    $stmt->close();

                    header("Location: dashboard.php");
                    exit;

                } else {

                    if (file_exists($uploadPath)) {
                        unlink($uploadPath);
                    }

                    $error = "Failed to save fish information.";

                    $stmt->close();
                }

            } else {

                $error = "Failed to upload image.";

            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Add Fish</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7f9;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: 40px auto;
        }

        .form-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h1 {
            color: #126782;
            margin-top: 0;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        input,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 15px;
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        button {
            margin-top: 20px;
            padding: 12px 20px;
            background: #126782;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 15px;
        }

        button:hover {
            background: #0d536b;
        }

        .back {
            display: inline-block;
            margin-left: 10px;
            color: #126782;
            text-decoration: none;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .info {
            color: #666;
            font-size: 14px;
        }

    </style>

</head>

<body>

<div class="container">

    <div class="form-box">

        <h1>Add Fish</h1>

        <?php if ($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            action="add_fish.php"
            enctype="multipart/form-data"
        >

            <label for="name">
                Fish Name
            </label>

            <input
                type="text"
                id="name"
                name="name"
                maxlength="100"
                value="<?php echo htmlspecialchars($name); ?>"
                required
            >

            <label for="description">
                Description
            </label>

            <textarea
                id="description"
                name="description"
                required
            ><?php echo htmlspecialchars($description); ?></textarea>

            <label for="image">
                Fish Image
            </label>

            <input
                type="file"
                id="image"
                name="image"
                accept=".jpg,.jpeg,.png"
                required
            >

            <p class="info">
                Allowed formats: JPG, JPEG, PNG.
                Maximum size: 5MB.
            </p>

            <button type="submit">
                Add Fish
            </button>

            <a href="dashboard.php" class="back">
                Cancel
            </a>

        </form>

    </div>

</div>

</body>

</html>