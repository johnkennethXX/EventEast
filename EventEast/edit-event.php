<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$event_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_id = $_SESSION['user_id'];

// Get event
$sql = "SELECT * FROM events WHERE id = $event_id AND user_id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    redirect('my-events.php');
}

$event = $result->fetch_assoc();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $category = $conn->real_escape_string($_POST['category']);
    $event_date = $conn->real_escape_string($_POST['event_date']);
    $event_time = $conn->real_escape_string($_POST['event_time']);
    $location = $conn->real_escape_string($_POST['location']);
    $max_attendees = intval($_POST['max_attendees']);
    $status = $conn->real_escape_string($_POST['status']);
    
    $image_name = $event['image'];
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Delete old image if exists
            if ($event['image'] && file_exists('uploads/' . $event['image'])) {
                unlink('uploads/' . $event['image']);
            }
            
            $image_name = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $image_name;
            
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $error = 'Failed to upload image';
            }
        }
    }
    
    if (!$error) {
        $sql = "UPDATE events SET 
                title = '$title',
                description = '$description',
                category = '$category',
                event_date = '$event_date',
                event_time = '$event_time',
                location = '$location',
                max_attendees = $max_attendees,
                status = '$status',
                image = '$image_name'
                WHERE id = $event_id AND user_id = $user_id";
        
        if ($conn->query($sql)) {
            $success = 'Event updated successfully!';
            $event = $conn->query("SELECT * FROM events WHERE id = $event_id")->fetch_assoc();
        } else {
            $error = 'Failed to update event.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - EventEast</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h2>Edit Event</h2>
            <p>Update your event details</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Event Title *</label>
                        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($event['title']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category *</label>
                        <select id="category" name="category" required>
                            <option value="Conference" <?php echo $event['category'] === 'Conference' ? 'selected' : ''; ?>>Conference</option>
                            <option value="Workshop" <?php echo $event['category'] === 'Workshop' ? 'selected' : ''; ?>>Workshop</option>
                            <option value="Seminar" <?php echo $event['category'] === 'Seminar' ? 'selected' : ''; ?>>Seminar</option>
                            <option value="Meetup" <?php echo $event['category'] === 'Meetup' ? 'selected' : ''; ?>>Meetup</option>
                            <option value="Concert" <?php echo $event['category'] === 'Concert' ? 'selected' : ''; ?>>Concert</option>
                            <option value="Sports" <?php echo $event['category'] === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                            <option value="Other" <?php echo $event['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_date">Event Date *</label>
                        <input type="date" id="event_date" name="event_date" required value="<?php echo $event['event_date']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="event_time">Event Time *</label>
                        <input type="time" id="event_time" name="event_time" required value="<?php echo $event['event_time']; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="location">Location *</label>
                    <input type="text" id="location" name="location" required value="<?php echo htmlspecialchars($event['location']); ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="max_attendees">Maximum Attendees *</label>
                        <input type="number" id="max_attendees" name="max_attendees" required min="1" value="<?php echo $event['max_attendees']; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Event Status *</label>
                        <select id="status" name="status" required>
                            <option value="upcoming" <?php echo $event['status'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="ongoing" <?php echo $event['status'] === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo $event['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $event['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="image">Event Image</label>
                    <?php if ($event['image']): ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="uploads/<?php echo $event['image']; ?>" alt="Current image" style="max-width: 200px; border-radius: 6px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small>Leave empty to keep current image</small>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Event</button>
                    <a href="event-details.php?id=<?php echo $event_id; ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>