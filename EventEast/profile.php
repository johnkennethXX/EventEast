<?php
require_once 'config.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get user data
$sql = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = $conn->real_escape_string($_POST['full_name']);
        $email = $conn->real_escape_string($_POST['email']);
        $phone = $conn->real_escape_string($_POST['phone']);
        
        // Check if email is already taken by another user
        $check_email = $conn->query("SELECT id FROM users WHERE email = '$email' AND id != $user_id");
        if ($check_email->num_rows > 0) {
            $error = 'Email is already taken by another user';
        } else {
            $sql = "UPDATE users SET full_name = '$full_name', email = '$email', phone = '$phone' WHERE id = $user_id";
            if ($conn->query($sql)) {
                $success = 'Profile updated successfully!';
                $_SESSION['full_name'] = $full_name;
                $user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
            } else {
                $error = 'Failed to update profile';
            }
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (!password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect';
        } elseif ($new_password !== $confirm_password) {
            $error = 'New passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = 'New password must be at least 6 characters';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = '$hashed_password' WHERE id = $user_id";
            if ($conn->query($sql)) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password';
            }
        }
    }
    
    // Handle profile picture upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_picture']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Delete old profile picture if exists and not default
            if ($user['profile_picture'] && $user['profile_picture'] !== 'default.png' && file_exists('uploads/' . $user['profile_picture'])) {
                unlink('uploads/' . $user['profile_picture']);
            }
            
            $image_name = 'profile_' . $user_id . '_' . uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $image_name;
            
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                $sql = "UPDATE users SET profile_picture = '$image_name' WHERE id = $user_id";
                if ($conn->query($sql)) {
                    $success = 'Profile picture updated successfully!';
                    $user['profile_picture'] = $image_name;
                }
            } else {
                $error = 'Failed to upload profile picture';
            }
        } else {
            $error = 'Invalid file type. Only JPG, PNG, and GIF allowed.';
        }
    }
}

// Get user statistics
$events_created = $conn->query("SELECT COUNT(*) as count FROM events WHERE user_id = $user_id")->fetch_assoc()['count'];
$events_registered = $conn->query("SELECT COUNT(*) as count FROM registrations WHERE user_id = $user_id AND status = 'registered'")->fetch_assoc()['count'];
$events_attended = $conn->query("SELECT COUNT(*) as count FROM registrations WHERE user_id = $user_id AND status = 'attended'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - EventEast</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="page-header">
            <h2>My Profile</h2>
            <p>Manage your account settings</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="profile-container">
            <!-- Profile Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-picture-section">
                        <img src="<?php echo $user['profile_picture'] && file_exists('uploads/' . $user['profile_picture']) ? 'uploads/' . $user['profile_picture'] : 'images/default-avatar.png'; ?>" 
                             alt="Profile Picture" 
                             class="profile-picture"
                             id="profilePicturePreview">
                        
                        <form method="POST" enctype="multipart/form-data" id="pictureForm">
                            <input type="file" name="profile_picture" id="profile_picture" accept="image/*" style="display: none;" onchange="document.getElementById('pictureForm').submit();">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('profile_picture').click();">
                                Change Picture
                            </button>
                        </form>
                    </div>
                    
                    <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                    <p class="user-role"><?php echo ucfirst($user['role']); ?></p>
                    <p class="user-email"><?php echo htmlspecialchars($user['email']); ?></p>
                    <p class="member-since">Member since <?php echo date('M Y', strtotime($user['created_at'])); ?></p>
                </div>
                
                <div class="profile-stats">
                    <h4>My Statistics</h4>
                    <div class="stat-item">
                        <span class="stat-label">Events Created</span>
                        <span class="stat-value"><?php echo $events_created; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Events Registered</span>
                        <span class="stat-value"><?php echo $events_registered; ?></span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Events Attended</span>
                        <span class="stat-value"><?php echo $events_attended; ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Profile Content -->
            <div class="profile-content">
                <!-- Personal Information -->
                <div class="profile-section">
                    <h3>Personal Information</h3>
                    <form method="POST" id="profileForm">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                            <small>Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="profile-section">
                    <h3>Change Password</h3>
                    <form method="POST" id="passwordForm">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                            <small>Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <button type="submit" name="change_password" class="btn btn-primary">Change Password</button>
                    </form>
                </div>
                
                <!-- Account Actions -->
                <div class="profile-section">
                    <h3>Account Actions</h3>
                    <div class="account-actions">
                        <a href="my-events.php" class="btn btn-secondary">View My Events</a>
                        <a href="my-registrations.php" class="btn btn-secondary">View My Registrations</a>
                        <a href="logout.php" class="btn btn-danger">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <style>
    .profile-container {
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 2rem;
        margin-top: 2rem;
    }
    
    .profile-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .profile-card {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: var(--shadow);
        text-align: center;
    }
    
    .profile-picture-section {
        margin-bottom: 1.5rem;
    }
    
    .profile-picture {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid var(--primary-color);
        margin-bottom: 1rem;
    }
    
    .profile-card h3 {
        margin-bottom: 0.5rem;
        color: var(--dark-color);
    }
    
    .user-role {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: var(--primary-color);
        color: white;
        border-radius: 20px;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }
    
    .user-email {
        color: var(--secondary-color);
        font-size: 0.9rem;
        margin-bottom: 0.25rem;
    }
    
    .member-since {
        color: var(--secondary-color);
        font-size: 0.85rem;
    }
    
    .profile-stats {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: var(--shadow);
    }
    
    .profile-stats h4 {
        margin-bottom: 1rem;
        color: var(--dark-color);
    }
    
    .stat-item {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .stat-item:last-child {
        border-bottom: none;
    }
    
    .stat-label {
        color: var(--secondary-color);
    }
    
    .stat-value {
        font-weight: 600;
        color: var(--primary-color);
        font-size: 1.1rem;
    }
    
    .profile-content {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }
    
    .profile-section {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        box-shadow: var(--shadow);
    }
    
    .profile-section h3 {
        margin-bottom: 1.5rem;
        color: var(--dark-color);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 0.5rem;
    }
    
    .account-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    @media (max-width: 968px) {
        .profile-container {
            grid-template-columns: 1fr;
        }
    }
    </style>
    
    <script src="js/validation.js"></script>
    <script>
    // Password match validation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New passwords do not match!');
            return false;
        }
    });
    
    // Profile picture preview
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePicturePreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>