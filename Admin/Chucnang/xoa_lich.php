<?php
require_once '../../config/db.php';


if (isset($_GET['date'])) {
    $date = $_GET['date'];

    // Xóa dữ liệu theo ngày
    $sql = "DELETE FROM NGAY WHERE date = '$date'";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('Xóa thành công!'); window.location.href='../index.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa!'); window.history.back();</script>";
    }
} else {
    echo "<script>alert('Thiếu dữ liệu!'); window.history.back();</script>";
}
?>
