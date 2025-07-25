<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['declare']) && isset($_POST['game_id'])) {
    $selected_game = (int)$_POST['game_id'];
    
    // First, check if results for this game have already been declared
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM gameplay_results WHERE game_id = ?");
    $check_stmt->bind_param("i", $selected_game);
    $check_stmt->execute();
    $result_check = $check_stmt->get_result();
    $row = $result_check->fetch_assoc();
    
    if ($row['count'] > 0) {
        // Results already declared for this game
        header("Location: declare_result.php?error=already_declared&game_id=$selected_game");
        exit;
    }
    
    $gameplay_rows = [];
    $total_collection = 0;
    $game_name = ""; // Initialize game_name variable

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
    $result = $stmt->get_result();
    $gameplay_rows = $result->fetch_all(MYSQLI_ASSOC);

    // Get the game name from the first row if available
    if (!empty($gameplay_rows)) {
        $game_name = $gameplay_rows[0]['game_name'];
    } else {
        // If no gameplay rows, get game name directly
        $game_stmt = $conn->prepare("SELECT game_name FROM games WHERE id = ?");
        $game_stmt->bind_param("i", $selected_game);
        $game_stmt->execute();
        $game_result = $game_stmt->get_result();
        if ($game_row = $game_result->fetch_assoc()) {
            $game_name = $game_row['game_name'];
        }
    }

    foreach ($gameplay_rows as &$gp) {
        $total_collection += $gp['price'];
        $gp['coupons'] = intval($gp['price'] / 100);
    }

    if (count($gameplay_rows) === 0) {
        header("Location: gameplays.php?error=no_data");
        exit;
    }

    // Calculate distribution pool (27% of total collection after deductions)
    // 30% income tax + 25% company deduction + 18% GST = 73% deduction
    $distribution_pool = $total_collection * 0.27;  // 27% of total collection

    // Top 3 Winners with specific percentages:
    // 1st place: 10% of 27% distribution pool
    // 2nd place: 8% of 27% distribution pool
    // 3rd place: 5% of 27% distribution pool
    // Total for top 3: 23% of the 27% distribution pool
    $winners = array_slice($gameplay_rows, 0, 3);

    // Get the fixed percentage shares for top 3 winners
    $first_place_share = $distribution_pool * 0.10;   // 10% of distribution pool
    $second_place_share = $distribution_pool * 0.08;  // 8% of distribution pool
    $third_place_share = $distribution_pool * 0.05;   // 5% of distribution pool

    // Top 3 shares stored in array for easy access
    $top_shares = [$first_place_share, $second_place_share, $third_place_share];

    // Track all winning users for notifications
    $winning_users = [];

    // Process the top 3 winners
    foreach ($winners as $index => $win) {
        $share = $top_shares[$index];
        
        // Insert into gameplay_results
        $stmt = $conn->prepare("INSERT INTO gameplay_results (game_id, user_id, winning_price, lucky_number) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iidd", $selected_game, $win['user_id'], $share, $win['lucky_number']);
        $stmt->execute();
        
        // Update user's wallet balance
        $update_wallet = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        $update_wallet->bind_param("di", $share, $win['user_id']);
        $update_wallet->execute();
        
        // Record transaction in user_transaction_history
        $transaction_type = "Credited";
        $transaction_for = "Winnings";
        
        $transaction_stmt = $conn->prepare("INSERT INTO user_transaction_history (user_id, amount, transaction_type, transaction_for) VALUES (?, ?, ?, ?)");
        $transaction_stmt->bind_param("idss", $win['user_id'], $share, $transaction_type, $transaction_for);
        $transaction_stmt->execute();
        
        // Add to winning users list with position-specific title
        $position_titles = ['1st Place', '2nd Place', '3rd Place'];
        $winning_users[] = [
            'user_id' => $win['user_id'],
            'name' => $win['user_name'],
            'winning_amount' => $share,
            'win_type' => $position_titles[$index]
        ];
    }

    // Pattern-based winners (77% of the distribution pool)
    // Ensure pattern number is always a positive two-digit number (00-99)
    $pattern_num = abs(count($gameplay_rows) % 100);

    // Make sure pattern number is always two digits (00-99)
    if ($pattern_num < 10) {
        $pattern_display = "0" . $pattern_num; // For display purposes (e.g., "05" instead of "5")
    } else {
        $pattern_display = (string)$pattern_num;
    }

    // Store the pattern number in the games table
    $store_pattern = $conn->prepare("UPDATE games SET pattern_lucky_number = ? WHERE id = ?");
    $store_pattern->bind_param("ii", $pattern_num, $selected_game);
    $store_pattern->execute();

    // Find winners whose lucky number matches the pattern number
    $pattern_winners = array_filter($gameplay_rows, fn($g) => $g['lucky_number'] == $pattern_num);

    if (count($pattern_winners) > 0) {
        // Calculate the pattern winners pool (77% of the 27% distribution pool)
        $pattern_pool = $distribution_pool * 0.77;  // 77% of distribution pool
        $pattern_total_coupons = array_sum(array_column($pattern_winners, 'coupons'));
        
        foreach ($pattern_winners as $pw) {
            // Distribute based on proportional coupon contribution
            $pattern_share = ($pw['coupons'] / $pattern_total_coupons) * $pattern_pool;
            
            // Insert into gameplay_results
            $stmt = $conn->prepare("INSERT INTO gameplay_results (game_id, user_id, winning_price, lucky_number) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iidd", $selected_game, $pw['user_id'], $pattern_share, $pw['lucky_number']);
            $stmt->execute();
            
            // Update user's wallet balance
            $update_wallet = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $update_wallet->bind_param("di", $pattern_share, $pw['user_id']);
            $update_wallet->execute();
            
            // Record transaction in user_transaction_history
            $transaction_type = "Credited";
            $transaction_for = "Winnings";
            
            $transaction_stmt = $conn->prepare("INSERT INTO user_transaction_history (user_id, amount, transaction_type, transaction_for) VALUES (?, ?, ?, ?)");
            $transaction_stmt->bind_param("idss", $pw['user_id'], $pattern_share, $transaction_type, $transaction_for);
            $transaction_stmt->execute();
            
            // Add to winning users list
            $winning_users[] = [
                'user_id' => $pw['user_id'],
                'name' => $pw['user_name'],
                'winning_amount' => $pattern_share,
                'win_type' => 'Pattern Match (' . $pattern_display . ')'
            ];
        }
    }
    
    // Send notifications to all winning users
    foreach ($winning_users as $winner) {
        // Get user's notification token
        $token_stmt = $conn->prepare("SELECT expo_notification_token, name FROM users WHERE id = ?");
        $token_stmt->bind_param("i", $winner['user_id']);
        $token_stmt->execute();
        $token_result = $token_stmt->get_result();
        $user_data = $token_result->fetch_assoc();
        
        if (!empty($user_data['expo_notification_token'])) {
            $expo_token = $user_data['expo_notification_token'];
            $username = $user_data['name'];
            $winning_amount = number_format($winner['winning_amount'], 2);
            
            $data = [
                'to' => $expo_token,
                'sound' => 'default',
                'title' => "Congratulations " . $username . "! 🎉",
                'body' => "You've won ₹" . $winning_amount . " in " . $game_name . " as a " . $winner['win_type'] . " winner!"
            ];
            
            $json_data = json_encode($data);
            
            // Initialize cURL session
            $ch = curl_init('https://exp.host/--/api/v2/push/send');
            
            // Set cURL options
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Content-Length: ' . strlen($json_data)
            ]);
            
            // Execute cURL request
            $result = curl_exec($ch);
            
            // Check for errors
            if (curl_errno($ch)) {
                // Log error if needed
                error_log('Expo Push Notification Error: ' . curl_error($ch));
            }
            
            // Close cURL session
            curl_close($ch);
        }
    }
    
    // Update game status to indicate results have been declared
    $update_game = $conn->prepare("UPDATE games SET result_declared = 1 WHERE id = ?");
    $update_game->bind_param("i", $selected_game);
    $update_game->execute();

    // Redirect
    header("Location: results.php?game_id=$selected_game&status=declared");
    exit;
}
?>
