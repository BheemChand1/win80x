<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../connection.php';

$error = "";
$transactions = [];

// Date filters (default: current month)
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date = $_GET['to_date'] ?? date('Y-m-t');

try {
    $where = "WHERE 1";
    $params = [];
    $types = "";

    if (!empty($from_date) && !empty($to_date)) {
        $where .= " AND DATE(uth.created_at) BETWEEN ? AND ?";
        $params[] = $from_date;
        $params[] = $to_date;
        $types .= "ss";
    }

    $sql = "
        SELECT 
            uth.id,
            uth.amount,
            uth.transaction_type,
            uth.transaction_for,
            uth.created_at,
            u.id AS user_id,
            u.name AS user_name,
            u.email AS user_email
        FROM user_transaction_history uth
        JOIN users u ON uth.user_id = u.id
        $where
        ORDER BY uth.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Transaction History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .table thead th {
            background-color: #343a40;
            color: #fff;
        }

        .btn-home {
            background-color: #0d6efd;
            color: white;
        }

        .btn-home:hover {
            background-color: #0b5ed7;
        }

        /* Custom DataTables styling */
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            margin: 10px 0;
        }

        .dt-buttons {
            margin-bottom: 15px;
        }

        .credit-amount {
            color: #198754 !important;
            font-weight: bold;
        }

        .debit-amount {
            color: #dc3545 !important;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="page-header mb-4">
        <h2 class="fw-bold">💳 All Users' Transaction History</h2>
        <a href="index.php" class="btn btn-home btn-lg rounded-pill px-4">🏠 Home</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3" method="GET">
                <div class="col-md-4">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" value="<?= htmlspecialchars($from_date) ?>">
                </div>
                <div class="col-md-4">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" value="<?= htmlspecialchars($to_date) ?>">
                </div>
                <div class="col-md-4 align-self-end">
                    <button type="submit" class="btn btn-primary me-2">🔍 Filter</button>
                    <a href="?" class="btn btn-outline-secondary">♻️ Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <table id="transactionTable" class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Purpose</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($transactions)): ?>
                        <?php foreach ($transactions as $index => $txn): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($txn['user_name']) ?></td>
                                <td><?= htmlspecialchars($txn['user_email']) ?></td>
                                <td class="<?= $txn['transaction_type'] === 'Credited' ? 'credit-amount' : 'debit-amount' ?>">
                                    <?= $txn['transaction_type'] === 'Credited' ? '+' : '-' ?>
                                    ₹<?= number_format($txn['amount'], 2) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $txn['transaction_type'] === 'credit' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= ucfirst($txn['transaction_type']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($txn['transaction_for']) ?></td>
                                <td><?= date('d-m-Y h:i A', strtotime($txn['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">No transactions found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables JS -->
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<!-- DataTables Buttons -->
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

<script>
$(document).ready(function() {
    $('#transactionTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 25,
        "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        "order": [[6, "desc"]], // Sort by date column (index 6) in descending order
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'csv',
                className: 'btn btn-success btn-sm',
                text: '📄 Export CSV'
            },
            {
                extend: 'pdf',
                className: 'btn btn-danger btn-sm',
                text: '📋 Export PDF'
            }
        ],
        "language": {
            "search": "Search transactions:",
            "lengthMenu": "Show _MENU_ transactions per page",
            "info": "Showing _START_ to _END_ of _TOTAL_ transactions",
            "infoEmpty": "No transactions available",
            "infoFiltered": "(filtered from _MAX_ total transactions)",
            "zeroRecords": "No matching transactions found",
            "emptyTable": "No transaction data available"
        }
    });
});
</script>

</body>
</html>