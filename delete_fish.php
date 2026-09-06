<?php

session_start();

require_once "database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
}

if (!isset($_POST["id"]) || !is_numeric($_POST["id"])) {
    header("Location: dashboard.php");
    exit;
}

$id = intval($_POST["id"]);

/*
 * First get the fish image filename.
 */
$stmt = $conn->prepare(
    "SELECT image
     FROM fish
     WHERE id = ?"
);

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    $stmt->close();

    header("Location: dashboard.php");
    exit;
}

$fish = $result->fetch_assoc();

$imageName = $fish["image"];

$stmt->close();

/*
 * Delete the fish from the database.
 */
$stmt = $conn->prepare(
    "DELETE FROM fish
     WHERE id = ?"
);

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

    $stmt->close();

    /*
     * Delete the image from the uploads folder.
     */
    $imagePath = __DIR__ . "/uploads/" . $imageName;

    if (file_exists($imagePath)) {
        unlink($imagePath);
    }

} else {

    $stmt->close();
}

header("Location: dashboard.php");
exit;

?>