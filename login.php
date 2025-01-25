<?php
// Load environment variables for database credentials
$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');
$debug_mode = getenv('DEBUG_MODE') === 'true'; // Enable debug mode based on an environment variable

// Set headers for JSON response
header('Content-Type: application/json; charset=utf-8');

// Helper function for consistent JSON responses
function respond($success, $message, $data = null, $code = 200)
{
    http_response_code($code);
    echo json_encode([
        "success" => $success,
        "message" => $message,
        "data" => $data,
    ]);
    exit();
}

try {
    // Establish database connection
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_TIMEOUT, 5); // Set timeout for the connection
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    respond(false, "Unable to connect to the database", null, 500);
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, "Invalid request method", null, 405);
}

// Parse JSON input
$data = json_decode(file_get_contents("php://input"), true);

$phone_number = trim($data['phone_number'] ?? '');
$password = trim($data['password'] ?? '');

// Validate inputs
if (empty($phone_number) || empty($password)) {
    respond(false, "Phone number and password are required", null, 400);
}

if (!preg_match('/^[0-9]{10}$/', $phone_number)) {
    respond(false, "Invalid phone number format", null, 400);
}

try {
    // Fetch user data securely
    $stmt = $conn->prepare("SELECT id, full_name, phone_number, password_hash FROM user WHERE phone_number = :phone_number");
    $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debugging: Log query result if debug mode is enabled
    if ($debug_mode) {
        error_log("Query result: " . print_r($user, true));
    }

    if ($user && password_verify($password, $user['password_hash'])) {
        unset($user['password_hash']); // Remove sensitive data
        respond(true, "Login successful", $user);
    } else {
        sleep(1); // Add a slight delay to mitigate brute force attacks
        respond(false, "Invalid phone number or password", null, 401);
    }
} catch (PDOException $e) {
    error_log("Database query failed: " . $e->getMessage());
    respond(false, "An error occurred while processing your request", null, 500);
}