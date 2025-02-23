<?php
require 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['phone'])) {
        $phone = $_POST['phone'];
        
        $stmt = $conn->prepare("SELECT * FROM users WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "exists";
        } else {
            echo "not_found";
        }
        
        $stmt->close();
    } else {
        echo "missing_parameter";
    }
} else {
    echo "invalid_request";
}

$conn->close();
?>
