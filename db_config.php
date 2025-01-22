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

        // Check if any of the required fields are empty before proceeding
        if (empty($data['full_name']) || empty($data['phone_number']) || empty($data['email']) || empty($data['trade']) || empty($data['password'])) {
            echo json_encode(["success" => false, "message" => "All fields are required"]);
            exit();
        }

        // Extract data from the validated payload
        $full_name = $data['full_name'];
        $phone_number = $data['phone_number'];
        $email = $data['email'];
        $trade = $data['trade'];
        $password = password_hash($data['password'], PASSWORD_BCRYPT); // Hash the password

        // Prepare the SQL statement
        $stmt = $conn->prepare("INSERT INTO user (full_name, phone_number, email, trade, password_hash) 
                                VALUES (:full_name, :phone_number, :email, :trade, :password)");

        // Bind parameters to the SQL statement
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':trade', $trade);
        $stmt->bindParam(':password', $password);

        // Execute the statement and check if successful
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "User registered successfully"]);
        } else {
            echo json_encode(["success" => false, "message" => "Error registering user"]);
        }
    }
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
