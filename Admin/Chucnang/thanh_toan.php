<?php
require_once '../config/db.php'; // Kết nối CSDL

// Kiểm tra nếu nhân viên đã đăng nhập
if (!isset($_SESSION['manv'])) {
    echo "<script>alert('Bạn cần đăng nhập!'); window.location.href = '../login.php';</script>";
    exit();
}

// Nhận dữ liệu từ URL
$mahd = $_GET['mahd'] ?? null;

if (!$mahd) {
    echo "<script>alert('Không tìm thấy hóa đơn!'); window.location.href = 'index.php?page_layout=Ban';</script>";
    exit();
}

// Tính tổng tiền từ chi tiết hóa đơn
$sql_tongtien = "SELECT SUM(CHITIETHOADON.SOLUONG * MON.DONGIA) AS TONGTIEN 
                 FROM CHITIETHOADON 
                 JOIN MON ON CHITIETHOADON.MAMON = MON.MAMON 
                 WHERE CHITIETHOADON.MAHD = '$mahd'";
$query_tongtien = mysqli_query($conn, $sql_tongtien);
$row_tongtien = mysqli_fetch_assoc($query_tongtien);
$tongtien = $row_tongtien['TONGTIEN'] ?? 0;

// Cập nhật tổng tiền và trạng thái hóa đơn
$sql_update = "UPDATE HOADON SET TONGTIEN = '$tongtien', HD_TRANGTHAI = 'Đã thanh toán' WHERE MAHD = '$mahd'";
if (mysqli_query($conn, $sql_update)) {
    echo "<script>
    var newWindow = window.open('hoadon.php?mahd=$mahd', '_blank');
    if (newWindow) {
        setTimeout(() => {
            window.location.href = 'index.php?page_layout=Ban';
        }, 2000); // Đợi 2 giây rồi chuyển trang
    } else {
        alert('Trình duyệt đã chặn popup. Hãy bật popup để in hóa đơn!');
    }
</script>";

    exit();
} else {
    echo "<script>alert('Lỗi thanh toán!'); window.history.back();</script>";
    exit();
}
?>
