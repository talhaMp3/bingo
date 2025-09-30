<?php
// invoice.php
require_once '../include/connection.php';

if (!isset($_GET['id'])) {
    die('Order ID is required');
}

$order_id = (int)$_GET['id'];

// Fetch order details
$order_query = "
    SELECT o.*, c.full_name, c.email, c.phone 
    FROM orders o 
    LEFT JOIN customers c ON o.user_id = c.id 
    WHERE o.id = $order_id
";
$order_result = mysqli_query($conn, $order_query);
$order = mysqli_fetch_assoc($order_result);

if (!$order) {
    die('Order not found');
}

$shipping_address = json_decode($order['shipping_address'], true);

// Fetch order items
$items_query = "
    SELECT oi.*, p.name as product_name, p.image as product_image 
    FROM order_items oi 
    LEFT JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = $order_id
";
$items_result = mysqli_query($conn, $items_query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $order_id; ?> - Bingo Cycle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }

        .invoice-header {
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .invoice-table th {
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container mt-4 mb-4">
        <!-- Header -->
        <div class="row invoice-header">
            <div class="col-md-6">
                <h1 class="text-primary">BINGO CYCLE</h1>
                <p class="text-muted">
                    123 Cycle Street<br>
                    Mumbai, Maharashtra 400001<br>
                    Phone: +91 98765 43210<br>
                    Email: info@bingocycle.com
                </p>
            </div>
            <div class="col-md-6 text-end">
                <h2>INVOICE</h2>
                <p class="mb-1"><strong>Invoice #:</strong> <?php echo $order_id; ?></p>
                <p class="mb-1"><strong>Date:</strong> <?php echo date('M j, Y', strtotime($order['created_at'])); ?></p>
                <p class="mb-1"><strong>Status:</strong>
                    <span class="badge bg-<?php echo $order['status'] == 'delivered' ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst($order['status']); ?>
                    </span>
                </p>
            </div>
        </div>

        <!-- Customer Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Bill To:</h5>
                <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                <?php echo htmlspecialchars($order['email']); ?><br>
                <?php echo htmlspecialchars($order['phone']); ?>
            </div>
            <div class="col-md-6">
                <h5>Ship To:</h5>
                <?php if ($shipping_address): ?>
                    <strong><?php echo htmlspecialchars($shipping_address['full_name']); ?></strong><br>
                    <?php echo htmlspecialchars($shipping_address['address_line1']); ?><br>
                    <?php if (!empty($shipping_address['address_line2'])): ?>
                        <?php echo htmlspecialchars($shipping_address['address_line2']); ?><br>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($shipping_address['city']); ?>,
                    <?php echo htmlspecialchars($shipping_address['state']); ?> -
                    <?php echo htmlspecialchars($shipping_address['postal_code']); ?><br>
                    <?php echo htmlspecialchars($shipping_address['country']); ?>
                <?php else: ?>
                    <p class="text-muted">Same as billing address</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Order Items -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered invoice-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = mysqli_fetch_assoc($items_result)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                            <td><?php echo $item['qty']; ?></td>
                            <td>₹<?php echo number_format($item['price'] * $item['qty'], 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total Amount:</strong></td>
                        <td><strong>₹<?php echo number_format($order['total_amount'], 2); ?></strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Payment Info -->
        <div class="row">
            <div class="col-md-6">
                <h5>Payment Information</h5>
                <p>
                    <strong>Method:</strong> <?php echo ucfirst($order['payment_method']); ?><br>
                    <strong>Status:</strong>
                    <span class="badge bg-<?php echo $order['payment_status'] == 'paid' ? 'success' : 'warning'; ?>">
                        <?php echo ucfirst($order['payment_status']); ?>
                    </span><br>
                    <?php if ($order['razorpay_order_id']): ?>
                        <strong>Transaction ID:</strong> <?php echo $order['razorpay_order_id']; ?>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-md-6 text-end">
                <div class="border p-3">
                    <h5>Thank you for your business!</h5>
                    <p class="text-muted mb-0">Bingo Cycle Team</p>
                </div>
            </div>
        </div>

        <!-- Print Button -->
        <div class="row mt-4 no-print">
            <div class="col-12 text-center">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="bi bi-printer"></i> Print Invoice
                </button>
                <button onclick="window.close()" class="btn btn-secondary">
                    <i class="bi bi-x-circle"></i> Close
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php if (isset($_GET['print'])): ?>
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    <?php endif; ?>
</body>

</html>