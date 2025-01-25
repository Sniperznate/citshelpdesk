<?php phpinfo(); ?>

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

        // Sanitize and extract input data
        $full_name = htmlspecialchars($data['full_name'], ENT_QUOTES, 'UTF-8');
        $phone_number = htmlspecialchars($data['phone_number'], ENT_QUOTES, 'UTF-8');
        $email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
        $trade = htmlspecialchars($data['trade'], ENT_QUOTES, 'UTF-8');
        $password = password_hash($data['password'], PASSWORD_BCRYPT); // Hash the password

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(["success" => false, "message" => "Invalid email format"]);
            exit();
        }

        // Check if the phone number already exists
        $checkQuery = "SELECT COUNT(*) FROM user WHERE phone_number = :phone_number";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bindParam(':phone_number', $phone_number);
        $checkStmt->execute();
        $exists = $checkStmt->fetchColumn();

        if ($exists) {
            echo json_encode(["success" => false, "message" => "Phone number is already registered"]);
            exit();
        }

        // Prepare the SQL statement for insertion
        $stmt = $conn->prepare("INSERT INTO user (full_name, phone_number, email, trade, password_hash) 
                                VALUES (:full_name, :phone_number, :email, :trade, :password)");

        // Bind parameters to prevent SQL injection
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':trade', $trade);
        $stmt->bindParam(':password', $password);

        // Execute the query and respond with the result
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "User registered successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error registering user"]);
        }
    } else {
        // Handle unsupported request methods
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
    }
} catch (PDOException $e) {
    // Handle database exceptions securely
    error_log($e->getMessage()); // Log the error (do not expose details to the user)
    echo json_encode(["success" => false, "message" => "An internal server error occurred"]);
}
?>
