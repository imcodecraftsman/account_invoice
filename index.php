<?php

// Database credentils
$servername = "localhost";
$username = "root";
$password = "";
$database = "api_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/index.php/users') {
    $data = json_decode(file_get_contents('php://input'), true);

    try {
        $name = $conn->real_escape_string($data['name']);
        $email = $conn->real_escape_string($data['email']);

        $sql = "INSERT INTO users (name, email) VALUES ('$name', '$email')";

        if ($conn->query($sql) === TRUE) {
            $userId = $conn->insert_id;
            $response = [
                'message' => 'User inserted successfully.',
                'data' => [
                    'id' => $userId,
                    'name' => $name,
                    'email' => $email
                ]
            ];
            echo json_encode($response);
        } else {
            echo json_encode(['error' => 'User creation failed']);
        }

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) {
            echo "Error: Duplicate email address. Email addresses must be unique.";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
    exit;
}


// Create Invoice
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/index.php/invoice') {
    $data = json_decode(file_get_contents('php://input'), true);

    $email = $conn->real_escape_string($data['email']);
    $amount = $conn->real_escape_string($data['amount']);
    $description = $conn->real_escape_string($data['description']);

    $get_sql = "SELECT id FROM users WHERE email = '$email'";   
    $result = $conn->query($get_sql);
    if ($result->num_rows > 0) {
        $user_id = $result->fetch_assoc()['id'];
    } else {
        echo "No results found";
    }

    $sql = "INSERT INTO invoices (user_id, email, amount, description) VALUES ('$user_id', '$email', '$amount', '$description')";

    if ($conn->query($sql) === TRUE) {
        $invoiceId = $conn->insert_id;
        $response = [
            'message' => 'Invoice created successfully.',
            'data' => [
                'id' => $invoiceId,
                'user_id' => $user_id,
                'email' => $email,
                'amount' => $amount,
                'description' => $description,
            ]
        ];
        echo json_encode($response);
    } else {
        echo json_encode(['error' => 'Invoice creation failed']);
    }
    exit;
}

// Invalid Endpoint
http_response_code(404);
echo json_encode(['error' => 'Not Found']);

// Close the database connection
$conn->close();


?>
