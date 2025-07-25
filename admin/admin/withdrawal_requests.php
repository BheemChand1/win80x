<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

include '../connection.php';

$error = "";
$requests = [];

// Date filter (optional)
$from_date = $_GET['from_date'] ?? date('Y-m-01');
$to_date = $_GET['to_date'] ?? date('Y-m-t');

// Status filter - default to 'pending'
$status = $_GET['status'] ?? 'pending';

try {
    $where = "WHERE 1";
    $params = [];
    $types = "";

    if (!empty($from_date) && !empty($to_date)) {
        $where .= " AND DATE(wr.created_at) BETWEEN ? AND ?";
        $params[] = $from_date;
        $params[] = $to_date;
        $types .= "ss";
    }

    // Add status filter to the query
    if ($status !== 'all') {
        $where .= " AND wr.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    $sql = "
    SELECT 
        wr.id,
        wr.amount,
        wr.status,
        wr.transaction_id,
        wr.created_at,
        wr.updated_at,
        u.id AS user_id,
        u.name AS user_name,
        u.email AS user_email,
        u.account_no,
        u.ifsc_code,
        u.account_holder_name
    FROM withdrawal_requests wr
    JOIN users u ON wr.user_id = u.id
    $where
    ORDER BY wr.created_at DESC
    ";

    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $requests[] = $row;
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Function to generate URL with updated parameters
function getFilterUrl($newParams) {
    $params = $_GET;
    foreach ($newParams as $key => $value) {
        $params[$key] = $value;
    }
    return '?' . http_build_query($params);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Withdrawal Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables & Buttons CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- DataTables Buttons JS -->
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <!-- JSZip for Excel export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="d-flex justify-content-between mb-4">
        <h2 class="fw-bold">🏦 Withdrawal Requests</h2>
        <a href="index.php" class="btn btn-primary">🏠 Home</a>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <input type="date" class="form-control" name="from_date" value="<?= $from_date ?>">
        </div>
        <div class="col-md-3">
            <input type="date" class="form-control" name="to_date" value="<?= $to_date ?>">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>All Statuses</option>
                <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="success" <?= $status === 'success' ? 'selected' : '' ?>>Completed</option>
                <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="col-md-3 d-flex">
            <button class="btn btn-primary me-2">🔍 Filter</button>
            <a href="withdrawal_requests.php" class="btn btn-outline-secondary">♻️ Reset</a>
        </div>
    </form>

    <div class="mb-3">
        <div class="btn-group">
            <a href="<?= getFilterUrl(['status' => 'all']) ?>" class="btn btn-outline-secondary <?= $status === 'all' ? 'active' : '' ?>">
                All
            </a>
            <a href="<?= getFilterUrl(['status' => 'pending']) ?>" class="btn btn-outline-warning <?= $status === 'pending' ? 'active' : '' ?>">
                Pending
            </a>
            <a href="<?= getFilterUrl(['status' => 'success']) ?>" class="btn btn-outline-success <?= $status === 'success' ? 'active' : '' ?>">
                Completed
            </a>
            <a href="<?= getFilterUrl(['status' => 'rejected']) ?>" class="btn btn-outline-danger <?= $status === 'rejected' ? 'active' : '' ?>">
                Rejected
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered align-middle" id="withdrawalTable">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Account No</th>
                    <th>IFSC</th>
                    <th>Holder Name</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Requested On</th>
                    <th>⏱ Time Left</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($requests)): ?>
                    <?php foreach ($requests as $i => $req): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= htmlspecialchars($req['user_name']) ?></td>
                            <td><?= htmlspecialchars($req['user_email']) ?></td>
                            <td><?= !empty($req['account_no']) ? htmlspecialchars($req['account_no']) : '<span class="text-muted">Not Available</span>' ?></td>
                            <td><?= !empty($req['ifsc_code']) ? htmlspecialchars($req['ifsc_code']) : '<span class="text-muted">Not Available</span>' ?></td>
                            <td><?= !empty($req['account_holder_name']) ? htmlspecialchars($req['account_holder_name']) : '<span class="text-muted">Not Available</span>' ?></td>
                            <td>₹<?= number_format($req['amount'], 2) ?></td>
                            <td>
                                <span class="badge bg-<?= $req['status'] === 'pending' ? 'warning' : ($req['status'] === 'success' ? 'success' : 'danger') ?>">
                                    <?= $req['status'] === 'success' ? 'Completed' : ucfirst($req['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    $createdTime = strtotime($req['created_at']);
                                    echo date('d-m-Y h:i A', $createdTime);
                                ?>
                            </td>
                            <td>
                                <?php
                                    $deadlineTime = $createdTime + (24 * 60 * 60);
                                    $now = time();

                                    if ($now < $deadlineTime) {
                                        $remaining = $deadlineTime - $now;
                                        $hours = floor($remaining / 3600);
                                        $minutes = floor(($remaining % 3600) / 60);
                                        $timerDisplay = "{$hours}h {$minutes}m left";
                                        $timerClass = "text-success";
                                    } else {
                                        $timerDisplay = "More Than 24 hours";
                                        $timerClass = "text-danger";
                                    }
                                ?>
                                <small class="<?= $timerClass ?> fw-semibold"><?= $timerDisplay ?></small>
                            </td>
                            <td>
                                <?php if ($req['status'] === 'pending'): ?>
                                    <form method="POST" action="update_withdrawal_status.php" class="d-flex gap-1">
                                        <input type="hidden" name="request_id" value="<?= $req['id'] ?>">
                                        <input type="hidden" name="user_id" value="<?= $req['user_id'] ?>">
                                        <input type="hidden" name="amount" value="<?= $req['amount'] ?>">
                                        <button name="action" value="approve" class="btn btn-success btn-sm">✅ Done</button>
                                        <button name="action" value="reject" class="btn btn-danger btn-sm">❌ Reject</button>
                                    </form>
                                <?php else: ?>
                                    <em class="text-muted">No action</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="11" class="text-center text-muted">No withdrawal requests found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    // Init DataTable with Buttons but without filtering functionality
    var table = $('#withdrawalTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: 'Export to Excel',
                className: 'btn btn-success mb-2'
            },
            {
                extend: 'print',
                text: 'Print Table',
                className: 'btn btn-primary mb-2'
            }
        ],
        // Disable the default search functionality
        searching: false
    });
});
</script>
</body>
</html>