<?php
// Database connection
$host = 'localhost';
$db = 'win80x_db';
$user = 'win80x_win80x';
$pass = 'k*NSZnA0n;5q';
try {
	$pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
	die("Could not connect to the database: " . $e->getMessage());
}

// Load games into select box
function loadGames($pdo)
{
	$stmt = $pdo->query("SELECT id, game_name FROM games");
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		echo "<option value='" . $row['id'] . "'>" . $row['game_name'] . "</option>";
	}
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$gameId = $_POST['game'];
	$gameDate = $_POST['game_date'];
	$gameTime = $_POST['game_time'];

	// Insert scheduled game into the database
	$stmt = $pdo->prepare("INSERT INTO scheduled_games (game_id, game_date, game_time) VALUES (?, ?, ?)");
	if ($stmt->execute([$gameId, $gameDate, $gameTime])) {

		echo "<script>alert('Game scheduled successfully!');</script>";
	} else {
		echo "Error scheduling the game.";
	}
}
?>

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
	<style>
		h1,
		h2 {
			text-align: center;
			color: #333;
		}

		form {
			display: flex;
			flex-direction: column;
		}

		label {
			margin: 10px 0 5px;
			font-weight: bold;
		}

		select,
		input[type="date"],
		input[type="time"],
		button {
			padding: 10px;
			margin-bottom: 15px;
			border: 1px solid #ccc;
			border-radius: 5px;
			font-size: 16px;
		}

		button {
			background-color: #4CAF50;
			color: white;
			cursor: pointer;
		}

		button:hover {
			background-color: #45a049;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			margin-top: 20px;
		}

		table,
		th,
		td {
			border: 1px solid #ccc;
		}

		th,
		td {
			padding: 12px;
			text-align: center;
		}

		th {
			background-color: #f2f2f2;
		}

		tr:nth-child(even) {
			background-color: #f9f9f9;
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
				<h1>Schedule a Game</h1>
				<form id="gameForm" method="POST">
					<label for="gameSelect">Select Game:</label>
					<select id="gameSelect" name="game" required>
						<option value="">--Select a Game--</option>
						<?php loadGames($pdo); ?>
					</select>

					<label for="gameDate">Select Date:</label>
					<input type="date" id="gameDate" name="game_date" required>

					<label for="gameTime">Select Time:</label>
					<input type="time" id="gameTime" name="game_time" required>

					<button type="submit">Schedule Game</button>
				</form>

				<h2>Scheduled Games</h2>
				<table id="scheduleTable">
					<thead>
						<tr>
							<th>Game</th>
							<th>Date</th>
							<th>Time</th>
						</tr>
					</thead>
					<tbody>
						<?php
						// Load scheduled games
						$stmt = $pdo->query("SELECT g.game_name, sg.game_date, sg.game_time 
                                     FROM scheduled_games sg
                                     JOIN games g ON sg.game_id = g.id");
						while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
							echo "<tr>
                            <td>" . $row['game_name'] . "</td>
                            <td>" . $row['game_date'] . "</td>
                            <td>" . $row['game_time'] . "</td>
                          </tr>";
						}
						?>
					</tbody>
				</table>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->


	<script src="script.js"></script>

</body>

</html>