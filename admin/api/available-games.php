<?php
header("Content-Type: application/json");
include '../connection.php';
$response = array();

// Query to fetch scheduled games for today
$sql = "SELECT 
    scheduled_games.*, 
    games.id AS gameid, 
    games.result_declared AS game_status, 
    games.game_name AS game_name 
FROM 
    scheduled_games
JOIN 
    games ON scheduled_games.game_id = games.id 
WHERE
    games.result_declared = 0 
    AND DATE(scheduled_games.game_date) = CURDATE()
";

$result = $conn->query($sql);

// Check if there are results
if ($result->num_rows > 0) {
    $games = array();

    // Fetch all results
    while ($row = $result->fetch_assoc()) {
        $games[] = array(
            'scheduled_game_id' => $row['id'],
            'game_id' => $row['gameid'],
            'game_name' => $row['game_name'],
            'game_date' => $row['game_date']
        );
    }

    $response['success'] = true;
    $response['games'] = $games;
} else {
    $response['success'] = false;
    $response['message'] = 'No games scheduled for today';
}

// Output JSON response
echo json_encode($response);

// Close the database connection
$conn->close();
