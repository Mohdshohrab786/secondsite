<?php
// Spoof HTTP_HOST for config.php to detect localhost environment correctly in CLI
$_SERVER['HTTP_HOST'] = 'localhost';

// Fix path for CLI execution
$config_path = dirname(__DIR__) . "/admin/inc/config.php";
include($config_path);

echo "<h3>Clearing Payment History Table...</h3>";

if(mysqli_query($con, "TRUNCATE TABLE tbl_commission_payment")) {
    echo "✅ SUCCESS: tbl_commission_payment has been cleared (0 records).";
} else {
    echo "❌ ERROR: " . mysqli_error($con);
}
?>
