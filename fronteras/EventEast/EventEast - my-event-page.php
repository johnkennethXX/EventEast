<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Get user's created events
$sql = "SELECT e.*, 
        (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = 'registered') as registered_count
        FROM events e 
        WHERE e.user_id = $user_id 
        ORDER BY e.event_date DESC, e.event_time DESC";
$events = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Events - EventEast</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h2>My Events</h2>
            <p>Events you have created</p>
        </div>
        
        <div class="page-actions">
            <a href="create-event.php" class="btn btn-primary">+ Create New Event</a>
        </div>
        
        <div class="events-grid">
            <?php if ($events->num_rows > 0): ?>
                <?php while ($event = $events->fetch_assoc()): ?>
                    <div class="event-card">
                        <div class="event-image">
                            <img src="<?php echo $event['image'] ? 'uploads/' . $event['image'] : 'images/default-event.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($event['title']); ?>">
                            <span class="event-category"><?php echo htmlspecialchars($event['category']); ?></span>
                        </div>
                        <div class="event-content">
                            <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="event-description"><?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?></p>
                            <div class="event-details">
                                <div class="detail-item">
                                    <span class="icon">📅</span>
                                    <span><?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="icon">⏰</span>
                                    <span><?php echo date('h:i A', strtotime($event['event_time'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="icon">📍</span>
                                    <span><?php echo htmlspecialchars($event['location']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <span class="icon">👥</span>
                                    <span><?php echo $event['registered_count']; ?> / <?php echo $event['max_attendees']; ?> registered</span>
                                </div>
                                <div class="detail-item">
                                    <span class="icon">📊</span>
                                    <span class="status-badge status-<?php echo $event['status']; ?>"><?php echo ucfirst($event['status']); ?></span>
                                </div>
                            </div>
                            <div class="event-footer">
                                <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                <a href="edit-event.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-events">
                    <p>You haven't created any events yet.</p>
                    <a href="create-event.php" class="btn btn-primary">Create Your First Event</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>