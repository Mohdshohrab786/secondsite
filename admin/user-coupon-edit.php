<?php require_once('header.php');

$error_message = '';
$success_message = '';

if (!isset($_GET['id'])) {
    header('location: logout.php');
    exit;
} else {
    // Check if the id exists
    $stmt = $pdo->prepare("SELECT * FROM tbl_user_coupon WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $total = $stmt->rowCount();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($total == 0) {
        header('location: logout.php');
        exit;
    }
}

if (isset($_POST['form1'])) {

    $user_id = trim($_POST['user_id']);
    $coupon_id = trim($_POST['coupon_id']);
    $percentage = trim($_POST['percentage']);

    if ($user_id === '' || $coupon_id === '' || $percentage === '') {
        $error_message = 'All fields are required.';
    } elseif (!is_numeric($percentage) || $percentage < 0) {
        $error_message = 'Percentage must be a non-negative number.';
    } else {
        // Check for duplicate assignment (excluding the current one)
        $stmt = $pdo->prepare("SELECT * FROM tbl_user_coupon WHERE user_id = ? AND coupon_id = ? AND id != ?");
        $stmt->execute([$user_id, $coupon_id, $_GET['id']]);

        if ($stmt->rowCount() > 0) {
            $error_message = 'This coupon is already assigned to this user.';
        } else {
            // Update assignment
            $stmt = $pdo->prepare("UPDATE tbl_user_coupon SET user_id = ?, coupon_id = ?, percentage = ? WHERE id = ?");
            $stmt->execute([$user_id, $coupon_id, $percentage, $_GET['id']]);
            $success_message = 'User coupon updated successfully.';
        }
    }

    // Refresh the row after update
    $stmt = $pdo->prepare("SELECT * FROM tbl_user_coupon WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<section class="content-header">
    <h1>Edit User Coupon</h1>
</section>

<section class="content">
    <?php if ($error_message): ?>
        <div class="callout callout-danger"><?= $error_message; ?></div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="callout callout-success"><?= $success_message; ?></div>
    <?php endif; ?>

    <form class="form-horizontal" action="" method="post">
        <div class="box box-info">
            <div class="box-body">

                <div class="form-group">
                    <label class="col-sm-3 control-label">Select User <span>*</span></label>
                    <div class="col-sm-4">
                        <select name="user_id" class="form-control select2" required>
                            <option value="">-- Select --</option>
                            <?php
                            $stmt = $pdo->query("SELECT id, full_name, email FROM tbl_user ORDER BY full_name ASC");
                            while ($user_row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($user_row['id'] == $row['user_id']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($user_row['id']) . '" ' . $selected . '>' . htmlspecialchars($user_row['full_name']) . ' (' . htmlspecialchars($user_row['email']) . ')</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Select Coupon <span>*</span></label>
                    <div class="col-sm-4">
                        <select name="coupon_id" class="form-control" required>
                            <option value="">-- Select --</option>
                            <?php
                            $stmt = $pdo->query("SELECT id, coupon_code FROM tbl_coupon ORDER BY coupon_code ASC");
                            while ($coupon_row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $selected = ($coupon_row['id'] == $row['coupon_id']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($coupon_row['id']) . '" ' . $selected . '>' . htmlspecialchars($coupon_row['coupon_code']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Commission (%) <span>*</span></label>
                    <div class="col-sm-4">
                        <input type="number" name="percentage" class="form-control" value="<?= htmlspecialchars($row['percentage']); ?>" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-6">
                        <button type="submit" class="btn btn-success" name="form1">Update</button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</section>

<?php require_once('footer.php'); ?>