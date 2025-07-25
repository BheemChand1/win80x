<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Win80x</title>
	<!-- Boxicons -->
	<link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
	<!-- My CSS -->

	<link rel="stylesheet" href="style.css">

	<!-- Include DataTables CSS -->
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
	<!-- Include jQuery -->
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<!-- Include DataTables JS -->
	<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
	<script>
		$(document).ready(function () {
			$('#userTable').DataTable(); // Initialize DataTables
		});
	</script>
	<title>80 Times</title>
	<style>
		/* for table */
		table {
			width: 100%;
			border-collapse: collapse;
		}

		th,
		td {
			padding: 10px;
			text-align: center;
		}

		img {
			max-width: 40px;
			border-radius: 50%;
			vertical-align: middle;
		}

		p {
			margin: 0;
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
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Users List</h3>
						<i class='bx bx-filter'></i>
					</div>
					<?php
					include './connection.php';

					// Query to fetch data
					$sql = "SELECT name, game_id, email, mobile_number, wallet_balance, joined_date FROM users";
					$result = $conn->query($sql);

					// Start HTML table
					echo '<table id="userTable" class="display">
        <thead>
            <tr>
                <th>Name</th>
                <th>Game ID</th>
                <th>Email Adress</th>
                <th>Mobile Number</th>
                <th>Wallet Balance</th>
                <th>Joined Date</th>
            </tr>
        </thead>
        <tbody>';

					// Check if there are results and output data
					if ($result->num_rows > 0) {
						while ($row = $result->fetch_assoc()) {
							echo '<tr>
                <td>
                    <img src="img/people.png" alt="Profile Picture" style="width:50px;height:50px;">
                    <p>' . htmlspecialchars($row["name"]) . '</p>
                </td>
                <td>' . htmlspecialchars($row["game_id"]) . '</td>
                <td>' . htmlspecialchars($row["email"]) . '</td>
                <td>' . htmlspecialchars($row["mobile_number"]) . '</td>
                <td>' . htmlspecialchars($row["wallet_balance"]) . '</td>
                <td>' . htmlspecialchars($row["joined_date"]) . '</td>
              </tr>';
						}
					} else {
						echo '<tr><td colspan="6">No records found</td></tr>';
					}

					// End HTML table
					echo '</tbody>
    </table>';

					// Close connection
					$conn->close();
					?>

				</div>
			</div>
		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->


	<script src="script.js"></script>

</body>

</html>