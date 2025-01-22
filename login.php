<?php
// Load environment variables for database credentials
$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');

// try {
//     $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
//     // Set the PDO error mode to exception
//     $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     echo json_encode(["success" => false, "message" => "Database connection failed: " . $e->getMessage()]);
//     exit();
// }

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $phone_number = $data['phone_number'] ?? '';
    $password = $data['password'] ?? '';

    // Validate inputs
    if (empty($phone_number) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Phone number and password are required"]);
        exit();
    }

    if (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        echo json_encode(["success" => false, "message" => "Invalid phone number format"]);
        exit();
    }

    try {
        // Check if user exists in the database
        $stmt = $conn->prepare("SELECT * FROM user WHERE phone_number = :phone_number");
        $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify the password against the stored hashed password
            if (password_verify($password, $user['password_hash'])) {
                unset($user['password_hash']); // Remove password hash before sending response
                echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
            } else {
                echo json_encode(["success" => false, "message" => "Invalid phone number or password"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "User not found"]);
        }
    } catch (PDOException $e) {
        // Log the error if needed (never display database errors directly to the user)
        echo json_encode(["success" => false, "message" => "An error occurred while processing your request"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
