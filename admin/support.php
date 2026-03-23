<?php
session_start();
include '../config.php';
include '../functions.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $reply = $_POST['reply'];
    
    $stmt = $conn->prepare("INSERT INTO support_replies (ticket_id, admin_id, message) VALUES (?, ?, ?)");
    $result = $stmt->execute([$ticket_id, $_SESSION['user_id'], $reply]);
    
    if ($result) {
        $stmt = $conn->prepare("UPDATE support_tickets SET status = 'resolved' WHERE id = ?");
        $stmt->execute([$ticket_id]);
        $_SESSION['success_message'] = "Reply sent successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to send reply.";
    }
    header('Location: support.php');
    exit();
}

$stmt = $conn->prepare("
    SELECT st.*, u.name as user_name, u.email as user_email, u.user_type,
           sr.message as reply, sr.created_at as reply_date 
    FROM support_tickets st 
    JOIN users u ON st.user_id = u.id 
    LEFT JOIN support_replies sr ON st.id = sr.ticket_id 
    ORDER BY st.status ASC, st.created_at DESC
");
$stmt->execute();
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt2 = $conn->prepare("SELECT COUNT(*) as c FROM support_tickets WHERE status = 'pending'");
$stmt2->execute();
$pending_tickets = $stmt2->fetch(PDO::FETCH_ASSOC)['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Management - Admin</title>
    <link rel="stylesheet" href="../css/admindash.css">
    <link rel="stylesheet" href="../css/adminsupport.css">
    <link rel="stylesheet" href="../css/nav.css">
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
                    <i class='bx bx-home nav__icon'></i>
                    <span class="nav__text">Dashboard</span>
                </a>
                <a href="manage_categories.php" class="nav__link">
                    <i class='bx bx-grid-alt nav__icon'></i>
                    <span class="nav__text">Categories</span>
                </a>
                <a href="order_manage.php" class="nav__link">
                    <i class='bx bxs-cart-alt nav__icon'></i>
                    <span class="nav__text">Orders</span>
                </a>
                <a href="support.php" class="nav__link active">
                    <i class='bx bx-message-rounded nav__icon'></i>
                    <span class="nav__text">Support
                        <?php if ($pending_tickets > 0): ?>
                            <span class="badge"><?php echo $pending_tickets; ?></span>
                        <?php endif; ?>
                    </span>
                </a>
                <a href="report.php" class="nav__link">
                    <i class='bx bx-bar-chart-alt-2 nav__icon'></i>
                    <span class="nav__text">Reports</span>
                </a>
                <a href="profile.php" class="nav__link">
                    <i class='bx bx-user-circle nav__icon'></i>
                    <span class="nav__text">Profile</span>
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
    <section id="support-tickets">
        <h2>Support Tickets</h2>

        <?php if (isset($_SESSION['success_message'])): ?>
            <p class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>

        <?php if (empty($tickets)): ?>
            <p>No support tickets found.</p>
        <?php else: ?>
            <div class="tickets-list">
                <?php foreach ($tickets as $ticket): ?>
                    <div class="ticket <?php echo htmlspecialchars($ticket['status']); ?>">
                        <div class="ticket-header">
                            <h3><?php echo htmlspecialchars($ticket['subject']); ?></h3>
                            <span class="ticket-status"><?php echo ucfirst($ticket['status']); ?></span>
                        </div>
                        <div class="ticket-info">
                            <p>From: <?php echo htmlspecialchars($ticket['user_name']); ?> (<?php echo ucfirst($ticket['user_type']); ?>)</p>
                            <p>Email: <?php echo htmlspecialchars($ticket['user_email']); ?></p>
                            <p>Created: <?php echo date('F j, Y, g:i a', strtotime($ticket['created_at'])); ?></p>
                        </div>
                        <div class="ticket-message">
                            <p><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></p>
                        </div>

                        <?php if ($ticket['reply']): ?>
                            <div class="ticket-reply">
                                <h4>Your Reply</h4>
                                <p class="reply-date">Sent: <?php echo date('F j, Y, g:i a', strtotime($ticket['reply_date'])); ?></p>
                                <p><?php echo nl2br(htmlspecialchars($ticket['reply'])); ?></p>
                            </div>
                        <?php else: ?>
                            <form action="support.php" method="post" class="reply-form">
                                <input type="hidden" name="ticket_id" value="<?php echo $ticket['id']; ?>">
                                <label for="reply-<?php echo $ticket['id']; ?>">Reply:</label>
                                <textarea id="reply-<?php echo $ticket['id']; ?>" name="reply" required></textarea>
                                <button type="submit">Send Reply</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script src="../js/admindash.js"></script>
</body>
</html>
