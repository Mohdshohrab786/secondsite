<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("admin/inc/config.php");

$user_id = "";
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_SESSION['temp_user_id'])) {
    $user_id = $_SESSION['temp_user_id'];
} else {
    header("location: login.php");
    exit;
}

// Fetch user data
$query_user = "SELECT * FROM tbl_user WHERE id = '$user_id'";
$result_user = mysqli_query($con, $query_user);
$user_info = mysqli_fetch_assoc($result_user);

// Fetch assigned coupons
$query_coupons = "SELECT uc.*, c.coupon_code 
                  FROM tbl_user_coupon uc 
                  JOIN tbl_coupon c ON uc.coupon_id = c.id 
                  WHERE uc.user_id = '$user_id'";
$result_coupons = mysqli_query($con, $query_coupons);
$assigned_coupons = [];
if ($result_coupons) {
    while ($row = mysqli_fetch_assoc($result_coupons)) {
        $assigned_coupons[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>My Account | Dashboard</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="libs/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #ffb200 0%, #fd9800 100%);
            --secondary-gradient: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
            --glass-bg: rgba(255, 255, 255, 0.95);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; color: #333; }
        .dashboard-header { background: var(--primary-gradient); padding: 50px 0 80px 0; color: white; margin-bottom: -50px; }
        .profile-card { background: var(--glass-bg); border-radius: 20px; box-shadow: var(--card-shadow); padding: 30px; margin-bottom: 30px; border: 1px solid rgba(255,255,255,0.3); }
        .nav-pills-custom .nav-link { color: #555; font-weight: 500; padding: 12px 25px; border-radius: 12px; transition: all 0.3s ease; margin-right: 10px; border: 1px solid transparent; }
        .nav-pills-custom .nav-link.active { background: var(--primary-gradient); color: white !important; box-shadow: 0 5px 15px rgba(253, 152, 0, 0.3); }
        .nav-pills-custom { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 5px; scrollbar-width: none; }
        .nav-pills-custom::-webkit-scrollbar { display: none; }
        .stat-card { border-radius: 16px; padding: 20px; color: white; height: 100%; transition: transform 0.3s ease; display: flex; align-items: center; gap: 15px; }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-icon { font-size: 2rem; opacity: 0.3; }
        .stat-value { font-size: 1.3rem; font-weight: 700; display: block; }
        .stat-label { font-size: 0.8rem; opacity: 0.9; }
        .badge-commission { background: rgba(46, 125, 50, 0.1); color: #2e7d32; padding: 8px 12px; border-radius: 8px; font-weight: 600; }
        .btn-modern { padding: 12px 25px; border-radius: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s ease; }
        .btn-modern-primary { background: var(--primary-gradient); color: white; border: none; }
        .table-modern th { font-weight: 600; padding: 15px; background: #f1f3f5; border: none; }
        .table-modern td { padding: 15px; vertical-align: middle; }
    </style>
</head>

<body>
    <?php include("include/header.php"); ?>

    <div class="dashboard-header text-center">
        <div class="container">
            <h1 class="fw-bold mb-2">Welcome, <?= htmlspecialchars($user_info['full_name']); ?>!</h1>
            <p class="opacity-75">Partner Dashboard (ID: <?= $user_id; ?>)</p>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <div class="col-12">
                <div class="profile-card">
                    <ul class="nav nav-pills nav-pills-custom mb-4 justify-content-center" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button"><i class="fas fa-user-circle me-2"></i>Personal Info</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="pills-address-tab" data-bs-toggle="pill" data-bs-target="#pills-address" type="button"><i class="fas fa-map-marker-alt me-2"></i>Addresses</button>
                        </li>
                        <?php if (count($assigned_coupons) > 0): ?>
                        <li class="nav-item">
                            <button class="nav-link" id="pills-commission-tab" data-bs-toggle="pill" data-bs-target="#pills-commission" type="button"><i class="fas fa-money-bill-wave me-2"></i>Commission</button>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item"><a href="my-orders.php" class="nav-link text-dark"><i class="fas fa-shopping-bag me-2"></i>Orders</a></li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        
                        <!-- Tab 1: Personal Info -->
                        <div class="tab-pane fade show active" id="pills-profile">
                            <div class="row justify-content-center">
                                <div class="col-md-9">
                                    <form action="update_profile.php" method="post">
                                        <div class="row g-3">
                                            <div class="col-md-6 text-start">
                                                <label class="form-label fw-bold small">Full Name</label>
                                                <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user_info['full_name']); ?>">
                                            </div>
                                            <div class="col-md-6 text-start">
                                                <label class="form-label fw-bold small">Mobile Number</label>
                                                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user_info['phone']); ?>">
                                            </div>
                                            <div class="col-md-6 text-start">
                                                <label class="form-label fw-bold small">Email Address</label>
                                                <input type="email" value="<?= htmlspecialchars($user_info['email']); ?>" class="form-control bg-light" readonly>
                                            </div>
                                            <div class="col-md-6 text-start">
                                                <label class="form-label fw-bold small">Security Password</label>
                                                <input type="text" name="password" class="form-control" value="<?= htmlspecialchars($user_info['password']); ?>">
                                            </div>
                                            <div class="col-12 text-center mt-4">
                                                <button type="submit" name="update_personal_info" class="btn btn-modern btn-modern-primary">Update Personal Info</button>
                                            </div>

                                            <div class="col-12 mt-5">
                                                <div class="p-3 bg-light rounded-4 text-start border shadow-sm">
                                                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-map-marked-alt me-2"></i>Your Saved Address</h6>
                                                    <?php
                                                    $res_ba = mysqli_query($con, "SELECT * FROM tbl_billing_address WHERE user_id = '$user_id'");
                                                    if($ba = mysqli_fetch_assoc($res_ba)) {
                                                        echo "<div class='fw-bold text-dark mb-1'>".htmlspecialchars($ba['name'])."</div>";
                                                        echo "<div class='text-muted small'>".htmlspecialchars($ba['street_address']).", ".htmlspecialchars($ba['town']).", ".htmlspecialchars($ba['state'])." - ".htmlspecialchars($ba['pincode'])."</div>";
                                                        echo "<div class='text-muted small'><i class='fas fa-phone-alt me-1'></i>".htmlspecialchars($ba['phone_no'])."</div>";
                                                    } else { 
                                                        echo "<span class='text-muted small'><i class='fas fa-info-circle me-1'></i>No address saved yet. Update it in the 'Addresses' tab.</span>"; 
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 2: Addresses -->
                        <div class="tab-pane fade" id="pills-address">
                            <form action="update_profile.php" method="post">
                            <div class="row justify-content-center text-start">
                                <div class="col-md-9">
                                    <div class="p-4 bg-light rounded-4 mb-3 border">
                                        <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-map-marker-alt me-2"></i>My Global Address</h5>
                                        <?php
                                        $res_b = mysqli_query($con, "SELECT * FROM tbl_billing_address WHERE user_id = '$user_id'");
                                        $addr = mysqli_fetch_assoc($res_b) ?? [];
                                        ?>
                                        <div class="row g-3">
                                            <div class="col-md-6"><label class="form-label small fw-bold">Full Name</label><input type="text" name="u_name" class="form-control" value="<?= $addr['name'] ?? $user_info['full_name']; ?>"></div>
                                            <div class="col-md-6"><label class="form-label small fw-bold">Mobile</label><input type="text" name="u_mobile" class="form-control" value="<?= $addr['phone_no'] ?? $user_info['phone']; ?>"></div>
                                            <div class="col-12"><label class="form-label small fw-bold">Street Address</label><input type="text" name="u_address" class="form-control" value="<?= $addr['street_address'] ?? ''; ?>" placeholder="Street, Building, Landmark"></div>
                                            <div class="col-md-4"><label class="form-label small fw-bold">City/Town</label><input type="text" name="u_town" class="form-control" value="<?= $addr['town'] ?? ''; ?>"></div>
                                            <div class="col-md-4"><label class="form-label small fw-bold">State</label><input type="text" name="u_state" class="form-control" value="<?= $addr['state'] ?? ''; ?>"></div>
                                            <div class="col-md-4"><label class="form-label small fw-bold">Pincode</label><input type="text" name="u_pincode" class="form-control" value="<?= $addr['pincode'] ?? ''; ?>"></div>
                                        </div>
                                    </div>
                                    <div class="text-center"><button type="submit" name="update_user_address" class="btn btn-modern btn-modern-primary">Save My Address</button></div>
                                </div>
                            </div>
                            </form>
                        </div>

                        <!-- Tab 3: Commission -->
                        <div class="tab-pane fade" id="pills-commission">
                            <?php if ($assigned_coupons): 
                                $total_sales = 0; $total_earned = 0; $total_paid = 0; $codes = [];
                                foreach($assigned_coupons as $uc) {
                                    $code = $uc['coupon_code']; $codes[] = $code;
                                    $q = mysqli_query($con, "SELECT SUM(p_actual_price * p_quantity) as val FROM tbl_order WHERE LOWER(applied_coupon) = LOWER('$code') AND order_status = 'Success'");
                                    $d = mysqli_fetch_assoc($q); $s = (float)($d['val'] ?? 0); $total_sales += $s;
                                    $total_earned += ($s * (float)$uc['percentage']) / 100;
                                    $q_p = mysqli_query($con, "SELECT SUM(amount_paid) as paid FROM tbl_commission_payment WHERE user_id = '$user_id' AND coupon_id = '{$uc['coupon_id']}'");
                                    $total_paid += (float)(mysqli_fetch_assoc($q_p)['paid'] ?? 0);
                                }
                                $balance = $total_earned - $total_paid;
                            ?>
                                <div class="row g-2 mb-4 text-start justify-content-center">
                                    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card earned" style="background: #1e3c72;"><div><span class="stat-label">Sales</span><span class="stat-value">₹<?= number_format($total_sales, 0); ?></span></div></div></div>
                                    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card earned" style="background: #2a5298;"><div><span class="stat-label">Earned</span><span class="stat-value">₹<?= number_format($total_earned, 0); ?></span></div></div></div>
                                    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card paid" style="background: #2e7d32;"><div><span class="stat-label">Received</span><span class="stat-value">₹<?= number_format($total_paid, 0); ?></span></div></div></div>
                                    <div class="col-6 col-md-4 col-lg-2"><div class="stat-card balance" style="background: #d31027;"><div><span class="stat-label">Pending</span><span class="stat-value">₹<?= number_format($balance, 0); ?></span></div></div></div>
                                </div>

                                <h5 class="fw-bold mb-3 text-start mt-4"><i class="fas fa-ticket-alt me-2"></i>Assigned Coupons Performance</h5>
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-hover text-start">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Coupon Code</th>
                                                <th>Comm. %</th>
                                                <th>Items Sold</th>
                                                <th>Sales Amount</th>
                                                <th>Earnings</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            foreach($assigned_coupons as $uc) {
                                                $code = $uc['coupon_code'];
                                                $q = mysqli_query($con, "SELECT SUM(p_quantity) as qty, SUM(p_actual_price * p_quantity) as val FROM tbl_order WHERE LOWER(applied_coupon) = LOWER('$code') AND order_status = 'Success'");
                                                $d = mysqli_fetch_assoc($q);
                                                $s_val = (float)($d['val'] ?? 0);
                                                $s_qty = (int)($d['qty'] ?? 0);
                                                $s_earn = ($s_val * (float)$uc['percentage']) / 100;
                                            ?>
                                                <tr>
                                                    <td><span class="badge bg-primary px-3 py-2"><?= htmlspecialchars($code); ?></span></td>
                                                    <td class="fw-bold"><?= $uc['percentage']; ?>%</td>
                                                    <td><?= $s_qty; ?> Items</td>
                                                    <td>₹<?= number_format($s_val, 0); ?></td>
                                                    <td class="text-success fw-bold">₹<?= number_format($s_earn, 2); ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>

                                <h5 class="fw-bold mb-3 text-start mt-4"><i class="fas fa-history me-2"></i>Sales History</h5>
                                <div class="table-responsive">
                                    <table class="table table-modern text-start">
                                        <thead><tr><th>Order</th><th>Product</th><th>Earning</th></tr></thead>
                                        <tbody>
                                            <?php
                                            $query_h = "SELECT o.*, uc.percentage FROM tbl_order o JOIN tbl_coupon c ON LOWER(o.applied_coupon) = LOWER(c.coupon_code) JOIN tbl_user_coupon uc ON c.id = uc.coupon_id WHERE uc.user_id = '$user_id' AND o.order_status = 'Success' ORDER BY o.id DESC";
                                            $res_h = mysqli_query($con, $query_h);
                                            if ($res_h && mysqli_num_rows($res_h) > 0) {
                                                while($h = mysqli_fetch_assoc($res_h)) {
                                                    $comm = ((float)$h['p_actual_price'] * (int)$h['p_quantity'] * (float)$h['percentage']) / 100; ?>
                                                    <tr>
                                                        <td>#<?= htmlspecialchars($h['order_id']); ?><br><small><?= htmlspecialchars($h['order_date']); ?></small></td>
                                                        <td><?= htmlspecialchars($h['p_name']); ?><br><small class='text-primary'><?= htmlspecialchars($h['applied_coupon']); ?></small></td>
                                                        <td><div class="badge-commission">₹<?= number_format($comm, 2); ?></div></td>
                                                    </tr>
                                            <?php } } else { echo "<tr><td colspan='3' class='text-center py-4'>No sales recorded yet.</td></tr>"; } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include("include/footer.php"); ?>
    <script src="libs/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>