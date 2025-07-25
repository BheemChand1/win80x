<?php
include '../connection.php';

$game_id = isset($_GET['game_id']) ? (int)$_GET['game_id'] : 0;
$results = [];
$games = [];
$pattern_number = null;

// Fetch all games for the dropdown
$gameQuery = $conn->query("SELECT id, game_name FROM games ORDER BY game_name ASC");
if ($gameQuery) {
    $games = $gameQuery->fetch_all(MYSQLI_ASSOC);
}

// Fetch results if game_id is provided
if ($game_id) {
    // Get the pattern lucky number for this game
    $patternStmt = $conn->prepare("SELECT pattern_lucky_number FROM games WHERE id = ?");
    $patternStmt->bind_param("i", $game_id);
    $patternStmt->execute();
    $patternResult = $patternStmt->get_result();
    if ($patternRow = $patternResult->fetch_assoc()) {
        $pattern_number = $patternRow['pattern_lucky_number'];
    }

    // Get all winners with ranking information
    $stmt = $conn->prepare("
        SELECT u.name, gr.winning_price, g.game_name, gr.lucky_number,
            CASE 
                WHEN gr.winning_price >= (SELECT MAX(winning_price) * 0.9 FROM gameplay_results WHERE game_id = ?) THEN '1st Place'
                WHEN gr.winning_price >= (SELECT MAX(winning_price) * 0.7 FROM gameplay_results WHERE game_id = ?) THEN '2nd Place'
                WHEN gr.winning_price >= (SELECT MAX(winning_price) * 0.4 FROM gameplay_results WHERE game_id = ?) THEN '3rd Place'
                ELSE 'Pattern Match'
            END AS rank_type
        FROM gameplay_results gr
        JOIN users u ON gr.user_id = u.id
        JOIN games g ON gr.game_id = g.id
        WHERE gr.game_id = ?
        ORDER BY gr.winning_price DESC
    ");
    $stmt->bind_param("iiii", $game_id, $game_id, $game_id, $game_id);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Result Winners</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #f0f4ff, #e0e7ff);
            color: #333;
        }

        .container {
            background: white;
            padding: 40px;
            margin-top: 50px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }

        h2 {
            font-weight: 600;
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
        }

        .winner-card {
            background: #f9fbff;
            border: 1px solid #dbeafe;
            border-left: 8px solid #3b82f6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .winner-card:hover {
            box-shadow: 0 8px 24px rgba(59, 130, 246, 0.2);
        }

        .winner-name {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .winner-price {
            font-size: 1.1rem;
            color: #10b981;
        }

        .btn-custom {
            background: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: background 0.2s;
            text-decoration: none;
        }

        .btn-custom:hover {
            background: #1d4ed8;
        }

        .select-form {
            max-width: 400px;
            margin: 0 auto 30px;
        }
        
        .badge {
            font-size: 0.85rem;
            padding: 5px 10px;
            border-radius: 50px;
            margin-left: 10px;
        }
        
        .badge-1st {
            background-color: #ffd700;
            color: #333;
        }
        
        .badge-2nd {
            background-color: #c0c0c0;
            color: #333;
        }
        
        .badge-3rd {
            background-color: #cd7f32;
            color: #fff;
        }
        
        .badge-pattern {
            background-color: #3b82f6;
            color: #fff;
        }
        
        .pattern-number {
            background-color: #f3f4f6;
            padding: 12px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .pattern-value {
            font-weight: bold;
            color: #3b82f6;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🎉 Game Winners</h2>

        <?php if (!$game_id): ?>
            <form method="GET" class="select-form">
                <div class="mb-3">
                    <label for="game_id" class="form-label">Select a Game to View Results:</label>
                    <select name="game_id" id="game_id" class="form-select" required>
                        <option value="">-- Choose Game --</option>
                        <?php foreach ($games as $game): ?>
                            <option value="<?= $game['id'] ?>"><?= htmlspecialchars($game['game_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn-custom">Show Results</button>
                </div>
            </form>
        <?php elseif (!empty($results)): ?>
            <?php if ($pattern_number !== null): ?>
                <div class="pattern-number">
                    Pattern Lucky Number: <span class="pattern-value"><?= str_pad($pattern_number, 2, '0', STR_PAD_LEFT) ?></span>
                </div>
            <?php endif; ?>
            
            <?php foreach ($results as $index => $res): ?>
                <div class="winner-card">
                    <div class="winner-name">
                        🏅 <?= htmlspecialchars($res['name']) ?>
                        <?php 
                        $badgeClass = '';
                        if ($res['rank_type'] === '1st Place') $badgeClass = 'badge-1st';
                        else if ($res['rank_type'] === '2nd Place') $badgeClass = 'badge-2nd';
                        else if ($res['rank_type'] === '3rd Place') $badgeClass = 'badge-3rd';
                        else $badgeClass = 'badge-pattern';
                        ?>
                        <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($res['rank_type']) ?></span>
                    </div>
                    <div class="winner-price">🏆 ₹ <?= number_format($res['winning_price'], 2) ?></div>
                    <div><strong>Lucky Number:</strong> <?= str_pad($res['lucky_number'], 2, '0', STR_PAD_LEFT) ?></div>
                    <div><strong>Game:</strong> <?= htmlspecialchars($res['game_name']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted text-center">No results found for this game.</p>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn-custom me-2">🏠 Back to Home</a>
            <a href="declare_result.php" class="btn-custom">🎮 Back to Gameplays</a>
        </div>
    </div>
</body>
</html>
