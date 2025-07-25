<?php
// Get the current script filename
$current_page = basename($_SERVER['PHP_SELF']);
?>

<section id="sidebar">
    <a href="index.php" class="brand">
        <i class='bx bxs-dice-6'></i>
        <span class="text">Win80x</span>
    </a>
    <ul class="side-menu top">
        <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <a href="index.php">
                <i class='bx bxs-dashboard'></i>
                <span class="text">Dashboard</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'users.php') ? 'active' : ''; ?>">
            <a href="users.php">
                <i class='bx bx-user'></i>
                <span class="text">Users</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'questions.php') ? 'active' : ''; ?>">
            <a href="questions.php">
                <i class='bx bxs-doughnut-chart'></i>
                <span class="text">Create Questions</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'create_game.php') ? 'active' : ''; ?>">
            <a href="create_game.php">
                <i class='bx bxs-dice-3'></i>
                <span class="text">Create Game</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'schedule_game.php') ? 'active' : ''; ?>">
            <a href="schedule_game.php">
                <i class='bx bxs-time-five'></i>
                <span class="text">Schedule Game</span>
            </a>
        </li>
         <li class="<?php echo ($current_page == 'transaction_history.php') ? 'active' : ''; ?>">
            <a href="transaction_history.php">
               <i class="bx bx-rupee"></i>
                <span class="text">Transaction History</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'declare_result.php') ? 'active' : ''; ?>">
            <a href="declare_result.php">
                <i class='bx bxs-trophy'></i>
                <span class="text">Declare Result</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'results.php') ? 'active' : ''; ?>">
            <a href="results.php">
                <i class='bx bxs-trophy'></i>
                <span class="text">Results</span>
            </a>
        </li>
        <li class="<?php echo ($current_page == 'withdrawal_requests.php') ? 'active' : ''; ?>">
    <a href="withdrawal_requests.php">
       <i class='bx bx-timer'></i>
        <span class="text">Withdrawal Requests</span>
    </a>
</li>

    </ul>
   
</section>
