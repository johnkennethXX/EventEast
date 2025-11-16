<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get event details
$sql = "SELECT e.*, u.full_name as organizer, u.email as organizer_email, u.phone as organizer_phone,
        (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = 'registered') as registered_count
        FROM events e 
        JOIN users u ON e.user_id = u.id 
        WHERE e.id = $event_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    redirect('index.php');
}

$event = $result->fetch_assoc();

// Check if user is registered
$user_id = $_SESSION['user_id'];
$check_registration = $conn->query("SELECT * FROM registrations WHERE event_id = $event_id AND user_id = $user_id");
$is_registered = $check_registration->num_rows > 0;
$registration = $is_registered ? $check_registration->fetch_assoc() : null;

// Check if user is organizer
$is_organizer = $event['user_id'] == $user_id;

$message = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        if ($event['registered_count'] < $event['max_attendees']) {
            $sql = "INSERT INTO registrations (event_id, user_id) VALUES ($event_id, $user_id)";
            if ($conn->query($sql)) {
                $message = 'Successfully registered for the event!';
                $is_registered = true;
                $event['registered_count']++;
            }
        } else {
            $message = 'Event is full!';
        }
    } elseif (isset($_POST['cancel_registration'])) {
        $sql = "DELETE FROM registrations WHERE event_id = $event_id AND user_id = $user_id";
        if ($conn->query($sql)) {
            $message = 'Registration cancelled successfully!';
            $is_registered = false;
            $event['registered_count']--;
        }
    }
}

// Get registered attendees (for organizer)
if ($is_organizer) {
    $attendees = $conn->query("SELECT u.full_name, u.email, u.phone, r.registration_date, r.status 
                               FROM registrations r 
                               JOIN users u ON r.user_id = u.id 
                               WHERE r.event_id = $event_id 
                               ORDER BY r.registration_date DESC");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - EventEast</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <div class="event-details-container">
            <div class="event-main">
                <div class="event-image-large">
                    <img src="<?php echo $event['image'] ? 'uploads/' . $event['image'] : 'images/default-event.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($event['title']); ?>">
                </div>
                
                <div class="event-header-section">
                    <div class="event-title-row">
                        <h1><?php echo htmlspecialchars($event['title']); ?></h1>
                        <span class="event-status status-<?php echo $event['status']; ?>">
                            <?php echo ucfirst($event['status']); ?>
                        </span>
                    </div>
                    <p class="event-category-large"><?php echo htmlspecialchars($event['category']); ?></p>
                </div>
                
                <div class="event-description-section">
                    <h2>About This Event</h2>
                    <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                </div>
                
                <?php if ($is_organizer && isset($attendees)): ?>
                    <div class="attendees-section">
                        <h2>Registered Attendees (<?php echo $attendees->num_rows; ?>)</h2>
                        <?php if ($attendees->num_rows > 0): ?>
                            <div class="attendees-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Registration Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($attendee = $attendees->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($attendee['full_name']); ?></td>
                                                <td><?php echo htmlspecialchars($attendee['email']); ?></td>
                                                <td><?php echo htmlspecialchars($attendee['phone']); ?></td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($attendee['registration_date'])); ?></td>
                                                <td><span class="status-badge status-<?php echo $attendee['status']; ?>"><?php echo $attendee['status']; ?></span></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No attendees registered yet.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="event-sidebar">
                <div class="event-info-card">
                    <h3>Event Information</h3>
                    
                    <div class="info-item">
                        <span class="info-icon">📅</span>
                        <div>
                            <strong>Date</strong>
                            <p><?php echo date('l, F d, Y', strtotime($event['event_date'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-icon">⏰</span>
                        <div>
                            <strong>Time</strong>
                            <p><?php echo date('h:i A', strtotime($event['event_time'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-icon">📍</span>
                        <div>
                            <strong>Location</strong>
                            <p><?php echo htmlspecialchars($event['location']); ?></p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-icon">👥</span>
                        <div>
                            <strong>Attendees</strong>
                            <p><?php echo $event['registered_count']; ?> / <?php echo $event['max_attendees']; ?> registered</p>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo ($event['registered_count'] / $event['max_attendees']) * 100; ?>%"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-icon">👤</span>
                        <div>
                            <strong>Organizer</strong>
                            <p><?php echo htmlspecialchars($event['organizer']); ?></p>
                            <p class="organizer-contact"><?php echo htmlspecialchars($event['organizer_email']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="event-actions-card">
                    <?php if ($is_organizer): ?>
                        <a href="edit-event.php?id=<?php echo $event_id; ?>" class="btn btn-primary btn-block">Edit Event</a>
                        <button onclick="deleteEvent(<?php echo $event_id; ?>)" class="btn btn-danger btn-block">Delete Event</button>
                    <?php else: ?>
                        <?php if ($is_registered): ?>
                            <div class="registered-badge">✓ You are registered</div>
                            <form method="POST">
                                <button type="submit" name="cancel_registration" class="btn btn-danger btn-block" 
                                        onclick="return confirm('Are you sure you want to cancel your registration?')">
                                    Cancel Registration
                                </button>
                            </form>
                        <?php else: ?>
                            <?php if ($event['registered_count'] < $event['max_attendees']): ?>
                                <form method="POST">
                                    <button type="submit" name="register" class="btn btn-primary btn-block">Register for Event</button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary btn-block" disabled>Event Full</button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <a href="index.php" class="btn btn-secondary btn-block">Back to Events</a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function deleteEvent(id) {
        if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
            window.location.href = 'delete-event.php?id=' + id;
        }
    }
    </script>
</body>
</html>