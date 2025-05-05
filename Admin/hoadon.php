<?php
session_start();
require_once '../config/db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['manv'])) {
    echo "<script>alert('Bạn cần đăng nhập!'); window.location.href = '../login.php';</script>";
    exit();
}

// Nhận dữ liệu
$manv = $_SESSION['manv'];
$mahd = $_GET['mahd'] ?? null;

if (!$mahd) {
    echo "<script>alert('Không tìm thấy hóa đơn!'); window.close();</script>";
    exit();
}

// Truy vấn tên nhân viên
$sql_nv = "SELECT HOTEN FROM NHANVIEN WHERE MANV = '$manv'";
$query_nv = mysqli_query($conn, $sql_nv);
$nhanvien = mysqli_fetch_assoc($query_nv);
$tennv = $nhanvien['HOTEN'] ?? 'Không xác định';

// Truy vấn thông tin hóa đơn
$sql_hd = "SELECT * FROM HOADON WHERE MAHD = '$mahd'";
$query_hd = mysqli_query($conn, $sql_hd);
$hoadon = mysqli_fetch_assoc($query_hd);

// Lấy thông tin khách hàng
$sql_kh = "SELECT TENKH FROM KHACHHANG WHERE MAKH = '{$hoadon['MAKH']}'";
$query_kh = mysqli_query($conn, $sql_kh);
$khachhang = mysqli_fetch_assoc($query_kh);

// Lấy danh sách món ăn
$sql_monan = "SELECT MON.TENMON, CHITIETHOADON.SOLUONG, MON.DONGIA 
              FROM CHITIETHOADON 
              JOIN MON ON CHITIETHOADON.MAMON = MON.MAMON 
              WHERE CHITIETHOADON.MAHD = '$mahd'";
$query_monan = mysqli_query($conn, $sql_monan);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hóa Đơn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        window.onload = function() {
            window.print(); // Tự động in hóa đơn khi trang được mở
            setTimeout(() => { window.close(); }, 1000); // Đóng trang sau khi in
        };
    </script>
</head>
<body>
    <div class="container my-4">
        <div class="card shadow-lg">
            <div class="card-body">
                <h2 class="text-center text-primary">HÓA ĐƠN THANH TOÁN</h2>
                <div class="row mb-3">
                    <div class="col-md-6"><strong>Nhân viên:</strong> <?php echo $tennv; ?></div>
                    <div class="col-md-6"><strong>Khách hàng:</strong> <?php echo $khachhang['TENKH'] ?? 'Không xác định'; ?></div>
                </div>
                <div class="mb-3"><strong>Ngày lập:</strong> <?php echo date('d/m/Y', strtotime($hoadon['HD_DATE'])); ?></div>

                <table class="table table-bordered text-center">
                    <thead class="table-dark">
                        <tr>
                            <th>Món Ăn</th>
                            <th>Số Lượng</th>
                            <th>Đơn Giá</th>
                            <th>Thành Tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $tongTien = 0; while ($row = mysqli_fetch_assoc($query_monan)) {
                            $thanhtien = $row['SOLUONG'] * $row['DONGIA'];
                            $tongTien += $thanhtien;
                        ?>
                        <tr>
                            <td><?php echo $row['TENMON']; ?></td>
                            <td><?php echo $row['SOLUONG']; ?></td>
                            <td><?php echo number_format($row['DONGIA'], 0, ',', '.'); ?> VNĐ</td>
                            <td><?php echo number_format($thanhtien, 0, ',', '.'); ?> VNĐ</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Tổng Tiền:</th>
                            <th><?php echo number_format($tongTien, 0, ',', '.'); ?> VNĐ</th>
                        </tr>
                    </tfoot>
                </table>

                <p class="text-center mt-3 text-success">Cảm ơn quý khách đã sử dụng dịch vụ!</p>
            </div>
        </div>
    </div>
</body>
</html>
