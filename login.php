<?php
// Load environment variables for database credentials
$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_TIMEOUT, 5); // Set timeout for the connection
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage()); // Log the error
    http_response_code(500); // Internal Server Error
    echo json_encode(["status" => "error", "message" => "Unable to connect to the database"]);
    exit();
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Parse JSON input
    $raw_input = file_get_contents("php://input");
    error_log("Raw Input Data: " . $raw_input); // Log raw input data

    $data = json_decode($raw_input, true);
    if ($data === null) {
        error_log("JSON Decode Error: " . json_last_error_msg()); // Log JSON errors
        http_response_code(400); // Bad Request
        echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
        exit();
    }

    $phone_number = $data['phone_number'] ?? null;
    $password = $data['password'] ?? null;

    // Validate inputs
    if (empty($phone_number) || empty($password)) {
        error_log("Invalid Input - Phone: $phone_number, Password: $password"); // Log invalid input
        http_response_code(400); // Bad Request
        echo json_encode(["status" => "error", "message" => "Phone number and password are required"]);
        exit();
    }

    if (!preg_match('/^[0-9]{10}$/', $phone_number)) {
        error_log("Invalid Phone Number Format: $phone_number"); // Log invalid phone format
        http_response_code(400); // Bad Request
        echo json_encode(["status" => "error", "message" => "Invalid phone number format"]);
        exit();
    }

    try {
        // Fetch user data securely
        $stmt = $conn->prepare("SELECT id, full_name, phone_number, hashed_password FROM user WHERE phone_number = :phone_number");
        $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['hashed_password'])) {
            unset($user['hashed_password']); // Remove sensitive data before sending the response
            http_response_code(200); // OK
            echo json_encode(["success" => "success", "message" => "Login successful", "user" => $user]);
        } else {
            sleep(1); // Add a slight delay to mitigate brute force attacks
            http_response_code(401); // Unauthorized
            echo json_encode(["status" => "error", "message" => "Invalid phone number or password"]);
        }
    } catch (PDOException $e) {
        error_log("Database query failed: " . $e->getMessage()); // Log the error
        http_response_code(500); // Internal Server Error
        echo json_encode(["status" => "error", "message" => "An error occurred while processing your request"]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
}
