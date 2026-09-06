<?php

session_start();

require_once "database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid fish ID.");
}

$id = intval($_GET["id"]);

$stmt = $conn->prepare(
    "SELECT id, name, description, image
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

$stmt->close();

$error = "";

$name = $fish["name"];
$description = $fish["description"];
$currentImage = $fish["image"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $description = trim($_POST["description"]);

    if ($name === "" || $description === "") {

        $error = "Please fill in all fields.";

    } else {

        $newImageName = $currentImage;
        $newImagePath = "";
        $newImageUploaded = false;

        /*
         * Check whether a new image was selected.
         */
        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

                $error = "There was a problem uploading the image.";

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

                    $newImageName =
                        uniqid("fish_", true) . "." . $extension;

                    $uploadDirectory = __DIR__ . "/uploads/";

                    if (!is_dir($uploadDirectory)) {
                        mkdir($uploadDirectory, 0755, true);
                    }

                    $newImagePath =
                        $uploadDirectory . $newImageName;

                    if (move_uploaded_file(
                        $image["tmp_name"],
                        $newImagePath
                    )) {

                        $newImageUploaded = true;

                    } else {

                        $error = "Failed to upload the new image.";

                    }
                }
            }
        }

        if ($error === "") {

            $stmt = $conn->prepare(
                "UPDATE fish
                 SET name = ?, description = ?, image = ?
                 WHERE id = ?"
            );

            $stmt->bind_param(
                "sssi",
                $name,
                $description,
                $newImageName,
                $id
            );

            if ($stmt->execute()) {

                $stmt->close();

                /*
                 * Delete the old image only after
                 * the database update succeeds.
                 */
                if ($newImageUploaded) {

                    $oldImagePath =
                        __DIR__ . "/uploads/" . $currentImage;

                    if (
                        file_exists($oldImagePath) &&
                        $currentImage !== $newImageName
                    ) {
                        unlink($oldImagePath);
                    }
                }

                header("Location: dashboard.php");
                exit;

            } else {

                $stmt->close();

                /*
                 * If database update fails, remove
                 * the newly uploaded image.
                 */
                if (
                    $newImageUploaded &&
                    file_exists($newImagePath)
                ) {
                    unlink($newImagePath);
                }

                $error = "Failed to update fish information.";
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Fish</title>

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

        .current-image {
            width: 200px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
            display: block;
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

        <h1>Edit Fish</h1>

        <?php if ($error !== ""): ?>

            <div class="error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>

        <form
            method="POST"
            action="edit_fish.php?id=<?php echo $id; ?>"
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

            <label>
                Current Image
            </label>

            <img
                class="current-image"
                src="uploads/<?php echo htmlspecialchars($currentImage); ?>"
                alt="<?php echo htmlspecialchars($name); ?>"
            >

            <label for="image">
                Change Image
            </label>

            <input
                type="file"
                id="image"
                name="image"
                accept=".jpg,.jpeg,.png"
            >

            <p class="info">
                Leave the image field empty if you want to keep
                the current image.
            </p>

            <p class="info">
                Allowed formats: JPG, JPEG, PNG.
                Maximum size: 5MB.
            </p>

            <button type="submit">
                Update Fish
            </button>

            <a href="dashboard.php" class="back">
                Cancel
            </a>

        </form>

    </div>

</div>

</body>

</html>