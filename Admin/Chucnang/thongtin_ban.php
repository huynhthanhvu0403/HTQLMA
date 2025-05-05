<?php
require_once '../config/db.php'; // Kết nối CSDL

// Kiểm tra nếu nhân viên đã đăng nhập
if (!isset($_SESSION['manv'])) {
    echo "<script>alert('Bạn cần đăng nhập!'); window.location.href = '../login.php';</script>";
    exit();
}

$manv = $_SESSION['manv'];

// Lấy thông tin từ URL
$maban = $_GET['maban'] ?? null;
$date = $_GET['date'] ?? null;
$time = $_GET['time'] ?? null;
$mahd = $_GET['mahd'] ?? null;

// Xử lý xóa món ăn
if (isset($_POST['delete_monan'])) {
    $mamon = $_POST['mamon'];
    $sql_delete = "DELETE FROM CHITIETHOADON WHERE MAHD = '$mahd' AND MAMON = '$mamon'";
    if (mysqli_query($conn, $sql_delete)) {
        echo "<script>alert('Đã xóa món ăn!'); window.location.href = 'index.php?page_layout=Thongtin&maban=$maban&date=$date&time=$time&mahd=$mahd';</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa món ăn!'); window.history.back();</script>";
    }
    exit();
}

// Lấy thông tin bàn
$sql_ban = "SELECT STT FROM BAN WHERE MABAN = '$maban'";
$query_ban = mysqli_query($conn, $sql_ban);
$row_ban = mysqli_fetch_assoc($query_ban);
$stt_ban = $row_ban['STT'] ?? 'Không xác định';

// Lấy thông tin khách hàng
$tenkh = "Không xác định";
$sql_khachhang = "SELECT KH.TENKH FROM HOADON HD JOIN KHACHHANG KH ON HD.MAKH = KH.MAKH WHERE HD.MAHD = '$mahd' LIMIT 1";
$query_khachhang = mysqli_query($conn, $sql_khachhang);
if ($row_khachhang = mysqli_fetch_assoc($query_khachhang)) {
    $tenkh = $row_khachhang['TENKH'];
}

// Kiểm tra trạng thái bàn
$sql_check = "SELECT * FROM TRANGTHAI WHERE MABAN = '$maban' AND DATE = '$date' AND TIME = '$time'";
$query_check = mysqli_query($conn, $sql_check);
$hasCustomer = mysqli_num_rows($query_check) > 0;

// Cập nhật trạng thái bàn nếu chưa có khách
if (!$hasCustomer) {
    $sql_update = "INSERT INTO TRANGTHAI (MABAN, TRANGTHAI, DATE, TIME) VALUES ('$maban', 'Có Khách', '$date', '$time')";
    mysqli_query($conn, $sql_update);
}

// Xử lý hủy bàn
if (isset($_POST['cancel'])) {
    // Kiểm tra xem hóa đơn có món ăn trong CHITIETHOADON không
    $sql_check_monan = "SELECT * FROM CHITIETHOADON WHERE MAHD = '$mahd'";
    $query_check_monan = mysqli_query($conn, $sql_check_monan);
    
    if (mysqli_num_rows($query_check_monan) > 0) {
        // Nếu có món, không thể hủy bàn
        echo "<script>alert('Không thể hủy bàn vì đã gọi món!'); window.location.href = 'index.php?page_layout=Ban';</script>";
    } else {
        // Nếu không có món, xóa hóa đơn và trạng thái bàn
        mysqli_query($conn, "DELETE FROM HOADON WHERE MAHD = '$mahd'");
        mysqli_query($conn, "DELETE FROM TRANGTHAI WHERE MABAN = '$maban' AND DATE = '$date' AND TIME = '$time'");
        echo "<script>alert('Đã hủy bàn!'); window.location.href = 'index.php?page_layout=Ban';</script>";
    }
    exit();
}

// Xử lý khi bấm Thanh Toán
if (isset($_POST['thanhtoan'])) {
    $mahd = $_POST['mahd'];
    $maban = $_POST['maban'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Cập nhật trạng thái bàn thành "Trống"
    $sql_update = "DELETE FROM TRANGTHAI WHERE MABAN = '$maban' AND DATE = '$date' AND TIME = '$time'";
    if (mysqli_query($conn, $sql_update)) {
        // Chuyển đến trang Thanh Toán mà không có thông báo
        header("Location: index.php?page_layout=Thanhtoan&mahd=$mahd");
        exit();
    } else {
        echo "<script>window.history.back();</script>";
    }
}

// Lấy danh sách món ăn từ CHITIETHOADON
$sql_monan = "SELECT MON.TENMON, MON.MAMON, CHITIETHOADON.SOLUONG, MON.DONGIA FROM CHITIETHOADON JOIN MON ON CHITIETHOADON.MAMON = MON.MAMON WHERE CHITIETHOADON.MAHD = '$mahd'";
$query_monan = mysqli_query($conn, $sql_monan);
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5>Thông Tin Bàn</h5>
        </div>
        <div class="card-body">
            <p><strong>Bàn số:</strong> <?php echo $stt_ban; ?></p>
            <p><strong>Khung giờ:</strong> <?php echo $time; ?></p>
            <p><strong>Khách hàng:</strong> <?php echo $tenkh; ?></p>

            <form action="index.php?page_layout=Goi" method="POST">
                <input type="hidden" name="MABAN" value="<?php echo $maban; ?>">
                <input type="hidden" name="DATE" value="<?php echo $date; ?>">
                <input type="hidden" name="TIME" value="<?php echo $time; ?>">
                <button type="submit" class="btn btn-success">Gọi Món</button>
            </form>

            <form method="POST" class="mt-2">
                <button type="submit" name="cancel" class="btn btn-danger">Hủy</button>
            </form>

            <!-- Nút Thanh Toán -->
            <form method="POST" class="mt-2">
                <input type="hidden" name="mahd" value="<?php echo $mahd; ?>">
                <input type="hidden" name="maban" value="<?php echo $maban; ?>">
                <input type="hidden" name="date" value="<?php echo $date; ?>">
                <input type="hidden" name="time" value="<?php echo $time; ?>">
                <button type="submit" name="thanhtoan" class="btn btn-primary">Thanh Toán</button>
            </form>

        </div>
    </div>

    <div class="card mt-3">
    <div class="card-header bg-warning text-white">
        <h5>Danh Sách Món Ăn</h5>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($query_monan) > 0) { ?>
            <!-- Container with only vertical scrollbar -->
            <div style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">
                <table class="table table-bordered">
                    <!-- Tổng Tiền row at the top -->
                    <tbody>
                        <tr>
                            <th colspan="4" class="text-right">Tổng Tiền:</th>
                            <th>
                                <?php
                                $tongTien = 0;
                                mysqli_data_seek($query_monan, 0); // Reset pointer for calculation
                                while ($row = mysqli_fetch_assoc($query_monan)) {
                                    $thanhtien = $row['SOLUONG'] * $row['DONGIA'];
                                    $tongTien += $thanhtien;
                                }
                                echo number_format($tongTien, 0, ',', '.'); ?> VNĐ
                            </th>
                        </tr>
                    </tbody>

                    <!-- Column headers -->
                    <thead class="thead-dark">
                        <tr>
                            <th>Món Ăn</th>
                            <th>Số Lượng</th>
                            <th>Đơn Giá</th>
                            <th>Thành Tiền</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>

                    <!-- Dish list -->
                    <tbody>
                        <?php
                        mysqli_data_seek($query_monan, 0); // Reset pointer for dish list
                        while ($row_monan = mysqli_fetch_assoc($query_monan)) {
                            $thanhtien = $row_monan['SOLUONG'] * $row_monan['DONGIA'];
                        ?>
                            <tr>
                                <td><?php echo $row_monan['TENMON']; ?></td>
                                <td><?php echo $row_monan['SOLUONG']; ?></td>
                                <td><?php echo number_format($row_monan['DONGIA'], 0, ',', '.'); ?> VNĐ</td>
                                <td><?php echo number_format($thanhtien, 0, ',', '.'); ?> VNĐ</td>
                                <td>
                                    <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa món này?');">
                                        <input type="hidden" name="mamon" value="<?php echo $row_monan['MAMON']; ?>">
                                        <button type="submit" name="delete_monan" class="btn btn-danger btn-sm">Xóa</button>
                                    </form>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } else { ?>
            <p class="text-center">Chưa có món ăn nào được gọi.</p>
        <?php } ?>
    </div>
</div>