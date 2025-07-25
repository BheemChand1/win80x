<?php
include 'connection.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare SQL statement using prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO games 
        (game_name, question_a1, question_a2, question_a3, question_a4, question_a5, question_a6, question_a7, question_a8, question_a9, question_b1, question_b2, question_b3, question_b4, question_b5, question_b6, question_b7, question_b8, question_b9)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    // Retrieve and sanitize form data
    $game_name = $conn->real_escape_string($_POST['game-name']);
    $question_a1 = $conn->real_escape_string($_POST['question-a1']);
    $question_a2 = $conn->real_escape_string($_POST['question-a2']);
    $question_a3 = $conn->real_escape_string($_POST['question-a3']);
    $question_a4 = $conn->real_escape_string($_POST['question-a4']);
    $question_a5 = $conn->real_escape_string($_POST['question-a5']);
    $question_a6 = $conn->real_escape_string($_POST['question-a6']);
    $question_a7 = $conn->real_escape_string($_POST['question-a7']);
    $question_a8 = $conn->real_escape_string($_POST['question-a8']);
    $question_a9 = $conn->real_escape_string($_POST['question-a9']);
    $question_b1 = $conn->real_escape_string($_POST['question-b1']);
    $question_b2 = $conn->real_escape_string($_POST['question-b2']);
    $question_b3 = $conn->real_escape_string($_POST['question-b3']);
    $question_b4 = $conn->real_escape_string($_POST['question-b4']);
    $question_b5 = $conn->real_escape_string($_POST['question-b5']);
    $question_b6 = $conn->real_escape_string($_POST['question-b6']);
    $question_b7 = $conn->real_escape_string($_POST['question-b7']);
    $question_b8 = $conn->real_escape_string($_POST['question-b8']);
    $question_b9 = $conn->real_escape_string($_POST['question-b9']);

    // Bind parameters
    $stmt->bind_param(
        "sssssssssssssssssss",
        $game_name,
        $question_a1,
        $question_a2,
        $question_a3,
        $question_a4,
        $question_a5,
        $question_a6,
        $question_a7,
        $question_a8,
        $question_a9,
        $question_b1,
        $question_b2,
        $question_b3,
        $question_b4,
        $question_b5,
        $question_b6,
        $question_b7,
        $question_b8,
        $question_b9
    );

    // Execute SQL statement
    if ($stmt->execute()) {
        echo "<script>alert('Game created successfully');</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="style.css">
    <title>Win80x</title>
    <style>
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .sections {
            display: flex;
            justify-content: space-between;
        }

        .section {
            width: 45%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 10px;
            background-color: #f9f9f9;
        }

        h2 {
            margin-bottom: 10px;
            text-align: center;
        }

        .select-container {
            margin-bottom: 15px;
        }

        .select-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .select-container select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .create-button {
            display: block;
            width: 100%;
            padding: 15px;
            font-size: 18px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        .create-button:hover {
            background-color: #218838;
        }
    </style>
    <style>
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            text-align: center;
            /* Center-aligns the content within the container */
        }

        .sections {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .section {
            flex: 1;
            margin-right: 20px;
        }

        .section:last-child {
            margin-right: 0;
        }

        .select-container {
            margin-bottom: 10px;
            text-align: left;
            /* Left-aligns text within select-container */
        }

        .select-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .select-container select,
        .select-container input {
            width: 100%;
            /* Makes the input and select take the full width of the container */
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .create-button {
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .create-button:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .sections {
                flex-direction: column;
            }

            .section {
                margin-right: 0;
                margin-bottom: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <?php include 'tabbing.php'; ?>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <?php include 'header.php'; ?>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="container">
                <h1>Select Questions</h1>
                <form id="main-form" method="post" action="">
                    <!-- Input for Game Name -->
                    <div class="select-container"
                        style="text-align: center; max-width: 400px; margin: auto; margin-bottom: 10px;">
                        <label for="game-name">Game Name</label>
                        <input type="text" id="game-name" name="game-name" required>
                    </div>

                    <!-- Sections Container -->
                    <div class="sections">
                        <!-- Section A -->
                        <div class="section" id="section-a">
                            <h2>Section A</h2>
                            <?php
                            // Fetch questions for Section A
                            $sql = "SELECT id, question FROM questions WHERE set_type = 'A'";
                            $result = $conn->query($sql);
                            $questions = $result->num_rows > 0 ? $result->fetch_all(MYSQLI_ASSOC) : [['id' => '', 'question' => 'No questions available']];

                            // Loop through questions for Section A
                            for ($i = 1; $i <= 9; $i++) {
                                echo '<div class="select-container">
                                    <label for="question-a' . $i . '">Question ' . $i . '</label>
                                    <select id="question-a' . $i . '" name="question-a' . $i . '">';
                                foreach ($questions as $question) {
                                    echo '<option value="' . $question['id'] . '">' . $question['question'] . '</option>';
                                }
                                echo '</select></div>';
                            }
                            ?>
                        </div>

                        <!-- Section B -->
                        <div class="section" id="section-b">
                            <h2>Section B</h2>
                            <?php
                            // Fetch questions for Section B
                            $sqlB = "SELECT id, question FROM questions WHERE set_type = 'B'";
                            $resultB = $conn->query($sqlB);
                            $questionsB = $resultB->num_rows > 0 ? $resultB->fetch_all(MYSQLI_ASSOC) : [['id' => '', 'question' => 'No questions available']];

                            // Loop through questions for Section B
                            for ($i = 1; $i <= 9; $i++) {
                                echo '<div class="select-container">
                                    <label for="question-b' . $i . '">Question ' . $i . '</label>
                                    <select id="question-b' . $i . '" name="question-b' . $i . '">';
                                foreach ($questionsB as $question) {
                                    echo '<option value="' . $question['id'] . '">' . $question['question'] . '</option>';
                                }
                                echo '</select></div>';
                            }

                            ?>
                        </div>
                    </div>

                    <!-- Create Button -->
                    <button type="submit" class="create-button">Create</button>
                </form>
            </div>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="script.js"></script>
</body>

</html>