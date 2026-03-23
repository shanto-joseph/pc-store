<?php
session_start();
include 'config.php';
include 'functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    
    $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, subject, message) VALUES (?, ?, ?)");
    $result = $stmt->execute([$user_id, $subject, $message]);
    
    if ($result) {
        $_SESSION['success_message'] = "Support ticket created successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to create support ticket.";
    }
}

// Get user's support tickets
$stmt = $conn->prepare("
    SELECT st.*, sr.message as reply, sr.created_at as reply_date 
    FROM support_tickets st 
    LEFT JOIN support_replies sr ON st.id = sr.ticket_id 
    WHERE st.user_id = ? 
    ORDER BY st.created_at DESC
");
$stmt->execute([$user_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - PC STORE</title>
    <link rel="stylesheet" href="css/nav.css">
    <link rel="stylesheet" href="css/support.css">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>

</head>
<body id="body">
    <div class="l-navbar" id="navbar">
        <nav class="nav">
            <div>
                <a href="index.php" class="nav__logo">
                    <img src="icon/logo.svg" alt="" class="nav__logo-icon">
                </a>
                <div class="nav__toggle" id="nav-toggle">
                    <i class='bx bx-chevron-right'></i>
                </div>
                <ul class="nav__list">
                    <a href="profile.php" class="nav__link" >
                        <i class='bx bx-user-circle nav__icon'></i>
                        <span class="nav__text">Profile</span>
                    </a>
                    <a href="profile.php?section=orders-section" class="nav__link">
    <i class='bx bx-package nav__icon'></i>
    <span class="nav__text">Orders</span>
</a>

                    <a href="index.php" class="nav__link">
                        <i class='bx bx-home nav__icon'></i>
                        <span class="nav__text">Home</span>
                    </a>
                    <a href="cart.php" class="nav__link">
                        <i class='bx bx-cart-alt nav__icon'></i>
                        <span class="nav__text">Cart</span>
                    </a>
                    <a href="support.php" class="nav__link active">
                        <i class='bx bx-message-rounded nav__icon'></i>
                        <span class="nav__text">Support</span>
                    </a>
                </ul>
            </div>
            <a href="logout.php" class="nav__link">           
                <i class='bx bx-log-out-circle nav__icon'></i>
                <span class="nav__text">Logout</span>
            </a>
        </nav>
    </div>

    <main>
        <section id="create-ticket">
            <h2>Create Support Ticket</h2>
            <?php if (isset($_SESSION['success_message'])): ?>
                <p class="success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error_message'])): ?>
                <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
            <?php endif; ?>
            
            <form action="support.php" method="post">
                <label for="subject">Subject</label>
                <input type="text" id="subject" name="subject" required>
                
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
                
                <button type="submit">Submit Ticket</button>
            </form>
        </section>

        <section id="ticket-history">
            <h2>Your Support Tickets</h2>
            <?php if (empty($tickets)): ?>
                <p>No support tickets found.</p>
            <?php else: ?>
                <div class="tickets-list">
                    <?php foreach ($tickets as $ticket): ?>
                        <div class="ticket">
                            <h3><?php echo htmlspecialchars($ticket['subject']); ?></h3>
                            <p class="ticket-date">Created: <?php echo date('F j, Y, g:i a', strtotime($ticket['created_at'])); ?></p>
                            <p class="ticket-message"><?php echo nl2br(htmlspecialchars($ticket['message'])); ?></p>
                            <p class="ticket-status">Status: <?php echo ucfirst($ticket['status']); ?></p>
                            
                            <?php if ($ticket['reply']): ?>
                                <div class="ticket-reply">
                                    <h4>Admin Reply</h4>
                                    <p class="reply-date">Replied: <?php echo date('F j, Y, g:i a', strtotime($ticket['reply_date'])); ?></p>
                                    <p class="reply-message"><?php echo nl2br(htmlspecialchars($ticket['reply'])); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script src="js/nav.js"></script>
</body>
</html>