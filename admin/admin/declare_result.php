<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../connection.php';

// Fetch games for dropdown
$games = $conn->query("SELECT id, game_name, result_declared FROM games");
// Handle form submission 
$selected_game = isset($_POST['game_id']) ? (int)$_POST['game_id'] : 0;
$gameplays = [];
$gameplay_rows = [];
$total_collection = 0;
$result_already_declared = false;

if ($selected_game) {
    // Check if results already declared
    $check_stmt = $conn->prepare("SELECT result_declared FROM games WHERE id = ?");
    $check_stmt->bind_param("i", $selected_game);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $check_row = $check_result->fetch_assoc();
    $result_already_declared = ($check_row && $check_row['result_declared'] == 1);

    $stmt = $conn->prepare("
        SELECT g.id AS gameplay_id, g.price, g.unique_id, g.lucky_number, g.created_at,
               g.user_id, u.name AS user_name, ga.game_name
        FROM gameplays g
        JOIN users u ON g.user_id = u.id
        JOIN games ga ON g.game_id = ga.id
        WHERE g.game_id = ? AND g.status = 1
        ORDER BY g.lucky_number DESC, g.created_at DESC
    ");
    $stmt->bind_param("i", $selected_game);
    $stmt->execute();
    $gameplays = $stmt->get_result();
    $gameplay_rows = $gameplays->fetch_all(MYSQLI_ASSOC);

    // Calculate total collection
    foreach ($gameplay_rows as $gp) {
        $total_collection += $gp['price'];
    }
}
?>

<!DOCTYPE html>

<html>
<head>
    <title>Gameplays Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f7f8fc;
            color: #333;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        h2 {
            font-weight: 600;
            background: linear-gradient(90deg, #6a11cb, #2575fc);
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
        }

        .form-select, .btn {
            border-radius: 8px;
        }

        .btn-primary {
            background: #2575fc;
            border: none;
        }

        .btn-primary:hover {
            background: #1a5edb;
        }

        .btn-success {
            background: #28c76f;
            border: none;
        }

        .btn-success:hover {
            background: #20b364;
        }

        table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }

        thead {
            background: linear-gradient(to right, #6a11cb, #2575fc);
            color: white;
        }

        tfoot {
            background-color: #f1f1f1;
            font-weight: 600;
        }

        .table td, .table th {
            vertical-align: middle;
        }

        .alert-success {
            background: #d1f7dd;
            color: #198754;
            border-radius: 8px;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #842029;
            border-radius: 8px;
        }

        .no-data {
            font-style: italic;
            color: #888;
        }
        
        .result-declared {
            background-color: #ffc107;
            color: #000;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 0.8rem;
            margin-left: 8px;
        }
    </style>
</head>
<body>
<div class="container-fluid mt-1">
    <h2 class="text-center">🎮 Gameplays Dashboard</h2>
    <div class="text-end mb-3">
    <a href="index.php" class="btn btn-outline-secondary">🏠 Back to Home</a>
</div>

    
    <?php if (isset($_GET['error']) && $_GET['error'] == 'already_declared'): ?>
    <div class="alert alert-danger mb-4">
        ⚠️ Results for this game have already been declared! You cannot declare results again.
    </div>
    <?php endif; ?>
    
    <form method="POST" class="mb-4">
        <div class="row g-2">
            <div class="col-md-6">
                <select name="game_id" class="form-select" required>
                    <option value=""> Select a Game</option>
                    <?php while($row = $games->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= $row['id'] == $selected_game ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['game_name']) ?>
                            <?php if ($row['result_declared'] == 1): ?>
                                <span class="result-declared">Results Declared</span>
                            <?php endif; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100" type="submit">🔍 Load</button>
            </div>
        </div>
    </form>

    <?php if (!empty($gameplay_rows)): ?>
        <?php if ($result_already_declared): ?>
            <div class="alert alert-warning mb-4">
                ⚠️ Results for this game have already been declared! You cannot declare results again.
            </div>
        <?php endif; ?>
        
        <form method="POST" action="submit_declare_result.php">
            <input type="hidden" name="game_id" value="<?= $selected_game ?>">
            <table class="table table-bordered table-striped text-center align-middle">
                <thead>
                    <tr>
                        <th>ID</th><th>User</th><th>Game</th><th>Price</th><th>Unique ID</th>
                        <th>Lucky No.</th><th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gameplay_rows as $gp): ?>
                        <tr>
                            <td><?= $gp['gameplay_id'] ?></td>
                            <td><?= htmlspecialchars($gp['user_name']) ?></td>
                            <td><?= htmlspecialchars($gp['game_name']) ?></td>
                            <td>₹ <?= number_format($gp['price']) ?><br><small>(<?= intval($gp['price'] / 100) ?> coupons)</small></td>
                            <td><?= htmlspecialchars($gp['unique_id']) ?></td>
                            <td><?= $gp['lucky_number'] ?></td>
                            <td><?= date('d M Y, g:i:s A', strtotime($gp['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3">💰 Total Collection</td>
                        <td colspan="4">₹ <?= number_format($total_collection, 2) ?></td>
                    </tr>
                </tfoot>
            </table>

            <div class="mt-4 text-end">
                <?php if (!$result_already_declared): ?>
                    <button class="btn btn-success" name="declare" value="1" type="submit"> Declare Results</button>
                <?php else: ?>
                    <button class="btn btn-secondary" disabled>Results Already Declared</button>
                <?php endif; ?>
            </div>
        </form>
    <?php elseif ($selected_game): ?>
        <p class="no-data">No gameplay records found for the selected game.</p>
    <?php endif; ?>
</div>

</body>
</html>

