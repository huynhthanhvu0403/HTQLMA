<?php
require_once '../config/db.php'; // Kết nối CSDL

// Kiểm tra nếu nhân viên đã đăng nhập
session_start();
if (!isset($_SESSION['manv'])) {
    echo "<script>alert('Bạn cần đăng nhập!'); window.location.href = '../login.php';</script>";
    exit();
}

$manv = $_SESSION['manv'];

// Xử lý khi khách hàng đặt bàn
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $maban = $_POST['maban'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Kiểm tra xem có giá trị time không
    if (empty($time)) {
        echo "<script>alert('Vui lòng chọn một khung giờ!'); window.history.back();</script>";
        exit();
    }

    // Kiểm tra xem bàn đã có khách vào khung giờ đó chưa
    $sql_check = "SELECT * FROM TRANGTHAI WHERE MABAN = '$maban' AND DATE = '$date' AND TIME = '$time'";
    $query_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($query_check) > 0) {
        // Lấy mã hóa đơn của bàn đó nếu đã tồn tại
        $sql_get_mahd = "SELECT MAHD FROM HOADON WHERE MABAN = '$maban' AND HD_DATE = '$date' LIMIT 1";
        $query_get_mahd = mysqli_query($conn, $sql_get_mahd);
        $row_mahd = mysqli_fetch_assoc($query_get_mahd);
        $mahd = $row_mahd['MAHD'] ?? '';

        // Chuyển hướng đến trang thông tin bàn với cả mahd
        header("Location: index.php?page_layout=Thongtin&maban=$maban&date=$date&time=$time&mahd=$mahd");
        exit();
    }

    // Nếu chưa tồn tại, cập nhật trạng thái bàn
    $sql_update = "INSERT INTO TRANGTHAI (MABAN, TRANGTHAI, DATE, TIME) 
                   VALUES ('$maban', 'Có Khách', '$date', '$time')";
    if (!mysqli_query($conn, $sql_update)) {
        echo "<script>alert('Lỗi khi cập nhật trạng thái bàn: " . mysqli_error($conn) . "'); window.history.back();</script>";
        exit();
    }

    // Lấy mã hóa đơn mới nhất và tạo mã mới
    $sql_get_mahd = "SELECT MAHD FROM HOADON ORDER BY MAHD DESC LIMIT 1";
    $query_get_mahd = mysqli_query($conn, $sql_get_mahd);
    $row = mysqli_fetch_assoc($query_get_mahd);

    if ($row) {
        // Tăng số thứ tự lên (VD: HD001 -> HD002)
        $number = (int)substr($row['MAHD'], 2) + 1;
        $mahd = 'HD' . str_pad($number, 3, '0', STR_PAD_LEFT);
    } else {
        // Nếu chưa có hóa đơn nào, bắt đầu từ HD001
        $mahd = 'HD001';
    }

    // Thực hiện chèn hóa đơn mới
    $sql_hoadon = "INSERT INTO HOADON (MAHD, MANV, MABAN, MAKH, TONGTIEN, HD_DATE, HD_TRANGTHAI) 
                   VALUES ('$mahd', '$manv', '$maban', 'KH001', 0, '$date', 'Chưa thanh toán')";
    if (!mysqli_query($conn, $sql_hoadon)) {
        echo "<script>alert('Lỗi khi tạo hóa đơn: " . mysqli_error($conn) . "'); window.history.back();</script>";
        exit();
    }

    // Chuyển hướng đến trang thông tin bàn
    header("Location: index.php?page_layout=Thongtin&maban=$maban&date=$date&time=$time&mahd=$mahd");
    exit();
}
?>
