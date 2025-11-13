<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

// Get filter parameters
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';
$date_filter = isset($_GET['date_filter']) ? $conn->real_escape_string($_GET['date_filter']) : '';

// Build query
$sql = "SELECT e.*, u.full_name as organizer, 
        (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = 'registered') as registered_count
        FROM events e 
        JOIN users u ON e.user_id = u.id 
        WHERE 1=1";

if ($search) {
    $sql .= " AND (e.title LIKE '%$search%' OR e.description LIKE '%$search%' OR e.location LIKE '%$search%')";
}

if ($category) {
    $sql .= " AND e.category = '$category'";
}

if ($date_filter) {
    $today = date('Y-m-d');
    if ($date_filter === 'today') {
        $sql .= " AND e.event_date = '$today'";
    } elseif ($date_filter === 'week') {
        $week_end = date('Y-m-d', strtotime('+7 days'));
        $sql .= " AND e.event_date BETWEEN '$today' AND '$week_end'";
    } elseif ($date_filter === 'month') {
        $month_end = date('Y-m-d', strtotime('+30 days'));
        $sql .= " AND e.event_date BETWEEN '$today' AND '$month_end'";
    }
}

$sql .= " ORDER BY e.event_date ASC, e.event_time ASC";
$events = $conn->query($sql);

// Get statistics
$total_events = $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'];
$my_events = $conn->query("SELECT COUNT(*) as count FROM events WHERE user_id = {$_SESSION['user_id']}")->fetch_assoc()['count'];
$my_registrations = $conn->query("SELECT COUNT(*) as count FROM registrations WHERE user_id = {$_SESSION['user_id']} AND status = 'registered'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EventEast</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <!-- Statistics Dashboard -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3><?php echo $total_events; ?></h3>
                    <p>Total Events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-info">
                    <h3><?php echo $my_events; ?></h3>
                    <p>My Events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $my_registrations; ?></h3>
                    <p>Registered Events</p>
                </div>
            </div>
        </div>
        
        <!-- Search and Filter -->
        <div class="search-filter-section">
            <form method="GET" class="search-form" id="searchForm">
                <div class="search-box">
                    <input type="text" name="search" placeholder="Search events..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
                
                <div class="filters">
                    <select name="category" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        <option value="Conference" <?php echo $category === 'Conference' ? 'selected' : ''; ?>>Conference</option>
                        <option value="Workshop" <?php echo $category === 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                        <option value="Seminar" <?php echo $category === 'Seminar' ? 'selected' : ''; ?>>Seminar</option>
                        <option value="Meetup" <?php echo $category === 'Meetup' ? 'selected' : ''; ?>>Meetup</option>
                        <option value="Concert" <?php echo $category === 'Concert' ? 'selected' : ''; ?>>Concert</option>
                        <option value="Sports" <?php echo $category === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                        <option value="Other" <?php echo $category === 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                    
                    <select name="date_filter" onchange="this.form.submit()">
                        <option value="">All Dates</option>
                        <option value="today" <?php echo $date_filter === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $date_filter === 'week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="month" <?php echo $date_filter === 'month' ? 'selected' : ''; ?>>This Month</option>
                    </select>
                    
                    <?php if ($search || $category || $date_filter): ?>
                        <a href="index.php" class="btn btn-secondary">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Events Grid -->
        <div class="events-grid">
            <?php if ($events->num_rows > 0): ?>
                <?php while ($event = $events->fetch_assoc()): ?>
                    <div class="event-card" data-event-id="<?php echo $event['id']; ?>">
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
                                    <span><?php echo $event['registered_count']; ?> / <?php echo $event['max_attendees']; ?> attendees</span>
                                </div>
                            </div>
                            <div class="event-footer">
                                <span class="organizer">By <?php echo htmlspecialchars($event['organizer']); ?></span>
                                <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-events">
                    <p>No events found matching your criteria.</p>
                    <a href="create-event.php" class="btn btn-primary">Create an Event</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="js/main.js"></script>
</body>
</html>