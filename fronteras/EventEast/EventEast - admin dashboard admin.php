<?php
require_once 'config.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('index.php');
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_events = $conn->query("SELECT COUNT(*) as count FROM events")->fetch_assoc()['count'];
$total_registrations = $conn->query("SELECT COUNT(*) as count FROM registrations WHERE status = 'registered'")->fetch_assoc()['count'];
$upcoming_events = $conn->query("SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE() AND status = 'upcoming'")->fetch_assoc()['count'];

// Get all users
$users = $conn->query("SELECT u.*, 
                       (SELECT COUNT(*) FROM events WHERE user_id = u.id) as event_count,
                       (SELECT COUNT(*) FROM registrations WHERE user_id = u.id) as registration_count
                       FROM users u 
                       ORDER BY u.created_at DESC");

// Get all events
$events = $conn->query("SELECT e.*, u.full_name as organizer,
                        (SELECT COUNT(*) FROM registrations WHERE event_id = e.id AND status = 'registered') as registered_count
                        FROM events e 
                        JOIN users u ON e.user_id = u.id 
                        ORDER BY e.created_at DESC 
                        LIMIT 20");

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $delete_id = intval($_GET['delete_user']);
    if ($delete_id != $_SESSION['user_id']) { // Can't delete self
        $conn->query("DELETE FROM users WHERE id = $delete_id");
        redirect('admin.php');
    }
}

// Handle event deletion
if (isset($_GET['delete_event'])) {
    $delete_id = intval($_GET['delete_event']);
    $event = $conn->query("SELECT image FROM events WHERE id = $delete_id")->fetch_assoc();
    if ($event && $event['image'] && file_exists('uploads/' . $event['image'])) {
        unlink('uploads/' . $event['image']);
    }
    $conn->query("DELETE FROM events WHERE id = $delete_id");
    redirect('admin.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EventEast</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h2>Admin Dashboard</h2>
            <p>Manage users and events</p>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3><?php echo $total_users; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-info">
                    <h3><?php echo $total_events; ?></h3>
                    <p>Total Events</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3><?php echo $total_registrations; ?></h3>
                    <p>Total Registrations</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-info">
                    <h3><?php echo $upcoming_events; ?></h3>
                    <p>Upcoming Events</p>
                </div>
            </div>
        </div>
        
        <!-- Users Management -->
        <div class="admin-section">
            <h3>User Management</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Events Created</th>
                            <th>Registrations</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo $user['role']; ?></span></td>
                                <td><?php echo $user['event_count']; ?></td>
                                <td><?php echo $user['registration_count']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?delete_user=<?php echo $user['id']; ?>" 
                                           class="btn btn-danger btn-sm" 
                                           onclick="return confirm('Delete this user and all their events?')">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Events Management -->
        <div class="admin-section">
            <h3>Recent Events</h3>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Organizer</th>
                            <th>Category</th>
                            <th>Date</th>
                            <th>Attendees</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($event = $events->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $event['id']; ?></td>
                                <td><?php echo htmlspecialchars($event['title']); ?></td>
                                <td><?php echo htmlspecialchars($event['organizer']); ?></td>
                                <td><?php echo htmlspecialchars($event['category']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td><?php echo $event['registered_count']; ?> / <?php echo $event['max_attendees']; ?></td>
                                <td><span class="status-badge status-<?php echo $event['status']; ?>"><?php echo $event['status']; ?></span></td>
                                <td>
                                    <a href="event-details.php?id=<?php echo $event['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                    <a href="?delete_event=<?php echo $event['id']; ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Delete this event?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <style>
    .admin-section {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: var(--shadow);
        margin-bottom: 2rem;
    }
    
    .admin-section h3 {
        margin-bottom: 1.5rem;
        color: var(--dark-color);
    }
    
    .table-responsive {
        overflow-x: auto;
    }
    
    .admin-table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .admin-table th,
    .admin-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    
    .admin-table th {
        background: var(--light-color);
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .role-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 12px;
        font-size: 0.875rem;
        font-weight: 600;
    }
    
    .role-admin {
        background: #fef3c7;
        color: #92400e;
    }
    
    .role-user {
        background: #e0e7ff;
        color: #3730a3;
    }
    </style>
</body>
</html>