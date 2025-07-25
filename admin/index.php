<?php include 'connection.php'; ?>
<!doctype html>
<html lang="en">
  <head>
  	<title>Win80x</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<link href="https://fonts.googleapis.com/css?family=Lato:300,400,700&display=swap" rel="stylesheet">

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<link rel="stylesheet" href="css/style.css">

	</head>
	<body class="img js-fullheight" style="background-image: url(images/bg.jpg);">
	<section class="ftco-section">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-6 text-center mb-5">
					<h2 class="heading-section">Win80x</h2>
				</div>
			</div>
			<div class="row justify-content-center">
				<div class="col-md-6 col-lg-4">
					<div class="login-wrap p-0">
		      	<h3 class="mb-4 text-center">Admin Login</h3>
		      	<?php

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);

    // Execute statement
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
    // Successful login
    echo "<script>window.location.href = 'admin/index.php';</script>";
    exit();
} else {
    // Invalid credentials
    echo "<script>alert('Invalid username or password.');</script>";
}


    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>

		      	<form action="" method="POST" class="signin-form">
    <div class="form-group">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
    </div>
    <div class="form-group">
        <input id="password-field" type="password" name="password" class="form-control" placeholder="Password" required>
        <span toggle="#password-field" class="fa fa-fw fa-eye field-icon toggle-password"></span>
    </div>
    <div class="form-group">
        <button type="submit" class="form-control btn btn-primary submit px-3">Log In</button>
    </div>
    <div class="form-group d-md-flex">
        <div class="w-50">
            <label class="checkbox-wrap checkbox-primary">Remember Me
                <input type="checkbox" checked>
                <span class="checkmark"></span>
            </label>
        </div>
    </div>
</form>
				</div>
			</div>
		</div>
	</section>

	<script src="js/jquery.min.js"></script>
  <script src="js/popper.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/main.js"></script>

	</body>
</html>

