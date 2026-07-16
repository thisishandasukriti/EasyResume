<?php
// Database connection variables
$server = "localhost";
$username = "root";
$password = "";
$database = "easy resume";

// Establish the connection
$con = new mysqli($server, $username, $password, $database);

// Check the connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// Handle form submission for insertion
if (isset($_POST['insert'])) {
    $template_name = $_POST['template_name'];
    $description = $_POST['description'];

    // SQL query to insert data
    $insert_sql = "INSERT INTO `cv_templates` (`template_name`, `description`, `created_at`) 
                   VALUES ('$template_name', '$description', CURRENT_TIMESTAMP())";

    // Execute the query
    if ($con->query($insert_sql) === TRUE) {
        echo "Template inserted successfully!";
    } else {
        echo "Error: " . $insert_sql . "<br>" . $con->error;
    }
}

// Handle form submission for deletion
if (isset($_POST['delete'])) {
    $id_to_delete = $_POST['id'];

    // SQL query to delete data
    $delete_sql = "DELETE FROM `cv_templates` WHERE `id` = '$id_to_delete'";

    // Execute the query
    if ($con->query($delete_sql) === TRUE) {
        if ($con->affected_rows > 0) {
            echo "Template deleted successfully!";
        } else {
            echo "No template found with that ID!";
        }
    } else {
        echo "Error: " . $delete_sql . "<br>" . $con->error;
    }
}

// Fetch all records from the table for viewing
$fetch_sql = "SELECT * FROM `cv_templates`";
$result = $con->query($fetch_sql);

// Close the connection at the end of the script
$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage CV Templates</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #c6c6f7;
            margin: 0;
            padding: 20px;
        }
        .form-container, .table-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 20px auto;
        }
        .form-container h2, .table-container h2 {
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-container input, .form-container button, .form-container textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-container button {
            background-color: #6c63ff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #3a1c8e;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        table th {
            background-color: #6c63ff;
            color: white;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Insert Template</h2>
    <form action="" method="POST">
        <input type="text" name="template_name" placeholder="Enter template name" required>
        <textarea name="description" placeholder="Enter template description" required></textarea>
        <button type="submit" name="insert">Insert Template</button>
    </form>
</div>

<div class="form-container">
    <h2>Delete Template</h2>
    <form action="" method="POST">
        <input type="number" name="id" placeholder="Enter template ID to delete" required>
        <button type="submit" name="delete">Delete Template</button>
    </form>
</div>

<div class="table-container">
    <h2>View Templates</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Template Name</th>
                <th>Description</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['template_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No templates found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
