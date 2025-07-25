<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include connection
include '../connection.php';

// Initialize response
$response = [
    'status' => 'error',
    'message' => '',
    'data' => []
];

// Support GET and POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    // Get game_id
    $game_id = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 
               (isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0);

    if ($game_id <= 0) {
        $response['message'] = 'Invalid game ID provided';
        echo json_encode($response);
        exit;
    }

    try {
        // Fetch game results
        $stmt = $conn->prepare("
            SELECT 
                g.game_name,
                u.name,
                gr.winning_price,
                gr.lucky_number
            FROM 
                gameplay_results gr
            JOIN 
                games g ON gr.game_id = g.id
            JOIN 
                users u ON gr.user_id = u.id
            WHERE 
                gr.game_id = ?
            ORDER BY 
                gr.winning_price DESC
        ");

        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $winners = [];

            while ($row = $result->fetch_assoc()) {
                $winners[] = [
                    'game_name' => $row['game_name'],
                    'user_name' => $row['name'],
                    'winning_price' => (float)$row['winning_price'],
                    'lucky_number' => isset($row['lucky_number']) ? (int)$row['lucky_number'] : 0
                ];
            }

            $response['status'] = 'success';
            $response['message'] = 'Results found';
            $response['data'] = $winners;
        } else {
            $response['message'] = 'result not announced yet';
        }

    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }

} else {
    $response['message'] = 'Invalid request method. Use GET or POST.';
}

echo json_encode($response);
exit;
?>
