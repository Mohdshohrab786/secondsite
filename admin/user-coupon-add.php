<?php require_once('header.php');

$error_message = '';
$success_message = '';

if (isset($_POST['form1'])) {

    $user_id = trim($_POST['user_id']);
    $coupon_id = trim($_POST['coupon_id']);
    $percentage = trim($_POST['percentage']);

    if ($user_id === '' || $coupon_id === '' || $percentage === '') {
        $error_message = 'All fields are required.';
    } elseif (!is_numeric($percentage) || $percentage < 0) {
        $error_message = 'Percentage must be a non-negative number.';
    } else {
        // Check for duplicate assignment
        $stmt = $pdo->prepare("SELECT * FROM tbl_user_coupon WHERE user_id = ? AND coupon_id = ?");
        $stmt->execute([$user_id, $coupon_id]);

        if ($stmt->rowCount() > 0) {
            $error_message = 'This coupon is already assigned to this user.';
        } else {
            // Insert assignment
            $stmt = $pdo->prepare("INSERT INTO tbl_user_coupon (user_id, coupon_id, percentage) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $coupon_id, $percentage]);
            $success_message = 'User coupon assigned successfully.';
        }
    }
}
?>

<section class="content-header">
    <h1>Add User Coupon</h1>
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
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['full_name']) . ' (' . htmlspecialchars($row['email']) . ')</option>';
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
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['coupon_code']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Commission (%) <span>*</span></label>
                    <div class="col-sm-4">
                        <input type="number" name="percentage" class="form-control" min="0" step="0.01" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-sm-offset-3 col-sm-6">
                        <button type="submit" class="btn btn-success" name="form1">Submit</button>
                    </div>
                </div>

            </div>
        </div>
    </form>
</section>

<?php require_once('footer.php'); ?>