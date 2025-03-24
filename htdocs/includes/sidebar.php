<div class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse" id="sidebarMenu">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'students.php') ? 'active' : ''; ?>" href="students.php">
                    <i class="fas fa-user-graduate"></i> Students
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'teachers.php') ? 'active' : ''; ?>" href="teachers.php">
                    <i class="fas fa-chalkboard-teacher"></i> Teachers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'classes.php') ? 'active' : ''; ?>" href="classes.php">
                    <i class="fas fa-chalkboard"></i> Classes
                </a>
            </li>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1 text-muted">
                    <span>Administration</span>
                </h6>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" href="users.php">
                    <i class="fas fa-users-cog"></i> Users
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" href="settings.php">
                    <i class="fas fa-cogs"></i> Settings
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item mt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mb-1 text-muted">
                    <span>Reports</span>
                </h6>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>" href="reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </li>
        </ul>
    </div>
</div>