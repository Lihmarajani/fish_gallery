<?php

session_start();

require_once "database.php";

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

$result = $conn->query(
    "SELECT id, name, description, image, created_at
     FROM fish
     ORDER BY id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard</title>

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
            padding: 20px;
        }

        .header-content {
            width: 90%;
            max-width: 1200px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .header h1 {
            margin: 0;
        }

        .logout {
            background: white;
            color: #126782;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .welcome {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .add-button {
            display: inline-block;
            background: #126782;
            color: white;
            padding: 12px 18px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .add-button:hover {
            background: #0d536b;
        }

        .table-container {
            background: white;
            border-radius: 8px;
            overflow-x: auto;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #126782;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }

        .fish-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        .action-button {
            display: inline-block;
            padding: 7px 10px;
            text-decoration: none;
            border-radius: 4px;
            margin: 2px;
        }

        .edit-button {
            background: #f0ad4e;
            color: white;
        }

        .delete-button {
            background: #d9534f;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .delete-form {
            display: inline;
        }

        .empty {
            padding: 30px;
            text-align: center;
        }

    </style>

</head>

<body>

<div class="header">

    <div class="header-content">

        <h1>Admin Dashboard</h1>

        <a href="logout.php" class="logout">
            Logout
        </a>

    </div>

</div>

<div class="container">

    <div class="welcome">

        <h2>
            Welcome, <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>!
        </h2>

        <p>
            Manage the fish information below.
        </p>

    </div>

    <a href="add_fish.php" class="add-button">
        + Add Fish
    </a>

    <div class="table-container">

        <?php if ($result->num_rows > 0): ?>

            <table>

                <thead>

                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    <?php while ($fish = $result->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?php echo $fish["id"]; ?>
                            </td>

                            <td>

                                <img
                                    class="fish-image"
                                    src="uploads/<?php echo htmlspecialchars($fish["image"]); ?>"
                                    alt="<?php echo htmlspecialchars($fish["name"]); ?>"
                                >

                            </td>

                            <td>
                                <?php echo htmlspecialchars($fish["name"]); ?>
                            </td>

                            <td>

                                <?php

                                $description = $fish["description"];

                                if (strlen($description) > 80) {
                                    echo htmlspecialchars(
                                        substr($description, 0, 80)
                                    ) . "...";
                                } else {
                                    echo htmlspecialchars($description);
                                }

                                ?>

                            </td>

                            <td>

                                <a
                                    href="edit_fish.php?id=<?php echo $fish["id"]; ?>"
                                    class="action-button edit-button"
                                >
                                    Edit
                                </a>

                                <form
                                    class="delete-form"
                                    method="POST"
                                    action="delete_fish.php"
                                    onsubmit="return confirm('Are you sure you want to delete this fish?');"
                                >

                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?php echo $fish["id"]; ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="action-button delete-button"
                                    >
                                        Delete
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                </tbody>

            </table>

        <?php else: ?>

            <div class="empty">

                <h2>No fish available</h2>

                <p>
                    Click "Add Fish" to add your first fish.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>

</body>

</html>