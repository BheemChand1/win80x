<?php include 'connection.php'; ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->
	<link rel="stylesheet" href="style.css">

	<title>Win80x</title>
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
			<div class="head-title">
				<div class="left">
					<ul class="breadcrumb">
						<li>
							<a href="#">Dashboard</a>
						</li>
						<li><i class='bx bx-chevron-right'></i></li>
						<li>
							<a class="active" href="#">Home</a>
						</li>
					</ul>
				</div>

			</div>
<?php

// SQL query to count users
$userQuery = "SELECT COUNT(*) AS user_count FROM users";
$userResult = $conn->query($userQuery);
$userCount = ($userResult->num_rows > 0) ? $userResult->fetch_assoc()['user_count'] : 0;

// SQL query to count total games
$gameQuery = "SELECT COUNT(*) AS total_games FROM games";
$gameResult = $conn->query($gameQuery);
$totalGames = ($gameResult->num_rows > 0) ? $gameResult->fetch_assoc()['total_games'] : 0;

// SQL query to count scheduled games
$scheduledGameQuery = "SELECT COUNT(*) AS scheduled_games FROM scheduled_games";
$scheduledGameResult = $conn->query($scheduledGameQuery);
$scheduledGames = ($scheduledGameResult->num_rows > 0) ? $scheduledGameResult->fetch_assoc()['scheduled_games'] : 0;

// SQL query to count scheduled games
$pendingWithdrwalQuery = "SELECT COUNT(*) AS pendingWithdrwal FROM withdrawal_requests WHERE status = 'pending'";
$pendingWithdrwalResult = $conn->query($pendingWithdrwalQuery);
$pendingWithdrwal = ($pendingWithdrwalResult->num_rows > 0) ? $pendingWithdrwalResult->fetch_assoc()['pendingWithdrwal'] : 0;


?>
		<ul class="box-info">
    <a href="users.php" style="text-decoration: none; color: inherit;">
        <li>
            <i class='bx bxs-group'></i>
            <span class="text">
                <h3><?php echo $userCount; ?></h3>
                <p>Users</p>
            </span>
        </li>
    </a>

    <a href="#" style="text-decoration: none; color: inherit;">
        <li>
            <i class='bx bxs-game'></i>
            <span class="text">
                <h3><?php echo $totalGames; ?></h3>
                <p>Total Games</p>
            </span>
        </li>
    </a>

    <a href="schedule_game.php" style="text-decoration: none; color: inherit;">
        <li>
            <i class='bx bx-time'></i>
            <span class="text">
                <h3><?php echo $scheduledGames; ?></h3>
                <p>Scheduled Games</p>
            </span>
        </li>
    </a>

    <a href="withdrawal_requests.php" style="text-decoration: none; color: inherit;">
        <li>
            <i class='bx bx-time'></i>
            <span class="text">
                <h3><?php echo $pendingWithdrwal; ?></h3>
                <p>Pending Withdrawals</p>
            </span>
        </li>
    </a>
</ul>




<?php

$latestTransactions = [];

try {
    $stmt = $conn->prepare("
        SELECT uth.amount, uth.transaction_type, uth.created_at,
               u.name AS user_name
        FROM user_transaction_history uth
        JOIN users u ON uth.user_id = u.id
        ORDER BY uth.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $latestTransactions[] = $row;
    }

    $stmt->close();
} catch (Exception $e) {
    // handle error silently or log it if needed
}

?>
			<div class="table-data">
    <div class="order">
        <div class="head">
            <h3>Latest 5 Transactions</h3>
            <a href="transaction_history.php" style="color: inherit; text-decoration: none;">
    <i class='bx bx-time' style="cursor: pointer;"></i>
</a>

        </div>
        <table>
            <thead>
                <tr>
                    <th>User</th>
                    <th>Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($latestTransactions)): ?>
                    <?php foreach ($latestTransactions as $txn): ?>
                        <tr>
                            <td>
                                <img src="img/people.png" alt="User">
                                <p><?= htmlspecialchars($txn['user_name']) ?></p>
                            </td>
                            <td><?= date('d-m-Y h:i A', strtotime($txn['created_at'])) ?></td>
                            <td>
                                <span class="status <?= $txn['transaction_type'] === 'Credited' ? 'completed' : 'pending' ?>">
                                    <?= $txn['transaction_type'] === 'Credited' ? '+' : '-' ?>₹<?= number_format($txn['amount'], 2) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted">No recent transactions.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->


	<script src="script.js"></script>
</body>

</html>