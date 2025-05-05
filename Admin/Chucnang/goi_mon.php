<?php
require_once '../config/db.php'; // Kết nối CSDL

// Kiểm tra nếu nhân viên đã đăng nhập
if (!isset($_SESSION['manv'])) {
    echo "<script>alert('Bạn cần đăng nhập!'); window.location.href = '../login.php';</script>";
    exit();
}

$manv = $_SESSION['manv'];
$maban = $_POST['MABAN'] ?? null;
$date = $_POST['DATE'] ?? null;
$time = $_POST['TIME'] ?? null;
$tenkh = $_POST['TENKH'] ?? null;

// Lấy hóa đơn tương ứng với bàn
$sql_get_mahd = "SELECT MAHD FROM HOADON WHERE MABAN = '$maban' AND HD_DATE = '$date' AND HD_TRANGTHAI = 'Chưa thanh toán'";
$query_get_mahd = mysqli_query($conn, $sql_get_mahd);
$row_hoadon = mysqli_fetch_assoc($query_get_mahd);
$mahd = $row_hoadon['MAHD'] ?? null;

if (!$mahd) {
    echo "<script>alert('Không tìm thấy hóa đơn cho bàn này!'); window.history.back();</script>";
    exit();
}

// Lấy danh sách loại món và buổi ăn
$loai_query = mysqli_query($conn, "SELECT * FROM loai");
$buoi_query = mysqli_query($conn, "SELECT * FROM buoi");

// Lấy giá trị lọc
$filter_loai = isset($_POST['loai']) ? $_POST['loai'] : '';
$filter_buoi = isset($_POST['buoi']) ? $_POST['buoi'] : '';

// Truy vấn món ăn với bộ lọc
$where = "WHERE 1";
if ($filter_loai != '') {
    $where .= " AND mon.MALOAI = '$filter_loai'";
}
if ($filter_buoi != '') {
    $where .= " AND mon.MABUOI = '$filter_buoi'";
}

$sql_mon = "SELECT MAMON, TENMON, DONGIA FROM MON $where";
$query_mon = mysqli_query($conn, $sql_mon);

// Lưu lại các món và số lượng đã chọn vào session
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Nếu có thay đổi số lượng, lưu lại vào session
    if (isset($_POST['MAMON']) && isset($_POST['SOLUONG'])) {
        $mamons = $_POST['MAMON'] ?? [];
        $soluongs = $_POST['SOLUONG'] ?? [];
        foreach ($mamons as $index => $mamon) {
            $_SESSION['selected_items'][$mamon] = (int) $soluongs[$index];
        }
    }
}

// Xử lý khi gọi món
if (isset($_POST['goi_mon'])) {
    foreach ($_SESSION['selected_items'] as $mamon => $soluong) {
        if ($soluong > 0) {
            $sql_insert = "INSERT INTO CHITIETHOADON (MAMON, MAHD, SOLUONG) VALUES ('$mamon', '$mahd', '$soluong')
                            ON DUPLICATE KEY UPDATE SOLUONG = SOLUONG + VALUES(SOLUONG)";
            mysqli_query($conn, $sql_insert);
        }
    }
    unset($_SESSION['selected_items']);
    echo "<script>alert('Gọi món thành công!'); window.location.href = 'index.php?page_layout=Thongtin&maban=$maban&date=$date&time=$time&mahd=$mahd';</script>";
    exit();
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h5>Gọi Món Cho Bàn <?php echo $maban; ?></h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="MABAN" value="<?php echo $maban; ?>">
                <input type="hidden" name="DATE" value="<?php echo $date; ?>">
                <input type="hidden" name="TIME" value="<?php echo $time; ?>">
                <input type="hidden" name="MAHD" value="<?php echo $mahd; ?>">

                <!-- Bộ lọc Loại Món (Cơm, Nước) -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Loại món:</label>
                        <select name="loai" class="form-control" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            <?php
                            mysqli_data_seek($loai_query, 0);
                            while ($row = mysqli_fetch_assoc($loai_query)) { ?>
                                <option value="<?php echo $row['MALOAI']; ?>" <?php if ($row['MALOAI'] == $filter_loai) echo 'selected'; ?>>
                                    <?php echo $row['TENLOAI']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Buổi ăn:</label>
                        <select name="buoi" class="form-control" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            <?php
                            mysqli_data_seek($buoi_query, 0);
                            while ($row = mysqli_fetch_assoc($buoi_query)) { ?>
                                <option value="<?php echo $row['MABUOI']; ?>" <?php if ($row['MABUOI'] == $filter_buoi) echo 'selected'; ?>>
                                    <?php echo $row['TENBUOI']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <!-- Bảng danh sách món -->
                <div style="max-height: 350px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Món Ăn</th>
                                <th>Đơn Giá</th>
                                <th>Số Lượng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($query_mon)) { ?>
                                <tr>
                                    <td><?php echo $row['TENMON']; ?></td>
                                    <td><?php echo number_format($row['DONGIA']); ?> VND</td>
                                    <td>
                                        <input type="hidden" name="MAMON[]" value="<?php echo $row['MAMON']; ?>">
                                        <input type="number" name="SOLUONG[]" class="form-control" min="0"
                                            value="<?php echo isset($_SESSION['selected_items'][$row['MAMON']]) ? $_SESSION['selected_items'][$row['MAMON']] : 0; ?>">
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <button type="submit" name="goi_mon" class="btn btn-success">Xác Nhận Gọi Món</button>
            </form>
        </div>
    </div>
</div>