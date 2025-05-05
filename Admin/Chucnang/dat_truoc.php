<?php
require_once '../config/db.php'; // Kết nối CSDL

$manv = $_SESSION['manv'] ?? NULL; // Lấy mã nhân viên từ session

// Lấy danh sách bàn
$sql_ban = "SELECT MABAN, STT FROM BAN ORDER BY STT ASC";
$query_ban = mysqli_query($conn, $sql_ban);
$ds_ban = [];
while ($row = mysqli_fetch_assoc($query_ban)) {
    $ds_ban[$row['MABAN']] = $row['STT'];
}

// Lấy danh sách bàn đã đặt trước
$date_selected = $_GET['date'] ?? '';
$time_selected = $_GET['time'] ?? '';
$sql_ban_dat_truoc = "SELECT MABAN FROM TRANGTHAI WHERE DATE = ? AND TIME = ?";
$stmt = mysqli_prepare($conn, $sql_ban_dat_truoc);
mysqli_stmt_bind_param($stmt, 'ss', $date_selected, $time_selected);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$ban_dat_truoc = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ban_dat_truoc[] = $row['MABAN'];
}

// Xử lý đặt trước
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $maban = $_POST['maban'] ?? '';
    $tenkh = trim($_POST['tenkh'] ?? '');

    if ($date_selected && $time_selected && $maban && $tenkh && $manv) {
        // Kiểm tra khách hàng
        $sql_check_kh = "SELECT MAKH, SDT FROM KHACHHANG WHERE TENKH = ?";
        $stmt = mysqli_prepare($conn, $sql_check_kh);
        mysqli_stmt_bind_param($stmt, 's', $tenkh);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($row = mysqli_fetch_assoc($result)) {
            // Nếu khách hàng đã có, cập nhật số điện thoại
            $makh = $row['MAKH'];
            $existing_sdt = $row['SDT'];

            // Kiểm tra xem số điện thoại có thay đổi không
            if ($existing_sdt != $_POST['sdt']) {
                $sql_update_sdt = "UPDATE KHACHHANG SET SDT = ? WHERE MAKH = ?";
                $stmt = mysqli_prepare($conn, $sql_update_sdt);
                mysqli_stmt_bind_param($stmt, 'ss', $_POST['sdt'], $makh);
                mysqli_stmt_execute($stmt);
            }
        } else {
            // Tạo mã khách hàng mới và thêm khách hàng vào CSDL
            $sql_get_max_makh = "SELECT MAKH FROM KHACHHANG ORDER BY MAKH DESC LIMIT 1";
            $result = mysqli_query($conn, $sql_get_max_makh);

            if ($row = mysqli_fetch_assoc($result)) {
                $last_makh = (int)substr($row['MAKH'], 2);
                $new_makh = "KH" . str_pad($last_makh + 1, 3, "0", STR_PAD_LEFT);
            } else {
                $new_makh = "KH001";
            }

            // Thêm khách hàng mới
            $sql_insert_kh = "INSERT INTO KHACHHANG (MAKH, TENKH, SDT) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql_insert_kh);
            mysqli_stmt_bind_param($stmt, 'sss', $new_makh, $tenkh, $_POST['sdt']);
            mysqli_stmt_execute($stmt);
            $makh = $new_makh;
        }

        // Đặt trước bàn
        $sql_insert_tt = "INSERT INTO TRANGTHAI (DATE, TIME, MABAN, TRANGTHAI) VALUES (?, ?, ?, 'Đặt trước')";
        $stmt = mysqli_prepare($conn, $sql_insert_tt);
        mysqli_stmt_bind_param($stmt, 'sss', $date_selected, $time_selected, $maban);
        mysqli_stmt_execute($stmt);

        // Tạo mã hóa đơn mới dạng HD001, HD002...
        $sql_get_max_hd = "SELECT MAHD FROM HOADON ORDER BY MAHD DESC LIMIT 1";
        $result = mysqli_query($conn, $sql_get_max_hd);

        if ($row = mysqli_fetch_assoc($result)) {
            $last_mahd = (int)substr($row['MAHD'], 2);
            $new_mahd = "HD" . str_pad($last_mahd + 1, 3, "0", STR_PAD_LEFT);
        } else {
            $new_mahd = "HD001";
        }

        // Thêm hóa đơn mới
        $sql_insert_hd = "INSERT INTO HOADON (MAHD, MANV, MABAN, MAKH, TONGTIEN, HD_DATE, HD_TRANGTHAI) VALUES (?, ?, ?, ?, 0, ?, 'Chưa thanh toán')";
        $stmt = mysqli_prepare($conn, $sql_insert_hd);
        mysqli_stmt_bind_param($stmt, 'sssss', $new_mahd, $manv, $maban, $makh, $date_selected);
        mysqli_stmt_execute($stmt);

        echo "<script>alert('Đặt bàn thành công!'); window.location.href='index.php?page_layout=Lich';</script>";
    } else {
        echo "<script>alert('Vui lòng nhập đầy đủ thông tin!');</script>";
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center">Đặt Bàn Trước</h2>
    <p><strong>Ngày:</strong> <?php echo $date_selected; ?></p>
    <p><strong>Giờ:</strong> <?php echo $time_selected; ?></p>
    <form method="POST" action="">
        <div class="form-group">
            <label>Chọn Bàn:</label>
            <select name="maban" class="form-control" required>
                <?php foreach ($ds_ban as $maban => $stt) {
                    if (!in_array($maban, $ban_dat_truoc)) { ?>
                        <option value="<?php echo $maban; ?>">Bàn <?php echo $stt; ?></option>
                <?php }
                } ?>
            </select>
        </div>

        <div class="form-group">
            <label>Tên Khách Hàng:</label>
            <input type="text" name="tenkh" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Số điện thoại:</label>
            <input type="text" name="sdt" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Xác Nhận Đặt Trước</button>
    </form>
</div>