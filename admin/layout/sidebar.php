<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add/Edit Category - Cycle Selling Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        /* Custom monochrome styling */
        :root {
            --sidebar-width: 250px;
            --navbar-height: 60px;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background-color: #212529;
            color: white;
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar .nav-link {
            color: #adb5bd;
            padding: 12px 20px;
            border-radius: 0;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #495057;
            color: white;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
            width: 20px;
        }

        /* Main content area */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--navbar-height);
            min-height: 100vh;
        }

        /* Top navbar */
        .top-navbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--navbar-height);
            background-color: white;
            border-bottom: 1px solid #dee2e6;
            z-index: 999;
        }

        /* Form styling */
        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .form-control:focus {
            border-color: #6c757d;
            box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.25);
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }

            .top-navbar {
                left: 0;
            }
        }

        .category-level-0 {
            padding-left: 0;
        }

        .category-level-1 {
            padding-left: 20px;
        }

        .category-level-2 {
            padding-left: 40px;
        }

        .category-level-3 {
            padding-left: 60px;
        }

        .category-level-4 {
            padding-left: 80px;
        }

        .category-tree-icon {
            color: #6c757d;
            margin-right: 8px;
        }

        .subcategory-count {
            background-color: #e9ecef;
            color: #495057;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.75rem;
            margin-left: 8px;
        }

        .btn-action {
            margin-right: 5px;
        }

        .table-responsive {
            max-height: 600px;
            overflow-y: auto;
        }

        .success-message {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .category-image {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 5px;
            margin-right: 10px;
        }

        .bulk-actions {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .stats-card {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px; */
        }
    </style>
</head>


<body>
    <!-- Sidebar Navigation -->
    <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    // Example: "dashboard.php", "categories.php"
    ?>

    <nav class="sidebar bg-dark text-white vh-100">
        <div class="p-3">
            <h4 class="text-white mb-4">
                <i class="bi bi-bicycle"></i> Cycle Admin
            </h4>
        </div>
        <ul class="nav flex-column">
            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>

            <!-- Categories -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'categories.php' ? 'active' : '' ?>" href="categories.php">
                    <i class="bi bi-tags"></i> Categories
                </a>
            </li>

            <!-- Products -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'products.php' ? 'active' : '' ?>" href="products.php">
                    <i class="bi bi-box-seam"></i> Products
                </a>
            </li>

            <!-- Orders -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'orders.php' ? 'active' : '' ?>" href="orders.php">
                    <i class="bi bi-cart-check"></i> Orders
                </a>
            </li>

            <!-- Customers -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'customers.php' ? 'active' : '' ?>" href="customers.php">
                    <i class="bi bi-people"></i> Customers
                </a>
            </li>

            <!-- Landing Page -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'landing_page.php' ? 'active' : '' ?>" href="landing_page.php">
                    <i class="bi bi-layers"></i> Landing Page
                </a>
            </li>

            <!-- Hero Section -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'hero_section.php' ? 'active' : '' ?>" href="hero_section.php">
                    <i class="bi bi-image"></i> Hero Section
                </a>
            </li>

            <!-- Hero Banners -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'hero_banners.php' ? 'active' : '' ?>" href="hero_banners.php">
                    <i class="bi bi-images"></i> Hero Banners
                </a>
            </li>

            <!-- Brand Logos -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'brand_logos.php' ? 'active' : '' ?>" href="brand_logos.php">
                    <i class="bi bi-award"></i> Brand Logos
                </a>
            </li>
            <!-- Settings -->
            <li class="nav-item">
                <a class="nav-link <?= $currentPage == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
        </ul>
    </nav>