<?php
header("Content-Type: application/json");

// Database connection settings
$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        $phone_number = $data['phone_number'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($phone_number) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Phone number and password are required"]);
            exit();
        }

        $stmt = $conn->prepare("SELECT * FROM user WHERE phone_number = :phone_number");
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            unset($user['password_hash']); // Remove password hash before sending response
            echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid phone number or password"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Server error: " . $e->getMessage()]);
}
