<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cycle Selling Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
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
        
        /* Stats cards */
        .stats-card {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            transition: box-shadow 0.3s;
        }
        
        .stats-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            background-color: #6c757d;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        /* Recent activity */
        .activity-item {
            border-left: 3px solid #6c757d;
            padding-left: 15px;
            margin-bottom: 15px;
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
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="p-3">
            <h4 class="text-white mb-4">
                <i class="bi bi-bicycle"></i> Cycle Admin
            </h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.html">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="categories.html">
                    <i class="bi bi-tags"></i> Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="products.html">
                    <i class="bi bi-box-seam"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#orders">
                    <i class="bi bi-cart-check"></i> Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#customers">
                    <i class="bi bi-people"></i> Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#settings">
                    <i class="bi bi-gear"></i> Settings
                </a>
            </li>
        </ul>
    </nav>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white top-navbar">
        <div class="container-fluid">
            <h5 class="mb-0 text-dark">Dashboard</h5>
            <div class="d-flex align-items-center">
                <span class="text-muted me-3">Welcome, Admin</span>
                <i class="bi bi-person-circle fs-4 text-secondary"></i>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid p-4">
            <!-- Stats Cards Row -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon me-3">
                                <i class="bi bi-tags"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Categories</h6>
                                <h3 class="mb-0 text-dark">12</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon me-3">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Products</h6>
                                <h3 class="mb-0 text-dark">248</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon me-3">
                                <i class="bi bi-cart-check"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Total Orders</h6>
                                <h3 class="mb-0 text-dark">1,847</h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stats-card h-100">
                        <div class="card-body d-flex align-items-center">
                            <div class="stats-icon me-3">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-1">Revenue</h6>
                                <h3 class="mb-0 text-dark">$45,230</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="row">
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">Recent Orders</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Product</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#ORD-001</td>
                                            <td>John Smith</td>
                                            <td>Mountain Bike Pro</td>
                                            <td>$899.99</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-002</td>
                                            <td>Sarah Johnson</td>
                                            <td>Road Bike Elite</td>
                                            <td>$1,299.99</td>
                                            <td><span class="badge bg-warning text-dark">Pending</span></td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-003</td>
                                            <td>Mike Davis</td>
                                            <td>City Cruiser</td>
                                            <td>$549.99</td>
                                            <td><span class="badge bg-info">Processing</span></td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-004</td>
                                            <td>Emily Wilson</td>
                                            <td>Electric Bike</td>
                                            <td>$1,899.99</td>
                                            <td><span class="badge bg-success">Completed</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-dark">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-item">
                                <small class="text-muted">2 hours ago</small>
                                <p class="mb-1">New product "BMX Pro" added</p>
                            </div>
                            <div class="activity-item">
                                <small class="text-muted">4 hours ago</small>
                                <p class="mb-1">Order #ORD-001 completed</p>
                            </div>
                            <div class="activity-item">
                                <small class="text-muted">6 hours ago</small>
                                <p class="mb-1">Category "Electric Bikes" updated</p>
                            </div>
                            <div class="activity-item">
                                <small class="text-muted">1 day ago</small>
                                <p class="mb-1">New customer registered</p>
                            </div>
                            <div class="activity-item">
                                <small class="text-muted">2 days ago</small>
                                <p class="mb-1">Inventory updated for 15 products</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
