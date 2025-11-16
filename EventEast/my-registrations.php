<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user's registrations
$sql = "SELECT e.*, r.registration_date, r.status as registration_status, u.full_name as organizer
        FROM registrations r
        JOIN events e ON r.event_id = e.id
        JOIN users u ON e.user_id = u.id
        WHERE r.user_id = $user_id
        ORDER BY e.event_date ASC, e.event_time ASC";
$registrations = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations - EventEast</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h2>My Registrations</h2>
            <p>Events you have registered for</p>
        </div>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>>
        
        <div class="events-grid">
            <?php if ($registrations->num_rows > 0): ?>
                <?php while ($reg = $registrations->fetch_assoc()): ?>
                    <div class="event-card">
                        <div class="event-image">
                            <img src="<?php echo $reg['image'] ? 'uploads/' . $reg['image'] : 'images/default-event.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($reg['title']); ?>">
                            <span class="event-category"><?php echo htmlspecialchars($reg['category']); ?></span>
                        </div>
                        <div class="event-content">
                            <h3><?php echo htmlspecialchars($reg['title']); ?></h3>
                            <p class="event-description"><?php echo htmlspecialchars(substr($reg['description'], 0, 100)) . '...'; ?></p>
                            <div class="event-details">
                                <div class="detail-item">
                                    <span class="icon">📅</span>
                                    <span><?php echo date('M d, Y', strtotime($reg['event_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="icon">⏰</span>
                                    <span><?php echo date('h:i A', strtotime($reg['event_time'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="icon">📍</span>
                                    <span><?php echo htmlspecialchars($reg['location']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="icon">👤</span>
                                    <span>By <?php echo htmlspecialchars($reg['organizer']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="icon">✅</span>
                                    <span class="status-badge status-<?php echo $reg['registration_status']; ?>">
                                        <?php echo ucfirst($reg['registration_status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="event-footer" style="display: block;">
                                <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 0.5rem;">
                                    Registered: <?php echo date('M d, Y h:i A', strtotime($reg['registration_date'])); ?>
                                </p>
                                <a href="event-details.php?id=<?php echo $reg['id']; ?>" class="btn btn-primary btn-sm btn-block">View Event</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-events">
                    <p>You haven't registered for any events yet.</p>
                    <a href="index.php" class="btn btn-primary">Browse Events</a>
                </div>
            <?php endif; ?>
        </div