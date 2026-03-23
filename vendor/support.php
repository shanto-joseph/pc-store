<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'vendor') {
    header('Location: ../login.php');
    exit();
}

$vendor_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT st.*, sr.message as reply, sr.created_at as reply_date
    FROM support_tickets st
    LEFT JOIN support_replies sr ON st.id = sr.ticket_id
    WHERE st.user_id = ?
    ORDER BY st.created_at DESC
");
$stmt->execute([$vendor_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT COUNT(*) as c FROM support_tickets WHERE user_id = ? AND status = 'pending'");
$stmt->execute([$vendor_id]);
$ticket_count = $stmt->fetch(PDO::FETCH_ASSOC)['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - Vendor Dashboard</title>
    <link rel="stylesheet" href="../css/vendor.css">
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/support.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
</head>
<body id="body">

<div class="l-navbar" id="navbar">
    <nav class="nav">
        <div>
            <div class="nav__toggle" id="nav-toggle">
                <i class='bx bx-chevron-right'></i>
            </div>
            <ul class="nav__list">
                <a href="dashboard.php" class="nav__link">
                    <i class='bx bx-grid-alt nav__icon'></i>
                    <span class="nav__text">Dashboard</span>
                </a>
                <a href="dashboard.php" class="nav__link">
                    <i class='bx bx-package nav__icon'></i>
                    <span class="nav__text">Products</span>
                </a>
                <a href="add_product.php" class="nav__link">
                    <i class='bx bx-plus-circle nav__icon'></i>
                    <span class="nav__text">Add Product</span>
                </a>
                <a href="support.php" class="nav__link active">
                    <i class='bx bx-message-rounded nav__icon'></i>
                    <span class="nav__text">Support
                        <?php if ($ticket_count > 0): ?>
                            <span class="badge"><?php echo $ticket_count; ?></span>
                        <?php endif; ?>
                    </span>
                </a>
                <a href="report.php" class="nav__link">
                    <i class='bx bx-bar-chart-alt-2 nav__icon'></i>
                    <span class="nav__text">Reports</span>
                </a>
            </ul>
        </div>
        <a href="../logout.php" class="nav__link">
            <i class='bx bx-log-out-circle nav__icon'></i>
            <span class="nav__text">Logout</span>
        </a>
    </nav>
</div>

<main>
    <h2>Support</h2>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <section id="create-ticket">
        <h3>Create New Ticket</h3>
        <form action="handlers/create_ticket.php" method="post" class="support-form">
            <div class="form-group">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required placeholder="Enter subject">
            </div>
            <div class="form-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required placeholder="Describe your issue"></textarea>
            </div>
            <button type="submit" class="btn-primary">Submit Ticket</button>
        </form>
    </section>

    <section id="ticket-history">
        <h3>Your Tickets</h3>
        <?php if (empty($tickets)): ?>
            <p class="empty-msg">No tickets yet.</p>
        <?php else: ?>
            <div class="tickets-list">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket <?php echo $ticket['status']; ?>">
                        <div class="ticket-header">
                            <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                            <span class="ticket-status"><?php echo ucfirst($ticket['status']); ?></span>
                        </div>
                        <p class="ticket-date"><?php echo date('M j, Y g:i a', strtotime($ticket['created_at'])); ?></p>
                        <div class="ticket-message"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></div>
                        <?php if ($ticket['reply']): ?>
                            <div class="ticket-reply">
                                <strong>Admin Reply</strong>
                                <p class="reply-date"><?php echo date('M j, Y g:i a', strtotime($ticket['reply_date'])); ?></p>
                                <div><?php echo nl2br(htmlspecialchars($ticket['reply'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script src="../js/vendor.js"></script>
</body>
</html>
