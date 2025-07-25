<?php
// Set headers to allow access from React Native and return JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include '../connection.php';


// Get JSON input from the request body
$data = json_decode(file_get_contents("php://input"));

// Check if required fields are provided
if (
    isset($data->unique_id) && 
    isset($data->set_type) && 
    isset($data->lucky_number)
) {
    // Sanitize the inputs
    $unique_id = $conn->real_escape_string($data->unique_id);
    $set_type = $conn->real_escape_string($data->set_type);
    $lucky_number = intval($data->lucky_number);

    // Prepare the SQL query to update lucky_number
    $sql = "UPDATE gameplays SET lucky_number = ?";

    // Append the appropriate status update based on set_type
    if ($set_type == 'A') {
        $sql .= ", status_A = 1";
    } elseif ($set_type == 'B') {
        $sql .= ", status_B = 1 , status = 1";
    }

    // Complete the query with the WHERE clause
    $sql .= " WHERE unique_id = ?";

    // Prepare and bind
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $lucky_number, $unique_id);

    // Execute the query
    if ($stmt->execute()) {
        // Respond with success
        http_response_code(200); // 200 OK
        echo json_encode(array("message" => "Gameplay updated successfully!", "success"=>true));
    } else {
        // Respond with error
        http_response_code(500); // Internal Server Error
        echo json_encode(array("message" => "Error: " . $stmt->error));
    }

    // Close the statement
    $stmt->close();
} else {
    // Respond with missing data error
    http_response_code(400); // 400 Bad Request
    echo json_encode(array("message" => "Incomplete data. unique_id, set_type, and lucky_number are required."));
}

// Close the connection
$conn->close();
?>
