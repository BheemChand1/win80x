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
	<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

	<title>80 Times</title>
	<style>
		/* General styles for the main section */
		main {
			padding: 20px;
			font-family: Arial, sans-serif;
		}

		/* Styles for the Create Question section */
		.create-question {
			margin-bottom: 20px;
			padding: 20px;
			border: 1px solid #ddd;
			border-radius: 8px;
			background-color: #f9f9f9;
		}

		.create-question .head h3 {
			margin-bottom: 15px;
			font-size: 24px;
		}

		.create-question form div {
			margin-bottom: 15px;
		}

		/* Labels for inputs */
		.create-question label {
			display: block;
			font-weight: bold;
			margin-bottom: 5px;
		}

		/* Input fields styling */
		.create-question input[type="text"],
		.create-question select {
			width: 100%;
			padding: 10px;
			border: 1px solid #ccc;
			border-radius: 4px;
			font-size: 16px;
		}

		/* Submit button styling */
		.create-question button[type="submit"] {
			padding: 10px 20px;
			background-color: #007bff;
			color: white;
			border: none;
			border-radius: 4px;
			font-size: 16px;
			cursor: pointer;
		}

		.create-question button[type="submit"]:hover {
			background-color: #0056b3;
		}

		/* Table styling */
		.table-data table button {
			padding: 8px 12px;
			background-color: #007bff;
			color: white;
			border: none;
			border-radius: 4px;
			cursor: pointer;
		}

		.table-data table button:hover {
			background-color: #0056b3;
		}

		.table-data table button:nth-child(2) {
			background-color: #dc3545;
		}

		.table-data table button:nth-child(2):hover {
			background-color: #c82333;
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

			<!-- Create or Edit Question Form -->
			<div class="create-question">
				<div class="head">
					<h3 id="form-title">Create Question</h3>
				</div>
			<form id="question-form">
    <input type="hidden" name="action" value="create_or_update"> <!-- Add this hidden input -->
    <div>
        <label for="set">Select Set:</label>
        <select id="set" name="set" required>
            <option value="A">Set A</option>
            <option value="B">Set B</option>
        </select>
    </div>
    <div>
        <label for="question">Question:</label>
        <input type="text" id="question" name="question" required>
    </div>
    <div>
        <label>Answers:</label>
        <div>
            <input type="text" id="answer1" name="answer1" placeholder="Answer 1" required>
            <input type="radio" name="correct_answer" value="1" required> Correct
        </div>
        <div>
            <input type="text" id="answer2" name="answer2" placeholder="Answer 2" required>
            <input type="radio" name="correct_answer" value="2" required> Correct
        </div>
        <div>
            <input type="text" id="answer3" name="answer3" placeholder="Answer 3" required>
            <input type="radio" name="correct_answer" value="3" required> Correct
        </div>
        <div>
            <input type="text" id="answer4" name="answer4" placeholder="Answer 4" required>
            <input type="radio" name="correct_answer" value="4" required> Correct
        </div>
    </div>
    <input type="hidden" id="question_id" name="question_id">
    <button type="submit">Create</button>
</form>

			</div>

			<!-- Questions Table -->
			<div class="table-data">
				<div class="order">
					<div class="head">
						<h3>Questions</h3>
					</div>
					<table id="questions-table" class="">
						<thead>
							<tr>
								<th>Question</th>
								<th>Answer</th>
								<th>Set</th>
								<th>Created Date</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<!-- Questions will be populated here dynamically -->
						</tbody>
					</table>
				</div>
			</div>

			<script>
    $(document).ready(function() {
        // Initialize DataTable
        var table = $('#questions-table').DataTable();

        // Load questions
        function loadQuestions() {
            $.ajax({
                url: 'ajax_handler.php',
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    // Clear existing data
                    table.clear();

                    // Populate table
                    data.forEach(function(question) {
                        const answers = `
                            1. ${question.answer1} ${question.correct_answer == 1 ? '(Correct)' : ''}<br>
                            2. ${question.answer2} ${question.correct_answer == 2 ? '(Correct)' : ''}<br>
                            3. ${question.answer3} ${question.correct_answer == 3 ? '(Correct)' : ''}<br>
                            4. ${question.answer4} ${question.correct_answer == 4 ? '(Correct)' : ''}
                        `;

                        table.row.add([
                            question.question,
                            answers,
                            question.set_type,
                            new Date(question.created_at).toLocaleDateString(),
                            `<button class="edit-btn" data-id="${question.id}">Edit</button>
                             <button class="delete-btn" data-id="${question.id}">Delete</button>`
                        ]).draw();
                    });
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching questions:', error);
                }
            });
        }

        loadQuestions(); // Load on page load

        // Create or update question
        $('#question-form').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            console.log(formData); // Log the formData to check what is being sent

            $.ajax({
                url: 'ajax_handler.php',
                method: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Operation successful');
                        loadQuestions(); // Reload questions after successful operation
                        $('#question-form')[0].reset();
                        $('#question_id').val('');
                        $('#form-title').text('Create Question');
                    } else {
                        alert('Error occurred: ' + response.message);  // Show the SQL error message
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Response:', xhr.responseText);  // This will show the response
                    alert('Error occurred during submission.');
                }
            });
        });

        // Edit question
        $(document).on('click', '.edit-btn', function() {
            const question_id = $(this).data('id');
            $.ajax({
                url: 'ajax_handler.php',
                method: 'GET',
                data: { edit: question_id },
                dataType: 'json',
                success: function(question) {
                    if (question) {
                        $('#set').val(question.set_type);
                        $('#question').val(question.question);
                        $('#answer1').val(question.answer1);
                        $('#answer2').val(question.answer2);
                        $('#answer3').val(question.answer3);
                        $('#answer4').val(question.answer4);
                        $(`input[name="correct_answer"][value="${question.correct_answer}"]`).prop('checked', true);
                        $('#question_id').val(question.id);
                        $('#form-title').text('Edit Question');
                    } else {
                        alert('Failed to load question details');
                    }
                },
                error: function() {
                    alert('Error fetching question details');
                }
            });
        });

        // Delete question
        $(document).on('click', '.delete-btn', function() {
            if (!confirm('Are you sure you want to delete this question?')) return;

            const question_id = $(this).data('id');
            $.ajax({
                url: 'ajax_handler.php',
                method: 'POST',
                data: { action: 'delete', question_id: question_id },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        alert('Question deleted');
                        loadQuestions(); // Reload questions after successful deletion
                    } else {
                        alert('Error deleting question');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error deleting question:', error);
                }
            });
        });
    });
</script>


		</main>
		<!-- MAIN -->
	</section>
	<!-- CONTENT -->

	<script src="script.js"></script>

</body>

</html>
