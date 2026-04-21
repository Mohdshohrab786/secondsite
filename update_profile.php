<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("admin/inc/config.php");

// Get User ID from session
$user_id = $_SESSION['user_id'] ?? $_SESSION['temp_user_id'] ?? null;
if (!$user_id) {
    die("Session error: User ID not found. Please login again.");
}

$affected = 0;

// 1. Handle Personal Info Update
if (isset($_POST['update_personal_info'])) {
    $full_name = mysqli_real_escape_string($con, $_POST['full_name']);
    $phone = mysqli_real_escape_string($con, $_POST['phone']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $sql = "UPDATE tbl_user SET full_name = '$full_name', phone = '$phone', password = '$password' WHERE id = '$user_id'";
    if (mysqli_query($con, $sql)) {
        $affected += mysqli_affected_rows($con);
    } else {
        die("SQL ERROR (tbl_user): " . mysqli_error($con));
    }
}

// 2. Handle Consolidated Address Update (Merged Billing/Shipping)
if (isset($_POST['update_user_address'])) {
    $u_name = mysqli_real_escape_string($con, $_POST['u_name']);
    $u_mobile = mysqli_real_escape_string($con, $_POST['u_mobile']);
    $u_address = mysqli_real_escape_string($con, $_POST['u_address']);
    $u_town = mysqli_real_escape_string($con, $_POST['u_town']);
    $u_state = mysqli_real_escape_string($con, $_POST['u_state']);
    $u_pincode = mysqli_real_escape_string($con, $_POST['u_pincode']);
    $u_landmark = mysqli_real_escape_string($con, $_POST['u_landmark'] ?? '');

    // Synchronize Billing Table
    $check_b = mysqli_query($con, "SELECT id FROM tbl_billing_address WHERE user_id = '$user_id'");
    if (mysqli_num_rows($check_b) > 0) {
        $sql_b = "UPDATE tbl_billing_address SET name = '$u_name', phone_no = '$u_mobile', street_address = '$u_address', town = '$u_town', state = '$u_state', pincode = '$u_pincode', landmark = '$u_landmark' WHERE user_id = '$user_id'";
    } else {
        $sql_b = "INSERT INTO tbl_billing_address (user_id, name, phone_no, street_address, town, state, pincode, landmark) VALUES ('$user_id', '$u_name', '$u_mobile', '$u_address', '$u_town', '$u_state', '$u_pincode', '$u_landmark')";
    }
    mysqli_query($con, $sql_b);

    // Synchronize Shipping Table
    $check_s = mysqli_query($con, "SELECT id FROM tbl_shipping_address WHERE user_id = '$user_id'");
    if (mysqli_num_rows($check_s) > 0) {
        $sql_s = "UPDATE tbl_shipping_address SET name = '$u_name', phone_no = '$u_mobile', street_address = '$u_address', town = '$u_town', state = '$u_state', pincode = '$u_pincode', landmark = '$u_landmark' WHERE user_id = '$user_id'";
    } else {
        $sql_s = "INSERT INTO tbl_shipping_address (user_id, name, phone_no, street_address, town, state, pincode, landmark) VALUES ('$user_id', '$u_name', '$u_mobile', '$u_address', '$u_town', '$u_state', '$u_pincode', '$u_landmark')";
    }
    mysqli_query($con, $sql_s);

    $affected = 1; // Mark as updated for alert
}

if ($affected > 0 || isset($_POST['update_personal_info']) || isset($_POST['update_user_address'])) {
    echo '<script>alert("Profile details updated successfully!"); window.location.href = "profile.php";</script>';
} else {
    header("Location: profile.php");
}
exit();
?>