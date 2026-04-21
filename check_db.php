<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("admin/inc/config.php");

echo "<h3>Database Connection:</h3>";
if (isset($con) && $con) {
    echo "✅ mysqli Connected<br>";
} else {
    echo "❌ mysqli Failed<br>";
}
if (isset($pdo) && $pdo) {
    echo "✅ PDO Connected<br>";
} else {
    echo "❌ PDO Failed<br>";
}

echo "<h3>Session Info:</h3>";
echo "Active user_id: " . ($_SESSION['user_id'] ?? 'NONE') . "<br>";
echo "Active temp_user_id: " . ($_SESSION['temp_user_id'] ?? 'NONE') . "<br>";

echo "<h3>ALL Assigned Coupons (tbl_user_coupon):</h3>";
echo "<p>Isme dekhein ki Mohd Shohrab ki ID ke aage kitne coupons hain.</p>";
$query_all_uc = "SELECT uc.*, c.coupon_code, u.full_name as partner_name 
                 FROM tbl_user_coupon uc 
                 LEFT JOIN tbl_coupon c ON uc.coupon_id = c.id 
                 LEFT JOIN tbl_user u ON uc.user_id = u.id";
$res_all = mysqli_query($con, $query_all_uc);
if ($res_all) {
    echo "<table border='1' cellpadding='5'>
          <tr><th>UC_ID</th><th>Partner Name (User_ID)</th><th>Coupon Code (ID)</th><th>Comm %</th></tr>";
    while ($row = mysqli_fetch_assoc($res_all)) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td><b>{$row['partner_name']}</b> (ID: {$row['user_id']})</td>
                <td>{$row['coupon_code']} (ID: {$row['coupon_id']})</td>
                <td>{$row['percentage']}%</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "Query Error: " . mysqli_error($con);
}

echo "<h3>Cleanup Preview (March 14 Cutoff):</h3>";
$cutoff = '2026-03-14';
// We assume Y-m-d format for now based on standard PHP, but we'll show samples to be sure.
$res_sample = mysqli_query($con, "SELECT order_date FROM tbl_order LIMIT 5");
echo "Sample 5 Dates in DB: ";
while ($rs = mysqli_fetch_assoc($res_sample)) {
    echo "[{$rs['order_date']}] ";
}
echo "<br><br>";

// Trying to count based on date string comparison
$q_before = "SELECT COUNT(*) as cnt FROM tbl_order WHERE order_date < '2026-03-14'";
$c_before = mysqli_fetch_assoc(mysqli_query($con, $q_before))['cnt'] ?? 0;
$q_after = "SELECT COUNT(*) as cnt FROM tbl_order WHERE order_date >= '2026-03-14'";
$c_after = mysqli_fetch_assoc(mysqli_query($con, $q_after))['cnt'] ?? 0;

echo "Orders BEFORE March 14: <b>$c_before</b><br>";
echo "Orders ON/AFTER March 14: <b>$c_after</b><br>";
echo "<p style='color:red'>WARNING: If we 'Clear', we will delete the 'BEFORE' group or 'AFTER' group. Please specify which one.</p>";

echo "<h3>tbl_order Stats:</h3>";
$res_stat = mysqli_query($con, "SELECT applied_coupon, COUNT(*) as count FROM tbl_order GROUP BY applied_coupon");
if ($res_stat) {
    echo "<table border='1' cellpadding='5'><tr><th>Coupon Code</th><th>Orders Found</th></tr>";
    while ($row = mysqli_fetch_assoc($res_stat)) {
        echo "<tr><td>" . ($row['applied_coupon'] ?: 'NONE') . "</td><td>{$row['count']}</td></tr>";
    }
    echo "</table>";
}
?>