<?php
// Load environment variables for database credentials
$host = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');

try {
    // Establish a secure connection to the database using PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Decode the JSON payload
        $data = json_decode(file_get_contents("php://input"), true);

        // Check if data exists
        if (!$data) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid input data"]);
            exit();
        }

        // Sanitize and extract input data
        $full_name = htmlspecialchars(trim($data['full_name']), ENT_QUOTES, 'UTF-8');
        $phone_number = htmlspecialchars(trim($data['phone_number']), ENT_QUOTES, 'UTF-8');
        $email = filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL);
        $trade = htmlspecialchars(trim($data['trade']), ENT_QUOTES, 'UTF-8');
        $password = trim($data['password']);

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid email format"]);
            exit();
        }

        // Validate phone number format (Indian 10-digit numbers)
        if (!preg_match('/^[6-9]\d{9}$/', $phone_number)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Invalid phone number"]);
            exit();
        }

        // Validate password strength (min 8 chars, at least 1 uppercase, 1 number, 1 special char)
        if (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
            http_response_code(400);
            echo json_encode(["success" => false, "message" => "Password must be at least 8 characters long, include an uppercase letter, a number, and a special character"]);
            exit();
        }

        // Hash the password securely
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the phone number already exists
        $checkQuery = "SELECT COUNT(*) FROM user WHERE phone_number = :phone_number";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':phone_number', $phone_number);
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(["success" => false, "message" => "Phone number is already registered"]);
            exit();
        }

        // Prepare the SQL statement for insertion
        $stmt = $conn->prepare("INSERT INTO user (full_name, phone_number, email, trade, hashed_password) 
                                VALUES (:full_name, :phone_number, :email, :trade, :hashed_password)");

        // Bind parameters to prevent SQL injection
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':trade', $trade);
        $stmt->bindParam(':hashed_password', $hashed_password);

        // Execute the query and respond with the result
        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["success" => true, "message" => "User registered successfully"]);
        } else {
            http_response_code(500);
            error_log("Database Insert Error: " . json_encode($stmt->errorInfo())); // Log exact error
            echo json_encode(["success" => false, "message" => "Error registering user"]);
        }
    } else {
        http_response_code(405); // Method Not Allowed
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
    }
} catch (PDOException $e) {
    // Handle database exceptions securely
    error_log("Database Connection Error: " . $e->getMessage()); // Log the error (do not expose details to the user)
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "An internal server error occurred"]);
}
?>