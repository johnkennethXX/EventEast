<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Check if event belongs to user or user is admin
$sql = "SELECT * FROM events WHERE id = $event_id AND (user_id = $user_id" . (isAdmin() ? " OR 1=1" : "") . ")";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    redirect('my-events.php');
}

$event = $result->fetch_assoc();

// Delete image if exists
if ($event['image'] && file_exists('uploads/' . $event['image'])) {
    unlink('uploads/' . $event['image']);
}

// Delete event (registrations will be deleted by CASCADE)
$sql = "DELETE FROM events WHERE id = $event_id";
if ($conn->query($sql)) {
    $_SESSION['message'] = 'Event deleted successfully';
    redirect('my-events.php');
} else {
    $_SESSION['error'] = 'Failed to delete event';
    redirect('event-details.php?id=' . $event_id);
}
?>