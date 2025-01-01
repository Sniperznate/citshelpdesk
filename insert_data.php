<?php
    include("db_config.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get data from POST request
        $full_name = $_POST['full_name'];
        $phone_number = $_POST['phone_number'];
        $email = $_POST['email'];
        $trade = $_POST['trade'];
        $password = hash('sha256', $_POST['password']);  // Hashing password with SHA-256

        // Check if required fields are empty
        if (empty($full_name) || empty($phone_number) || empty($email) || empty($password)) {
            echo "Please fill in all fields!";
            exit();
        }

        // Insert into the database
        $stmt = $conn->prepare("INSERT INTO users (full_name, phone_number, email, trade, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $full_name, $phone_number, $email, $trade, $password);

        // Execute the query and check if successful
        if ($stmt->execute()) {
            echo "Sign-up successful!";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the prepared statement and connection
        $stmt->close();
        $conn->close();
    }
?>
