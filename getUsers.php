<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

include 'db_config.php';

$response = array();

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    $sql = "SELECT full_name, phone_number, email, trade FROM user";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $users = array();
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $response['status'] = 'success';
        $response['users'] = $users;
    } else {
        $response['status'] = 'error';
        $response['message'] = 'No users found';
    }

    echo json_encode($response, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo json_encode(array("status" => "error", "message" => $e->getMessage()));
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
