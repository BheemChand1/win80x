<?php
header('Content-Type: application/json'); // Set content type to JSON
include '../connection.php';

// Get the raw POST data
$input_data = file_get_contents("php://input");
$request = json_decode($input_data, true); // Decode the JSON input
// Check if game ID and set_type are provided in the request


if (!isset($request['id']) || !isset($request['set_type'])) {
    http_response_code(400); // Bad request
    echo json_encode(['error' => 'Game ID and set_type are required']);
    exit;
}

$game_id = intval($request['id']); // Sanitize game ID
$set_type = strtoupper($request['set_type']); // Sanitize and ensure set_type is uppercase ('A' or 'B')

// Validate set_type
if ($set_type !== 'A' && $set_type !== 'B') {
    http_response_code(400); // Bad request
    echo json_encode(['error' => 'Invalid set_type. Allowed values are A or B']);
    exit;
}

// Define the columns to fetch based on the set_type
$question_columns = $set_type === 'A' 
    ? 'question_a1, question_a2, question_a3, question_a4, question_a5, question_a6, question_a7, question_a8, question_a9' 
    : 'question_b1, question_b2, question_b3, question_b4, question_b5, question_b6, question_b7, question_b8, question_b9';

// Fetch the game questions based on the game ID and set_type
$sql = "SELECT $question_columns FROM games WHERE id = $game_id"; 
$result = $conn->query($sql);

$questions_ids = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions_ids = array_merge($questions_ids, array_values($row));
    }
} else {
    http_response_code(404); // Not found
    echo json_encode(['error' => 'Game not found']);
    exit;
}

// Fetch questions and answers based on the IDs and set_type
$questions = [];
if (!empty($questions_ids)) {
    $ids = implode(',', array_map('intval', $questions_ids)); // Sanitize and create a comma-separated string
    $sql_questions = "SELECT id, question, answer1, answer2, answer3, answer4, correct_answer 
                      FROM questions 
                      WHERE id IN ($ids) AND set_type = '$set_type'";
    $questions_result = $conn->query($sql_questions);

    if ($questions_result && $questions_result->num_rows > 0) {
        while ($question_row = $questions_result->fetch_assoc()) {
            // Get the correct answer text
            $correct_answer_key = 'answer' . $question_row['correct_answer']; // Determine the key for the correct answer (answer1, answer2, etc.)
            $question_row['correct_answer'] = $question_row[$correct_answer_key]; // Replace the numeric correct_answer with the actual answer text

            $questions[] = $question_row; // Store each question
        }
    } else {
        http_response_code(404); // Not found
        echo json_encode(['error' => 'Questions not found']);
        exit;
    }
}

// Return questions as a JSON response
http_response_code(200); // Success
echo json_encode(['questions' => $questions, 'success'=>true]);
?>
