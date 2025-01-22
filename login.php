if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $phone_number = $data['phone_number'] ?? '';
    $hashed_password = $data['password'] ?? ''; // Now expecting a hashed password

    if (empty($phone_number) || empty($hashed_password)) {
        echo json_encode(["success" => false, "message" => "Phone number and password are required"]);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM user WHERE phone_number = :phone_number");
    $stmt->bindParam(':phone_number', $phone_number);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $hashed_password === $user['password_hash']) {
        unset($user['password_hash']); // Remove password hash before sending response
        echo json_encode(["success" => true, "message" => "Login successful", "user" => $user]);
    } else {
        echo json_encode(["success" => false, "message" => "Invalid phone number or password"]);
    }
}
