<?php
require_once '../config/db.php';

$update_success = false;

$sql_ngay = "
    SELECT DISTINCT TIME 
    FROM NGAY 
    WHERE DATE = CURDATE() 
    AND (TIME > CURTIME() OR TIME = (SELECT MAX(TIME) FROM NGAY WHERE DATE = CURDATE() AND TIME <= CURTIME()))
    ORDER BY TIME ASC
";
$query_ngay = mysqli_query($conn, $sql_ngay);

$khung_gio = [];
while ($row = mysqli_fetch_assoc($query_ngay)) {
    $khung_gio[] = $row['TIME'];
}

if (isset($_POST['update_time'])) {
    date_default_timezone_set('Asia/Ho_Chi_Minh');
    $now = date('H:i:s');

    $latest_time = null;
    foreach ($khung_gio as $time) {
        if ($time <= $now) {
            $latest_time = $time;
        }
    }

    if ($latest_time !== null) {
        $sql_update = "
            UPDATE TRANGTHAI
            SET TIME = ?
            WHERE DATE = CURDATE()
            AND TRANGTHAI = 'Có Khách'
            AND TIME < ?
        ";
        $stmt = mysqli_prepare($conn, $sql_update);
        mysqli_stmt_bind_param($stmt, 'ss', $latest_time, $now);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $update_success = true;
    }
}

$sql_ban = "SELECT * FROM BAN ORDER BY STT ASC";
$query_ban = mysqli_query($conn, $sql_ban);

$sql_trangthai = "
    SELECT MABAN, TRANGTHAI, TIME FROM TRANGTHAI
    WHERE DATE = CURDATE()
    ORDER BY TIME ASC
";
$query_trangthai = mysqli_query($conn, $sql_trangthai);

$trangthai_data = [];
while ($row = mysqli_fetch_assoc($query_trangthai)) {
    $trangthai_data[$row['MABAN']][$row['TIME']] = $row['TRANGTHAI'];
}
?>

<div class="container mt-4">
    <?php if ($update_success): ?>
        <div class="alert alert-success text-center" role="alert">
            Đã cập nhật khung giờ thành công!
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="text-center flex-grow-1 mb-0">Danh Sách Bàn</h2>
        <form method="POST">
            <button type="submit" name="update_time" class="btn btn-danger">Update</button>
        </form>
    </div>
    <div style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
    <div class="row">
        <?php while ($row_ban = mysqli_fetch_assoc($query_ban)) {
            $maban = $row_ban['MABAN'] ?? 'Không xác định';

            $hasCustomer = false;
            if (isset($trangthai_data[$maban])) {
                foreach ($trangthai_data[$maban] as $trangthai) {
                    if ($trangthai == 'Có Khách') {
                        $hasCustomer = true;
                        break;
                    }
                }
            }
        ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5>Bàn <?php echo $row_ban['STT']; ?></h5>
                    </div>
                    <div class="card-body">
                        <form action="index.php?page_layout=Datban" method="POST">
                            <input type="hidden" name="maban" value="<?php echo $maban; ?>">
                            <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">

                            <table class="table time-table">
                                <thead>
                                    <tr>
                                        <th>Chọn</th>
                                        <th>Khung Giờ</th>
                                        <th>Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($khung_gio as $time) {
                                        $status = $trangthai_data[$maban][$time] ?? 'Trống';
                                        $status_class = ($status == 'Có Khách') ? 'table-danger' : (($status == 'Đặt trước') ? 'table-warning' : 'table-success');

                                        if ($hasCustomer) {
                                            $disabled = ($status != 'Có Khách') ? 'disabled' : '';
                                        } else {
                                            $disabled = ($status != 'Trống') ? 'disabled' : '';
                                        }
                                        $checked = ($status == 'Có Khách') ? 'checked' : '';
                                    ?>
                                        <tr class="<?php echo $status_class; ?>">
                                            <td>
                                                <input type="radio" name="time" value="<?php echo $time; ?>" <?php echo $disabled; ?> <?php echo $checked; ?> required>
                                            </td>
                                            <td><?php echo $time; ?></td>
                                            <td><?php echo $status; ?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>

                            <button type="submit" class="btn btn-success">
                                <?php echo $hasCustomer ? 'Tiếp Tục' : 'Đặt Bàn'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    </div>
</div>
