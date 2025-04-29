<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <a href="index.php" class="brand-link">
            <i class="fas fa-crown"></i>
            <div class="brand-name">Luxe & Co. Events</div>
        </a>
    </div>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Scrollable Nav Items -->
    <div class="sidebar-scroll">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="services.php">
                    <i class="fas fa-fw fa-concierge-bell"></i>
                    <span>Services</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="service-categories.php">
                    <i class="fas fa-fw fa-list"></i>
                    <span>Service Categories</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="wedding-plans.php">
                    <i class="fas fa-fw fa-calendar-alt"></i>
                    <span>Wedding Plans</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="bookings.php">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Bookings</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="users.php">
                    <i class="fas fa-fw fa-users"></i>
                    <span>Users</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="testimonials.php">
                    <i class="fas fa-fw fa-star"></i>
                    <span>Testimonials</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="payments.php">
                    <i class="fas fa-fw fa-money-bill-wave"></i>
                    <span>Payments & Income</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="wedding-planners.php">
                    <i class="fas fa-fw fa-user-tie"></i>
                    <span>Wedding Planners</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="blog.php">
                    <i class="fas fa-fw fa-blog"></i>
                    <span>Blog Posts</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="gallery.php">
                    <i class="fas fa-fw fa-images"></i>
                    <span>Gallery</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Profile and Logout -->
            <li class="nav-item">
                <a class="nav-link" href="profile.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-fw fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Sidebar Toggler -->
    <div class="text-center d-none d-md-inline mt-3">
        <button class="rounded-circle border-0" id="sidebarToggle">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>
</nav>

<style>
.sidebar {
    position: fixed;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 100;
    padding: 0;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    display: flex;
    flex-direction: column;
    width: 250px;
    transition: all 0.3s;
    background-color: #212529;
    color: #fff;
    height: 100vh;
    overflow-y: hidden;
}

.sidebar-brand {
    padding: 1rem;
    background-color: #212529;
    position: sticky;
    top: 0;
    z-index: 1;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-brand .brand-link {
    color: #fff;
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sidebar-brand .brand-link:hover {
    color: #fff;
}

.sidebar-scroll {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 1rem;
    scrollbar-width: thin;
    scrollbar-color: #666 #212529;
}

.sidebar-scroll::-webkit-scrollbar {
    width: 4px;
}

.sidebar-scroll::-webkit-scrollbar-track {
    background: #212529;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
    background: #666;
    border-radius: 4px;
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
    background: #888;
}

.sidebar.toggled {
    width: 6.5rem !important;
}

.sidebar.toggled .sidebar-brand {
    padding: 1rem 0.5rem;
}

.sidebar.toggled .brand-name {
    display: none;
}

.sidebar.toggled .nav-item span {
    display: none;
}

.sidebar.toggled .sidebar-scroll {
    overflow-x: hidden;
}

.sidebar .nav-item {
    position: relative;
}

.sidebar .nav-link {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.3s;
    text-decoration: none;
}

.sidebar .nav-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link.active {
    color: #fff;
    background: rgba(255, 255, 255, 0.1);
}

.sidebar .nav-link i {
    margin-right: 0.5rem;
    width: 1.25rem;
    text-align: center;
}

.sidebar-divider {
    margin: 0.5rem 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

#sidebarToggle {
    width: 2rem;
    height: 2rem;
    background: rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.3s;
    margin-bottom: 1rem;
}

#sidebarToggle:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #fff;
}

@media (max-width: 768px) {
    .sidebar {
        margin-left: -250px;
    }
    
    .sidebar.toggled {
        margin-left: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const currentPath = window.location.pathname;
    
    // Set active state for current page
    document.querySelectorAll('.nav-link').forEach(link => {
        if (currentPath.endsWith(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('toggled');
        });
    }
});
</script> 