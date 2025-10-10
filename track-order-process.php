<?php
include_once './layout/header.php';

// Database connection
include_once './include/connection.php';

// Initialize variables
$tracking_data = null;
$error = '';

// Check if trackid parameter exists in URL
if (isset($_GET['trackid']) && !empty($_GET['trackid'])) {
    $tracking_id = trim($_GET['trackid']);

    // Prepare and execute query
    $stmt = $conn->prepare("SELECT * FROM order_tracking WHERE tracking_id = ?");
    $stmt->bind_param("s", $tracking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tracking_data = $result->fetch_assoc();
    } else {
        $error = "No tracking information found for ID: " . htmlspecialchars($tracking_id);
    }

    $stmt->close();
} else {
    $error = "Please provide a tracking ID";
}

// Function to determine progress step based on status
function getProgressStep($status)
{
    $steps = [
        'Order Placed' => 1,
        'Shipping' => 2,
        'OnTheWay' => 3,
        'Near By City' => 4,
        'Deliver' => 5
    ];
    return $steps[$status] ?? 0;
}


// Function to format date
function formatDate($date)
{
    if (empty($date) || $date == '0000-00-00') return 'N/A';
    return date('d-m-Y', strtotime($date));
}

// Function to format amount
function formatAmount($amount)
{
    return '₹' . number_format($amount, 2);
}
?>

<link rel="stylesheet" href="https://www.bingocycles.com/css/TrackStyle.css">

<style>
    /* Your existing CSS styles here */
    * {
        box-sizing: border-box;
    }

    .tracking-wrapper {
        background: #f8f9fa;
        min-height: 100vh;
    }

    .tracking-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
    }

    .card-header {
        padding: 24px 32px;
        border-bottom: 1px solid #e5e7eb;
    }

    .card-header h2 {
        font-size: 20px;
        font-weight: 600;
        color: #eb453b;
        margin: 0;
    }

    .tracking-id {
        font-size: 13px;
        color: #6b7280;
        margin-top: 2px;
    }

    .card-body {
        padding: 32px;
    }

    /* Progress Steps */
    .progress-track {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin: 32px 0 40px;
        padding: 0 10px;
    }

    .progress-track::before {
        content: '';
        position: absolute;
        top: 16px;
        left: 50px;
        right: 50px;
        height: 2px;
        background: #e5e7eb;
        z-index: 0;
    }

    .progress-track::after {
        content: '';
        position: absolute;
        top: 16px;
        left: 50px;
        width: <?php echo $tracking_data ? (getProgressStep($tracking_data['status']) / 4 * 100) : 0; ?>%;
        height: 2px;
        background: #eb453b;
        z-index: 1;
        transition: width 0.5s ease;
    }

    .track-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        flex: 1;
        position: relative;
        z-index: 2;
    }

    .step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: white;
        border: 2px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: #eb453b;
        transition: all 0.3s ease;
    }

    .track-step.completed .step-circle {
        background: #eb453b;
        border-color: #eb453b;
        color: white;
    }

    .track-step.active .step-circle {
        background: white;
        border-color: #eb453b;
        border-width: 2.5px;
        color: #eb453b;
    }

    .step-label {
        margin-top: 8px;
        font-size: 11px;
        color: #9ca3af;
        text-align: center;
        font-weight: 500;
    }

    .track-step.completed .step-label,
    .track-step.active .step-label {
        color: #eb453b;
        font-weight: 600;
    }

    /* Info Section */
    .info-section {
        margin-top: 32px;
    }

    .info-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        padding: 16px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-field {
        display: flex;
        flex-direction: column;
    }

    .field-label {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 6px;
    }

    .field-value {
        font-size: 14px;
        color: #111827;
        font-weight: 500;
    }

    .status-active {
        display: inline-block;
        padding: 4px 12px;
        background: #eb453b;
        color: white;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .amount {
        font-size: 16px;
        font-weight: 600;
        color: #eb453b;
    }

    /* Sidebar */
    .info-sidebar {
        background: #eb453b;
        color: white;
        padding: 28px 24px;
        border-radius: 10px;
        height: 100%;
    }

    .sidebar-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .sidebar-desc {
        font-size: 12px;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.5;
        margin-bottom: 24px;
    }

    .route-info {
        margin: 20px 0;
    }

    .route-label {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.75);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .route-value {
        font-size: 14px;
        font-weight: 600;
    }

    .delivery-box {
        background: rgba(255, 255, 255, 0.12);
        padding: 20px;
        border-radius: 6px;
        text-align: center;
        margin-top: 24px;
    }

    .delivery-days {
        font-size: 42px;
        font-weight: 700;
        margin: 12px 0;
    }

    .delivery-label {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.85);
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }

    .error-message {
        background: #fee2e2;
        border: 1px solid #fecaca;
        color: #dc2626;
        padding: 16px;
        border-radius: 8px;
        text-align: center;
        margin: 20px 0;
    }

    @media (max-width: 992px) {
        .card-body {
            padding: 20px;
        }

        .card-header {
            padding: 20px;
        }

        .progress-track {
            flex-wrap: wrap;
            gap: 20px;
            margin: 24px 0 32px;
        }

        .progress-track::before,
        .progress-track::after {
            display: none;
        }

        .track-step {
            flex-direction: row;
            justify-content: flex-start;
            width: 100%;
        }

        .step-label {
            margin-top: 0;
            margin-left: 12px;
            text-align: left;
        }

        .info-row {
            grid-template-columns: 1fr;
            gap: 16px;
        }
    }

    @media (min-width: 993px) and (max-width: 1200px) {
        .info-row {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .card-header h2 {
            font-size: 18px;
        }

        .info-section {
            margin-top: 24px;
        }
    }
</style>


<main class="pt-12">
    <!-- hero section start -->
    <section class="inner-hero-section px-xl-20 px-lg-10 px-sm-7"
        style="background-image: url(assets/images/inner-page-banner.png);">
        <div class="container-fluid">
            <span class="text-animation-word text-h1 text-n100 mb-3">Order Tracking</span>
            <ul class="breadcrumb d-inline-flex align-items-center gap-lg-2 gap-1">
                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                <li class="breadcrumb-item active"><a href="#">Tracking</a></li>
            </ul>
        </div>
    </section>
    <!-- hero section end -->

    <!-- tracking section start -->
    <section class="tracking-wrapper pt-120 pb-120 px-xl-20 px-lg-10 px-sm-7">
        <div class="container-fluid">
            <div class="container" style="margin-top:30px;margin-bottom:30px;">

                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo $error; ?>
                    </div>
                <?php elseif ($tracking_data): ?>

                    <div class="row g-4">
                        <!-- Sidebar -->
                        <div class="col-lg-3 col-md-12">
                            <div class="info-sidebar">
                                <div class="sidebar-title">Order Status</div>
                                <div class="sidebar-desc">Track your delivery in real-time. Contact support for assistance.</div>

                                <div class="route-info">
                                    <div class="route-label">From</div>
                                    <div class="route-value"><?php echo htmlspecialchars($tracking_data['from_location'] ?? 'N/A'); ?></div>
                                </div>

                                <div class="route-info">
                                    <div class="route-label">To</div>
                                    <div class="route-value"><?php echo htmlspecialchars($tracking_data['to_location'] ?? 'N/A'); ?></div>
                                </div>

                                <div class="delivery-box">
                                    <div class="delivery-label">Expected Delivery</div>
                                    <div class="delivery-days">
                                        <?php
                                        if (!empty($tracking_data['expected_delivery']) && $tracking_data['expected_delivery'] != '0000-00-00') {
                                            $expected = new DateTime($tracking_data['expected_delivery']);
                                            $today = new DateTime();
                                            $diff = $today->diff($expected);
                                            echo $diff->days;
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </div>
                                    <div class="delivery-label">Days</div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content -->
                        <div class="col-lg-9 col-md-12">
                            <div class="tracking-card">
                                <div class="card-header">
                                    <h2>Tracking Details</h2>
                                    <div class="tracking-id">Order #<?php echo htmlspecialchars($tracking_data['tracking_id']); ?></div>
                                </div>

                                <div class="card-body">
                                    <!-- Progress Track -->
                                    <div class="progress-track">
                                        <?php
                                        $current_step = getProgressStep($tracking_data['status']);
                                        $steps = [
                                            1 => ['label' => 'Order Placed', 'status' => 'Order Placed'],
                                            2 => ['label' => 'Shipping', 'status' => 'Shipping'],
                                            3 => ['label' => 'On The Way', 'status' => 'OnTheWay'],
                                            4 => ['label' => 'Near By City', 'status' => 'Near By City'],
                                            5 => ['label' => 'Delivered', 'status' => 'Deliver']
                                        ];

                                        foreach ($steps as $step_num => $step):
                                            $is_completed = $current_step > $step_num;
                                            $is_current = $current_step == $step_num;

                                            // For last step (Delivered): show ✓ when reached
                                            $show_tick = ($step_num == 5 && $current_step >= 5);
                                        ?>
                                            <div class="track-step 
                                         <?php echo $is_completed ? 'completed' : ($is_current ? 'completed' : ''); ?>">
                                                <div class="step-circle">
                                                    <?php
                                                    if ($is_completed || $show_tick) {
                                                        echo '✓';
                                                    } elseif ($is_current) {
                                                        echo '✓';
                                                    } else {
                                                        echo '○';
                                                    }
                                                    ?>
                                                </div>
                                                <div class="step-label"><?php echo $step['label']; ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>



                                    <!-- Info Section -->
                                    <div class="info-section">
                                        <div class="info-row">
                                            <div class="info-field">
                                                <div class="field-label">Customer Name</div>
                                                <div class="field-value"><?php echo htmlspecialchars($tracking_data['customer_name'] ?? 'N/A'); ?></div>
                                            </div>
                                            <div class="info-field">
                                                <div class="field-label">Tracking ID</div>
                                                <div class="field-value"><?php echo htmlspecialchars($tracking_data['tracking_id']); ?></div>
                                            </div>
                                            <div class="info-field">
                                                <div class="field-label">Status</div>
                                                <div class="field-value">
                                                    <span class="status-active">
                                                        <?php
                                                        $status_display = [
                                                            'Shipping' => 'SHIPPING',
                                                            'OnTheWay' => 'ON THE WAY',
                                                            'Near By City' => 'NEAR BY CITY',
                                                            'Deliver' => 'Deliver'
                                                        ];
                                                        echo $status_display[$tracking_data['status']] ?? strtoupper($tracking_data['status']);
                                                        ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-field">
                                                <div class="field-label">Product</div>
                                                <div class="field-value"><?php echo htmlspecialchars($tracking_data['product_name'] ?? 'N/A'); ?></div>
                                            </div>
                                            <div class="info-field">
                                                <div class="field-label">Transport Company</div>
                                                <div class="field-value"><?php echo htmlspecialchars($tracking_data['transport_detail'] ?? 'N/A'); ?></div>
                                            </div>
                                            <div class="info-field">
                                                <div class="field-label">Total Amount</div>
                                                <div class="field-value amount"><?php echo formatAmount($tracking_data['total_amount']); ?></div>
                                            </div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-field">
                                                <div class="field-label">Dispatch Date</div>
                                                <div class="field-value"><?php echo formatDate($tracking_data['dispatch_date']); ?></div>
                                            </div>
                                            <div class="info-field">
                                                <div class="field-label">Expected Delivery</div>
                                                <div class="field-value"><?php echo formatDate($tracking_data['expected_delivery']); ?></div>
                                            </div>
                                            <div class="info-field">
                                                <div class="field-label">Advance Paid</div>
                                                <div class="field-value amount"><?php echo formatAmount($tracking_data['advance_amount']); ?></div>
                                            </div>
                                        </div>

                                        <div class="info-row">
                                            <div class="info-field">
                                                <div class="field-label">From Location</div>
                                                <div class="field-value"><?php echo htmlspecialchars($tracking_data['from_location'] ?? 'N/A'); ?></div>
                                            </div>
                                            <div class="info-field">
                                                <div class="field-label">To Location</div>
                                                <div class="field-value"><?php echo htmlspecialchars($tracking_data['to_location'] ?? 'N/A'); ?></div>
                                            </div>
                                            <div class="info-field">
                                                <div class="field-label">Remaining Payment</div>
                                                <div class="field-value amount"><?php echo formatAmount($tracking_data['remaining_amount']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </div>
    </section>
    <!-- tracking section end -->
</main>

<?php
include_once './layout/footer.php';
?>