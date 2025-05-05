<?php
require_once '../../config/db.php'; // Kết nối CSDL

if (isset($_GET['month'])) {
    $month = $_GET['month']; // YYYY-MM

    // Xóa tất cả lịch đặt trong tháng
    $sql = "DELETE FROM NGAY WHERE date LIKE '$month-%'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Đã xóa toàn bộ lịch trong tháng $month!'); window.location.href='../index.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa!'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Thiếu dữ liệu!'); window.history.back();</script>";
}
?>
