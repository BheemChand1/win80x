<?php
header("Content-Type: application/json");

include "../connection.php";
include "./config.php"; // Should define $key and $issuer

require_once './vendor/autoload.php'; // Include the JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$response = array();

// Only allow POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
    http_response_code(405);
    echo json_encode($response);
    exit;
}

// Get form data (application/x-www-form-urlencoded)
$username = $_POST['username'] ?? null;
$password = $_POST['password'] ?? null;

if (!$username || !$password) {
    $response['status'] = 'error';
    $response['message'] = 'Missing required fields';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

$stmt = $conn->prepare("SELECT id, wallet_balance, password, name, email, mobile_number, profile_picture FROM users WHERE game_id = ?");
$stmt->bind_param("s", $username);

if ($stmt->execute()) {
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
        $stmt->bind_result($id, $wallet_balance, $hashed_password, $name, $email, $mobile, $profile_picture);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            // JWT Payload
            $issuedAt = time();
            $expirationTime = $issuedAt + (60 * 60); // Token valid for 1 hour
            $payload = array(
                "iss" => $issuer,
                "iat" => $issuedAt,
                "exp" => $expirationTime,
                "data" => array(
                    "id" => $id,
                    "email" => $email
                )
            );

            $jwt = JWT::encode($payload, $key, 'HS256');

            // Default profile picture path if empty
            $default_profile_picture = "https://win80x.com/uploads/images/default/a35328b569f7f572fb2ed79522e7bf6a.png";
            $final_profile_picture = (!empty($profile_picture)) ? $profile_picture : $default_profile_picture;

            $response['status'] = 'success';
            $response['message'] = 'Login successful';
            $response['token'] = $jwt;
            $response['user'] = array(
                'id' => $id,
                'wallet_balance' => $wallet_balance,
                'name' => $name,
                'email' => $email,
                'mobile_number' => $mobile,
                'profile_picture' => $final_profile_picture
            );
            http_response_code(200);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid username or password';
            http_response_code(401);
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid username or password';
        http_response_code(401);
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Database error: ' . $stmt->error;
    http_response_code(500);
}

$stmt->close();
mysqli_close($conn);

echo json_encode($response);
?>
