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

        $full_name = $data['full_name'] ?? null;
        $phone_number = $data['phone_number'] ?? null;
        $email = $data['email'] ?? null;
        $trade = $data['trade'] ?? null;
        $password = password_hash($data['password'], PASSWORD_BCRYPT) ?? null; // Hash the password

        $stmt = $conn->prepare("INSERT INTO user (full_name, phone_number, email, trade, password_hash) 
                                VALUES (:full_name, :phone_number, :email, :trade, :password)");
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':trade', $trade);
        $stmt->bindParam(':password', $password);

        if (empty($full_name) || empty($phone_number) || empty($email) || empty($trade) || empty($data['password'])) {
            echo json_encode(["success" => false, "message" => "All fields are required"]);
            exit();
        }

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
