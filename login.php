<?php
// Database connection settings
$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the raw POST data
        $data = json_decode(file_get_contents("php://input"), true);

        $phone_number = $data['phone_number'];
        $password = $data['password'];

        // Input validation
        if (empty($phone_number) || empty($password)) {
            echo json_encode(["success" => false, "message" => "Phone number and password are required"]);
            exit();
        }

        // Check if the user exists
        $stmt = $conn->prepare("SELECT * FROM user WHERE phone_number = :phone_number");
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->execute();

        // If user exists
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Password matched
            echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
        } else {
            // Invalid login credentials
            echo json_encode(["success" => false, "message" => "Invalid phone number or password"]);
        }
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>   edit it
