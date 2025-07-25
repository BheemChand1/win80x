<?php
// Database connection settings
$host = 'localhost';
$dbname = 'win80x_db';
$username = 'win80x_win80x';
$password = 'k*NSZnA0n;5q';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database: " . $e->getMessage());
}
// Handle AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    // Create or update question
    if ($action === 'create_or_update') {
        $set = $_POST['set'];
        $question = $_POST['question'];
        $answer1 = $_POST['answer1'];
        $answer2 = $_POST['answer2'];
        $answer3 = $_POST['answer3'];
        $answer4 = $_POST['answer4'];
        $correct_answer = $_POST['correct_answer']; // 1, 2, 3, or 4
        $question_id = $_POST['question_id'] ?? '';

        if ($question_id) {
            // Update existing question
            $sql = "UPDATE questions SET set_type = :set_type, question = :question, answer1 = :answer1, answer2 = :answer2, answer3 = :answer3, answer4 = :answer4, correct_answer = :correct_answer WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $question_id);
        } else {
            // Create new question
            $sql = "INSERT INTO questions (set_type, question, answer1, answer2, answer3, answer4, correct_answer) VALUES (:set_type, :question, :answer1, :answer2, :answer3, :answer4, :correct_answer)";
            $stmt = $pdo->prepare($sql);
        }

        $stmt->bindParam(':set_type', $set);
        $stmt->bindParam(':question', $question);
        $stmt->bindParam(':answer1', $answer1);
        $stmt->bindParam(':answer2', $answer2);
        $stmt->bindParam(':answer3', $answer3);
        $stmt->bindParam(':answer4', $answer4);
        $stmt->bindParam(':correct_answer', $correct_answer);

       if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    // Log or display the error
    $error = $stmt->errorInfo();
    echo json_encode(["status" => "error", "message" => $error]);
}

    }

    // Delete question
    if ($action === 'delete') {
        $question_id = $_POST['question_id'];
        $sql = "DELETE FROM questions WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $question_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error"]);
        }
    }

    exit();
}

// Fetch specific question for editing
if (isset($_GET['edit'])) {
    $question_id = $_GET['edit'];
    $sql = "SELECT * FROM questions WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $question_id);
    $stmt->execute();
    $question = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($question);
    exit();
}

// Fetch all questions for display
$sql = "SELECT * FROM questions ORDER BY created_at DESC";
$questions = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($questions);

?>
