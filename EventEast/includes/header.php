<header class="main-header">
    <div class="header-container">
        <div class="logo">
            <a href="index.php">
                <h1>EventEast</h1>
            </a>
        </div>
        
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Dashboard</a></li>
                <li><a href="my-events.php">My Events</a></li>
                <li><a href="my-registrations.php">My Registrations</a></li>
                <li><a href="create-event.php" class="btn-create">+ Create Event</a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="admin.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        
        <div class="user-menu">
            <div class="user-info" onclick="toggleUserDropdown()">
                <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <span class="dropdown-arrow">▼</span>
            </div>
            <div class="user-dropdown" id="userDropdown">
                <a href="profile.php">My Profile</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
        
        <div class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</header>

<script>
function toggleUserDropdown() {
    document.getElementById('userDropdown').classList.toggle('show');
}

function toggleMobileMenu() {
    document.querySelector('.main-nav').classList.toggle('show');
}

// Close dropdown when clicking outside
window.onclick = function(event) {
    if (!event.target.matches('.user-info') && !event.target.matches('.user-info *')) {
        const dropdown = document.getElementById('userDropdown');
        if (dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}
</script>