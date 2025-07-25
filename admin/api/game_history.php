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

// Check if the required field user_id is provided
if (isset($data->user_id)) {
    // Sanitize the input
    $user_id = $conn->real_escape_string($data->user_id);

    // Prepare the SQL query to fetch gameplay data
    $sql = "SELECT g.game_name, gp.* 
FROM gameplays gp
INNER JOIN games g ON gp.game_id = g.id
WHERE gp.user_id = ?";
    
    // Prepare and bind
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user_id); // 's' specifies the variable type => 'string'

    // Execute the query
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        // Check if any data is returned
        if ($result->num_rows > 0) {
            // Initialize an array to hold all gameplay data
            $gameplays = array();

            // Fetch all rows as an associative array
            while ($row = $result->fetch_assoc()) {
                $gameplays[] = $row; // Append each row to the array
            }

            http_response_code(200); // 200 OK
            echo json_encode(array("success" => true, "data" => $gameplays)); // Return all data
        } else {
            // Respond with no data found
            http_response_code(404); // 404 Not Found
            echo json_encode(array("success" => false, "message" => "No gameplay found with the given user_id."));
        }
    } else {
        // Respond with error on query execution
        http_response_code(500); // Internal Server Error
        echo json_encode(array("success" => false, "message" => "Error: " . $stmt->error));
    }

    // Close the statement
    $stmt->close();
} else {
    // Respond with missing data error
    http_response_code(400); // 400 Bad Request
    echo json_encode(array("success" => false, "message" => "Incomplete data. user_id is required."));
}

// Close the connection
$conn->close();
?>