<?php
require_once '../config/db.php';

// Lấy danh sách loại món và buổi ăn
$loai_query = mysqli_query($conn, "SELECT * FROM loai");
$buoi_query = mysqli_query($conn, "SELECT * FROM buoi");

// Lấy giá trị lọc
$filter_loai = isset($_GET['loai']) ? $_GET['loai'] : '';
$filter_buoi = isset($_GET['buoi']) ? $_GET['buoi'] : '';

// Lấy giá trị tìm kiếm
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Phân trang
$limit = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Truy vấn lọc
$where = "WHERE 1";
if ($filter_loai != '') {
    $where .= " AND mon.MALOAI = '$filter_loai'";
}
if ($filter_buoi != '') {
    $where .= " AND mon.MABUOI = '$filter_buoi'";
}
if ($search != '') {
    $escaped_search = mysqli_real_escape_string($conn, $search);
    $where .= " AND mon.TENMON LIKE '%$escaped_search%'";
}

// Đếm tổng số kết quả
$count_sql = "SELECT COUNT(*) AS total 
              FROM mon 
              LEFT JOIN loai ON mon.MALOAI = loai.MALOAI 
              LEFT JOIN buoi ON mon.MABUOI = buoi.MABUOI 
              $where";
$total_rows = mysqli_fetch_assoc(mysqli_query($conn, $count_sql))['total'];
$total_pages = ceil($total_rows / $limit);

// Truy vấn dữ liệu thực tế
$sql = "SELECT mon.*, loai.TENLOAI, buoi.TENBUOI 
        FROM mon 
        LEFT JOIN loai ON mon.MALOAI = loai.MALOAI 
        LEFT JOIN buoi ON mon.MABUOI = buoi.MABUOI 
        $where 
        LIMIT $limit OFFSET $offset";
$query = mysqli_query($conn, $sql);

// Lấy danh sách món không bán trong tuần này
$start_of_week = date('Y-m-d', strtotime('monday this week')); // Ngày đầu tuần
$end_of_week = date('Y-m-d', strtotime('sunday this week')); // Ngày cuối tuần
$mon_khong_ban = [];
$sql_khong_ban = "
    SELECT mon.MAMON 
    FROM mon
    LEFT JOIN chitiethoadon ON mon.MAMON = chitiethoadon.MAMON
    LEFT JOIN hoadon ON chitiethoadon.MAHD = hoadon.MAHD 
        AND hoadon.HD_DATE BETWEEN '$start_of_week' AND '$end_of_week'
    WHERE hoadon.MAHD IS NULL
";

$result = mysqli_query($conn, $sql_khong_ban);
while ($row = mysqli_fetch_assoc($result)) {
    $mon_khong_ban[] = $row['MAMON'];
}

?>

<div class="container mt-4">
    <h2 class="text-center">DANH SÁCH MÓN ĂN</h2>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="row align-items-end mb-3">
        <div class="col-md-3">
            <form method="GET">
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
                <input type="hidden" name="buoi" value="<?php echo $filter_buoi; ?>">
            </form>
        </div>

        <div class="col-md-3">
            <form method="GET">
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
                <input type="hidden" name="loai" value="<?php echo $filter_loai; ?>">
            </form>
        </div>

        <div class="col-md-4">
            <form method="GET" class="d-flex flex-column">
                <label>Tìm món ăn:</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Nhập tên món..." value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="loai" value="<?php echo $filter_loai; ?>">
                    <input type="hidden" name="buoi" value="<?php echo $filter_buoi; ?>">
                    <button class="btn btn-primary" type="submit">Tìm</button>
                </div>
            </form>
        </div>

        <div class="col-md-2">
            <a href="index.php" class="btn btn-secondary mt-3 w-100">Reset</a>
        </div>
    </div>

    <!-- Bảng danh sách món -->
    <table class="table table-bordered">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Tên Món</th>
                <th>Loại Món</th>
                <th>Buổi Ăn</th>
                <th>Hình ảnh</th>
                <th>Đơn giá</th>
                <th>Thành phần</th>
                <th>Sửa</th>
                <th>Xóa</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = $offset + 1;
            while ($row = mysqli_fetch_assoc($query)) { 
                // Lấy doanh thu và số lượng của món trong tuần
                $sql_doanhthu_soluong_mon = "
                    SELECT SUM(chitiethoadon.SOLUONG * mon.DONGIA) AS doanhthu_mon, 
                           SUM(chitiethoadon.SOLUONG) AS soluong_mon
                    FROM mon
                    LEFT JOIN chitiethoadon ON mon.MAMON = chitiethoadon.MAMON
                    LEFT JOIN hoadon ON chitiethoadon.MAHD = hoadon.MAHD
                    WHERE hoadon.HD_DATE BETWEEN '$start_of_week' AND '$end_of_week'
                    AND mon.MAMON = '$row[MAMON]'
                ";
                $doanhthu_soluong_result = mysqli_query($conn, $sql_doanhthu_soluong_mon);
                $doanhthu_soluong_row = mysqli_fetch_assoc($doanhthu_soluong_result);
                $doanhthu_mon = $doanhthu_soluong_row['doanhthu_mon'] ?? 0;
                $soluong_mon = $doanhthu_soluong_row['soluong_mon'] ?? 0;

                // Lấy tổng doanh thu của tất cả món ăn trong tuần
                $sql_doanhthu_tong = "
                    SELECT SUM(chitiethoadon.SOLUONG * mon.DONGIA) AS doanhthu_tong
                    FROM mon
                    LEFT JOIN chitiethoadon ON mon.MAMON = chitiethoadon.MAMON
                    LEFT JOIN hoadon ON chitiethoadon.MAHD = hoadon.MAHD
                    WHERE hoadon.HD_DATE BETWEEN '$start_of_week' AND '$end_of_week'
                ";
                $doanhthu_tong_result = mysqli_query($conn, $sql_doanhthu_tong);
                $doanhthu_tong_row = mysqli_fetch_assoc($doanhthu_tong_result);
                $doanhthu_tong = $doanhthu_tong_row['doanhthu_tong'] ?? 0;

                // Tính phần trăm doanh thu nếu tổng doanh thu không bằng 0
                $phantram_doanhthu = 0;
                if ($doanhthu_tong > 0) {
                    $phantram_doanhthu = ($doanhthu_mon / $doanhthu_tong) * 100;
                }
            ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td>
                        <?php echo $row['TENMON']; ?>
                        <?php if ($doanhthu_mon == 0): ?>
                            <br><small class="text-danger">* Không bán được trong tuần</small>
                        <?php else: ?>
                            <br><small class="text-success"><?php echo number_format($phantram_doanhthu, 2) . '% (' . number_format($soluong_mon, 0, ',', '.') . ')'; ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo $row['TENLOAI'] ?? 'Không xác định'; ?></td>
                    <td><?php echo $row['TENBUOI'] ?? 'Không xác định'; ?></td>
                    <td>
                        <img src="img/<?php echo $row['HINHANH']; ?>" style="width: 100px; height: auto;">
                    </td>
                    <td><?php echo number_format($row['DONGIA'], 0, ',', '.') . ' VNĐ'; ?></td>
                    <td><?php echo $row['THANHPHAN']; ?></td>
                    <td><a href="index.php?page_layout=Sua&id=<?php echo $row['MAMON']; ?>" class="btn btn-warning">Sửa</a></td>
                    <td><a onclick="return Del('<?php echo $row['TENMON']; ?>')" href="index.php?page_layout=Xoa&id=<?php echo $row['MAMON']; ?>" class="btn btn-danger">Xóa</a></td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

    <!-- Phân trang -->
    <?php if ($total_pages > 1): ?>
    <nav>
        <ul class="pagination justify-content-center">
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                <li class="page-item <?php if ($p == $page) echo 'active'; ?>">
                    <a class="page-link" href="?loai=<?php echo $filter_loai; ?>&buoi=<?php echo $filter_buoi; ?>&search=<?php echo urlencode($search); ?>&page=<?php echo $p; ?>">
                        <?php echo $p; ?>
                    </a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>

<script>
    function Del(name) {
        return confirm("Bạn có chắc muốn xóa món ăn: " + name + "?");
    }
</script>