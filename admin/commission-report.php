<?php require_once('header.php'); ?>

<?php
if(isset($_POST['pay_commission'])) {
    $user_id = $_POST['user_id'];
    $coupon_id = $_POST['coupon_id'];
    $amount_paid = $_POST['amount_paid'];
    $payment_date = date('Y-m-d H:i:s');
    
    $statement = $pdo->prepare("INSERT INTO tbl_commission_payment (user_id, coupon_id, amount_paid, payment_date) VALUES (?,?,?,?)");
    $statement->execute([$user_id, $coupon_id, $amount_paid, $payment_date]);
    
    header('location: commission-report.php');
    exit;
}
?>

<?php
// Calculate Grand Totals for Summary Cards
$grand_total_sales = 0;
$grand_total_earned = 0;
$grand_total_paid = 0;

$stmt_summary = $pdo->prepare("SELECT uc.*, c.coupon_code 
                                FROM tbl_user_coupon uc 
                                JOIN tbl_coupon c ON uc.coupon_id = c.id");
$stmt_summary->execute();
$all_coupons = $stmt_summary->fetchAll(PDO::FETCH_ASSOC);

foreach ($all_coupons as $c) {
    $perc = $c['percentage'];
    
    // Precise Sales for this specific partner and THIS SPECIFIC COUPON
    $st = $pdo->prepare("SELECT SUM(p_actual_price) as sales FROM tbl_order WHERE commission_user_id = ? AND LOWER(applied_coupon) = LOWER(?) AND order_status = 'Success'");
    $st->execute([$c['user_id'], $c['coupon_code']]);
    $sales = $st->fetch(PDO::FETCH_ASSOC)['sales'] ?? 0;
    
    // Paid
    $sp = $pdo->prepare("SELECT SUM(amount_paid) as paid FROM tbl_commission_payment WHERE user_id = ? AND coupon_id = ?");
    $sp->execute([$c['user_id'], $c['coupon_id']]);
    $paid = $sp->fetch(PDO::FETCH_ASSOC)['paid'] ?? 0;
    
    $grand_total_sales += $sales;
    $grand_total_earned += ($sales * $perc) / 100;
    $grand_total_paid += $paid;
}
$grand_total_balance = $grand_total_earned - $grand_total_paid;
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>User Commission Dashboard</h1>
	</div>
</section>

<section class="content">
    <!-- Summary Cards -->
    <div class="row">
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="fa fa-shopping-cart"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Partner Sales</span>
              <span class="info-box-number">₹<?= number_format($grand_total_sales, 2); ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-green"><i class="fa fa-money"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Expected Commission</span>
              <span class="info-box-number">₹<?= number_format($grand_total_earned, 2); ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="fa fa-handshake-o"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Total Paid Out</span>
              <span class="info-box-number">₹<?= number_format($grand_total_paid, 2); ?></span>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-12">
          <div class="info-box">
            <span class="info-box-icon bg-red"><i class="fa fa-clock-o"></i></span>
            <div class="info-box-content">
              <span class="info-box-text">Remaining Balance</span>
              <span class="info-box-number">₹<?= number_format($grand_total_balance, 2); ?></span>
            </div>
          </div>
        </div>
    </div>

	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Individual User Performance</h3>
                </div>
				<div class="box-body table-responsive">
                    <div class="well well-sm">
                        <i class="fa fa-info-circle text-info"></i> This section shows the performance and payout status for every user assigned a commission coupon.
                    </div>
					<table id="example1" class="table table-bordered table-hover table-striped">
						<thead class="thead-dark">
							<tr>
								<th width="30">#</th>
								<th>Partner Detail</th>
								<th>Coupon Code</th>
								<th>Sales Count</th>
								<th>Total Sales</th>
								<th>Earnings</th>
								<th>Paid</th>
								<th>Balance</th>
								<th width="140">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = 0;
							// Get users with assigned coupons
							$statement = $pdo->prepare("SELECT uc.*, u.full_name as user_name, u.email as user_email, c.coupon_code 
															FROM tbl_user_coupon uc 
															JOIN tbl_user u ON uc.user_id = u.id 
															JOIN tbl_coupon c ON uc.coupon_id = c.id 
															ORDER BY u.full_name ASC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);

							foreach ($result as $row) {
								$i++;
                                $coupon_code = $row['coupon_code'];
                                $percentage = $row['percentage'];
                                
                                // Precise Fetch based on commission_user_id AND coupon_code
                                $stmt_orders = $pdo->prepare("SELECT COUNT(*) as total_items, SUM(p_actual_price) as total_sales 
                                                               FROM tbl_order 
                                                               WHERE commission_user_id = ? AND LOWER(applied_coupon) = LOWER(?) AND order_status = 'Success'");
                                $stmt_orders->execute([$row['user_id'], $coupon_code]);
                                $order_data = $stmt_orders->fetch(PDO::FETCH_ASSOC);
                                
                                $total_items = $order_data['total_items'] ?? 0;
                                $total_sales = $order_data['total_sales'] ?? 0;
                                $total_earned = ($total_sales * $percentage) / 100;

                                // Fetch payments for this coupon
                                $stmt_payments = $pdo->prepare("SELECT SUM(amount_paid) as total_paid FROM tbl_commission_payment WHERE user_id = ? AND coupon_id = ?");
                                $stmt_payments->execute([$row['user_id'], $row['coupon_id']]);
                                $payment_data = $stmt_payments->fetch(PDO::FETCH_ASSOC);
                                $total_paid = $payment_data['total_paid'] ?? 0;
                                
                                $balance = $total_earned - $total_paid;
							?>
								<tr>
									<td><?= $i; ?></td>
									<td>
                                        <b><?= htmlspecialchars($row['user_name']); ?></b><br>
                                        <small class="text-muted"><?= htmlspecialchars($row['user_email']); ?></small>
                                    </td>
									<td>
                                        <span class="label label-primary" style="font-size: 14px;"><?= htmlspecialchars($row['coupon_code']); ?></span>
                                        <br><small><?= $percentage; ?>% Comm.</small>
                                    </td>
									<td><?= $total_items; ?></td>
									<td class="text-bold">₹<?= number_format($total_sales, 2); ?></td>
									<td><b class="text-success">₹<?= number_format($total_earned, 2); ?></b></td>
									<td class="text-info">₹<?= number_format($total_paid, 2); ?></td>
									<td><b class="text-danger">₹<?= number_format($balance, 2); ?></b></td>
									<td>
										<a href="commission-order-view.php?coupon=<?= urlencode($coupon_code); ?>&percent=<?= $percentage; ?>" class="btn btn-info btn-xs" title="View Commission History">
                                            <i class="fa fa-history"></i> History
                                        </a>
                                        <?php if($balance > 0): ?>
                                            <form method="post" style="display:inline;" onsubmit="return confirm('Confirm payment of ₹<?= number_format($balance, 2); ?>?');">
                                                <input type="hidden" name="user_id" value="<?= $row['user_id']; ?>">
                                                <input type="hidden" name="coupon_id" value="<?= $row['coupon_id']; ?>">
                                                <input type="hidden" name="amount_paid" value="<?= $balance; ?>">
                                                <button type="submit" name="pay_commission" class="btn btn-success btn-xs">
                                                    <i class="fa fa-money"></i> Pay Due
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="label label-success"><i class="fa fa-check"></i> Cleared</span>
                                        <?php endif; ?>
									</td>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="col-md-12">
			<div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Detailed Orders with Coupons</h3>
                </div>
				<div class="box-body table-responsive">
                    <div class="well well-sm">
                        <i class="fa fa-shopping-bag text-success"></i> This section lists every successful order where a partner's coupon was applied.
                    </div>
					<table id="example2" class="table table-bordered table-hover table-striped">
						<thead class="bg-gray">
							<tr>
								<th width="30">#</th>
								<th>Order ID & Date</th>
								<th>Customer Details</th>
								<th>Coupon Used</th>
								<th>Partner (Earnings)</th>
								<th>Order Total</th>
								<th>Commission</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$j = 0;
							// Fetch grouped orders with coupons
							$st_ord = $pdo->prepare("SELECT order_id, order_date, billing_address, applied_coupon, commission_user_id, 
                                                            SUM(p_actual_price * no_of_item) as total_sales
                                                     FROM tbl_order 
                                                     WHERE applied_coupon != '' AND order_status = 'Success' 
                                                     GROUP BY order_id 
                                                     ORDER BY id DESC");
							$st_ord->execute();
							$orders_with_coupons = $st_ord->fetchAll(PDO::FETCH_ASSOC);

							foreach ($orders_with_coupons as $ord) {
								$j++;
                                
                                // Get Partner Details & Percentage
                                $partner_name = "Unknown Partner";
                                $comm_perc = 0;
                                if ($ord['commission_user_id'] != 0) {
                                    $st_p = $pdo->prepare("SELECT u.full_name, uc.percentage 
                                                           FROM tbl_user u 
                                                           JOIN tbl_user_coupon uc ON u.id = uc.user_id 
                                                           JOIN tbl_coupon c ON uc.coupon_id = c.id
                                                           WHERE u.id = ? AND c.coupon_code = ? LIMIT 1");
                                    $st_p->execute([$ord['commission_user_id'], $ord['applied_coupon']]);
                                    $p_data = $st_p->fetch(PDO::FETCH_ASSOC);
                                    if ($p_data) {
                                        $partner_name = $p_data['full_name'];
                                        $comm_perc = $p_data['percentage'];
                                    }
                                }

                                $ord_total = $ord['total_sales'];
                                $ord_comm = ($ord_total * $comm_perc) / 100;

                                // Parse customer name from billing address
                                $addr_parts = explode(',', $ord['billing_address']);
                                $customer_name = trim($addr_parts[0]);
							?>
								<tr>
									<td><?= $j; ?></td>
									<td>
                                        <b>#<?= htmlspecialchars($ord['order_id']); ?></b><br>
                                        <small><?= htmlspecialchars($ord['order_date']); ?></small>
                                    </td>
									<td>
                                        <?= htmlspecialchars($customer_name); ?>
                                    </td>
									<td>
                                        <span class="label label-primary"><?= htmlspecialchars($ord['applied_coupon']); ?></span>
                                    </td>
									<td>
                                        <b><?= htmlspecialchars($partner_name); ?></b><br>
                                        <small class="text-muted">(<?= $comm_perc; ?>%)</small>
                                    </td>
									<td class="text-bold">₹<?= number_format($ord_total, 2); ?></td>
									<td><b class="text-success">₹<?= number_format($ord_comm, 2); ?></b></td>
								</tr>
							<?php
							}
							?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>
