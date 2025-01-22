<?php
// Database connection settings
$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

header("Content-Type: application/json");

try {
    // Establish database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Decode the raw POST data
        $data = json_decode(file_get_contents("php://input"), true);

        // Input sanitization and validation
        $phone_number = isset($data['phone_number']) ? trim($data['phone_number']) : null;
        $password = isset($data['password']) ? trim($data['password']) : null;

        if (empty($phone_number) || empty($password)) {
            echo json_encode([
                "success" => false,
                "message" => "Phone number and password are required"
            ]);
            exit();
        }

        // Prepare and execute the query to check if the user exists
        $stmt = $conn->prepare("SELECT id, full_name, phone_number, email, trade, password_hash FROM user WHERE phone_number = :phone_number");
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Password matched, exclude sensitive data from response
            unset($user['password_hash']);
            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "user" => $user
            ]);
        } else {
            // Invalid login credentials
            echo json_encode([
                "success" => false,
                "message" => "Invalid phone number or password"
            ]);
        }
    } else {
        // Invalid request method
        echo json_encode([
            "success" => false,
            "message" => "Invalid request method"
        ]);
    }

} catch (PDOException $e) {
    // Handle database errors
    echo json_encode([
        "success" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
