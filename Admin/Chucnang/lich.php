<?php
require_once '../config/db.php'; // Kết nối CSDL

$ngayHienTai = date("Y-m-d"); // Lấy ngày hiện tại

$sql_ngay = "
    SELECT DATE, TIME 
    FROM NGAY 
    WHERE (DATE > CURDATE()) 
       OR (DATE = CURDATE() AND (TIME > CURTIME() OR TIME = (SELECT MAX(TIME) FROM NGAY WHERE DATE = CURDATE() AND TIME <= CURTIME())))
    ORDER BY DATE ASC, TIME ASC
";
$query_ngay = mysqli_query($conn, $sql_ngay);

// Lưu danh sách khung giờ theo ngày
$ds_ngay = [];
while ($row = mysqli_fetch_assoc($query_ngay)) {
    $ds_ngay[$row['DATE']][] = $row['TIME'];
}


?>

<div class="container mt-4">
    <h2 class="text-center">Danh Sách Ngày & Khung Giờ</h2>
    <div style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Ngày</th>
                    <th>Khung Giờ</th>
                </tr>
            </thead>

            <tbody>
                <?php if (!empty($ds_ngay)) { ?>
                    <?php foreach ($ds_ngay as $ngay => $ds_time) { ?>
                        <tr>
                            <td><?php echo $ngay; ?></td>
                            <td>
                                <div class="btn-group">
                                    <?php foreach ($ds_time as $time) { ?>
                                        <a href="index.php?page_layout=Dattruoc&time=<?php echo $time; ?>&date=<?php echo $ngay; ?>" class="btn btn-primary">
                                            <?php echo $time; ?>
                                        </a>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="2" class="text-center">Không có dữ liệu phù hợp!</td>
                    </tr>
                <?php } ?>
            </tbody>

        </table>
    </div>
</div>