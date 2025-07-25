<?php
header("Content-Type: application/json");
include "../connection.php";

$response = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate required fields
    if (!isset($_POST['name'], $_POST['username'], $_POST['email'], $_POST['mobile'], $_POST['password'])) {
        $response['status'] = 'error';
        $response['message'] = 'Missing required fields';
        http_response_code(400);
        echo json_encode($response);
        exit;
    }

    // Get POST data
    $name = $_POST['name'];
    $username = $_POST['username']; // game_id
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $password = $_POST['password'];
    $profile_picture = ''; // default empty or you can set a default image URL

    // Check for existing user
    $checkQuery = "SELECT * FROM users WHERE email = ? OR mobile_number = ? OR game_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("sss", $email, $mobile, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $existing = $result->fetch_assoc();
        $conflictFields = [];
        if ($existing['email'] === $email) $conflictFields[] = 'email';
        if ($existing['mobile_number'] === $mobile) $conflictFields[] = 'mobile number';
        if ($existing['game_id'] === $username) $conflictFields[] = 'username';

        $response['status'] = 'error';
        $response['message'] = 'Already exists: ' . implode(', ', $conflictFields);
        http_response_code(409);
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Handle optional file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));
        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;

        $uploadFileDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/images/';
        $dest_path = $uploadFileDir . $newFileName;

        if (!file_exists($uploadFileDir)) {
            mkdir($uploadFileDir, 0777, true);
        }

        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            $profile_picture = 'https://win80x.com/uploads/images/' . $newFileName;
        }
    }

    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (name, game_id, email, mobile_number, password, profile_picture) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $username, $email, $mobile, $hashed_password, $profile_picture);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'User successfully signed up';
        http_response_code(201);
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Database error: ' . $stmt->error;
        http_response_code(500);
    }

    $stmt->close();
    mysqli_close($conn);
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
    http_response_code(405);
}

echo json_encode($response);


?>
