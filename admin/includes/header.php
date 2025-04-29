<?php
require_once '../includes/config.php';
requireAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxe & Co. Events - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <style>
        /* Critical sidebar styles */
        body {
            overflow-x: hidden;
        }
        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            min-height: 100vh;
        }
        .sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background: #212529 !important;
            box-shadow: 0 .15rem 1.75rem 0 rgba(58,59,69,.15);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            transition: margin-left 0.3s ease;
        }
        .sidebar.toggled {
            margin-left: -250px;
        }
        .sidebar .brand-link {
            color: #fff !important;
            text-decoration: none;
            padding: 1.5rem 1rem;
            display: block;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 300;
            letter-spacing: 0.05em;
        }
        .sidebar .brand-link:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar .brand-name {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.6) !important;
            padding: 1rem 1.5rem;
            font-size: 0.85rem;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover {
            color: #fff !important;
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar .nav-link i {
            margin-right: 0.5rem;
            color: rgba(255, 255, 255, 0.5);
        }
        .sidebar .nav-link:hover i {
            color: #fff;
        }
        .sidebar hr.sidebar-divider {
            margin: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        #content {
            width: calc(100% - 250px);
            margin-left: 250px;
            min-height: 100vh;
            transition: margin-left 0.3s ease, width 0.3s ease;
        }
        #content.full-width {
            width: 100%;
            margin-left: 0;
        }
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.toggled {
                margin-left: 0;
            }
            #content {
                width: 100%;
                margin-left: 0;
                transition: margin-left 0.3s ease;
            }
            .navbar {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600&display=swap" rel="stylesheet">
    <link href="../admin/assets/css/admin-style.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <?php require_once 'sidebar.php'; ?>

        <!-- Page Content -->
        <div id="content">
            <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-dark">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="ms-auto">
                        <div class="dropdown">
                            <button class="btn btn-dark dropdown-toggle" type="button" id="dropdownMenuButton" 
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                Welcome, <?php echo htmlspecialchars($_SESSION['admin']['username'] ?? 'Admin'); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content Container -->
            <div class="container-fluid p-4"> 