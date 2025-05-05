<?php
require_once '../config/db.php';

$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// Lấy danh sách khung giờ theo ngày
$sql_ngay = "
    SELECT DISTINCT TIME 
    FROM NGAY 
    WHERE DATE = '$selected_date'
    ORDER BY TIME ASC
";
$query_ngay = mysqli_query($conn, $sql_ngay);

$khung_gio = [];
while ($row = mysqli_fetch_assoc($query_ngay)) {
    $khung_gio[] = $row['TIME'];
}

// Lấy bàn
$sql_ban = "SELECT * FROM BAN ORDER BY STT ASC";
$query_ban = mysqli_query($conn, $sql_ban);

// Lấy trạng thái theo ngày được chọn và chỉ lấy trạng thái có khách
$sql_trangthai = "
    SELECT MABAN, TRANGTHAI, TIME
    FROM TRANGTHAI
    WHERE DATE = '$selected_date' AND TRANGTHAI = 'Có Khách'
    ORDER BY TIME ASC
";
$query_trangthai = mysqli_query($conn, $sql_trangthai);

$trangthai_data = [];
while ($row = mysqli_fetch_assoc($query_trangthai)) {
    $trangthai_data[$row['MABAN']][$row['TIME']] = $row['TRANGTHAI'];
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-center mb-3">
        <h2 class="text-center mb-0">Danh Sách Bàn</h2>
    </div>

    <form method="GET">
        <input type="hidden" name="page_layout" value="TTBAN">
        <div class="row mb-4 justify-content-center">
            <div class="col-md-4">
                <label for="date">Chọn ngày:</label>
                <input type="date" id="date" name="date" class="form-control" 
                       value="<?php echo $selected_date; ?>" onchange="this.form.submit()">
            </div>
        </div>
    </form>

    <div style="max-height: 500px; overflow-y: auto; overflow-x: hidden;">
    <div class="row">
        <?php while ($row_ban = mysqli_fetch_assoc($query_ban)) {
            $maban = $row_ban['MABAN'] ?? 'Không xác định';
            $id_table = "khunggio_" . $maban;
        ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5>Bàn <?php echo $row_ban['STT']; ?></h5>
                    </div>
                    <div class="card-body">
                        <button class="btn btn-primary btn-sm mb-2" onclick="toggleRows('<?php echo $id_table; ?>', this)">
                            Xem khung giờ
                        </button>

                        <!-- Bảng khung giờ -->
                        <div id="<?php echo $id_table; ?>" class="d-none">
                            <table class="table time-table">
                                <thead>
                                    <tr>
                                        <th>Khung Giờ</th>
                                        <th>Trạng Thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($khung_gio as $time) {
                                        $status = $trangthai_data[$maban][$time] ?? 'Trống';
                                        $status_class = ($status == 'Có Khách') ? 'table-danger' : (($status == 'Đặt trước') ? 'table-warning' : 'table-success');
                                        echo "<tr class='{$status_class}'>
                                                <td>{$time}</td>
                                                <td>{$status}</td>
                                              </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
    </div>
</div>

<script>
function toggleRows(divId, btn) {
    const content = document.getElementById(divId);
    const isHidden = content.classList.contains('d-none');
    content.classList.toggle('d-none');
    btn.textContent = isHidden ? 'Ẩn' : 'Xem khung giờ';
}
</script>
