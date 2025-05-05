<?php
require_once '../config/db.php'; // Kết nối CSDL

// Hàm kiểm tra tháng có trong database chưa
function daCoThangTrongCSDL($conn, $thang) {
    $sqlCheck = "SELECT COUNT(*) AS count FROM NGAY WHERE LEFT(date, 7) = '$thang'";
    $result = mysqli_query($conn, $sqlCheck);
    $row = mysqli_fetch_assoc($result);
    return $row['count'] > 0;
}

// Tìm tháng đầu tiên chưa có dữ liệu
$thangThem = date("Y-m"); // Bắt đầu từ tháng hiện tại
while (daCoThangTrongCSDL($conn, $thangThem)) {
    $thangThem = date("Y-m", strtotime("$thangThem +1 month")); // Chuyển sang tháng kế tiếp
}

// Lấy số ngày của tháng cần thêm
$soNgayThang = date("t", strtotime("$thangThem-01"));
$khungGio = ["07:00", "09:00", "11:00", "13:00", "15:00", "17:00", "19:00", "21:00"]; // Khung giờ cố định

// Xử lý khi nhấn nút "Thêm Ngày Giờ"
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["ngay"])) {
        foreach ($_POST["ngay"] as $ngay) {
            if (!empty($_POST["gio"][$ngay])) {
                foreach ($_POST["gio"][$ngay] as $gio) {
                    $gio = $gio . ":00"; // Thêm giây ":00"
                    $sql = "INSERT INTO NGAY (date, time) VALUES ('$ngay', '$gio')";
                    mysqli_query($conn, $sql);
                }
            }
        }
        echo "<p style='color:green;'>Đã thêm ngày và giờ vào CSDL!</p>";
    } else {
        echo "<p style='color:red;'>Bạn chưa chọn ngày nào!</p>";
    }
}
?>

<!-- Form chọn ngày & giờ -->
<div class="container mt-4">
    <form action="" method="POST">
        <h3 class="text-center">Chọn ngày và khung giờ cho tháng <?php echo date("m/Y", strtotime("$thangThem-01")); ?></h3>
        <div style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
        <?php
        for ($ngay = 1; $ngay <= $soNgayThang; $ngay++) {
            $ngayFull = "$thangThem-" . str_pad($ngay, 2, "0", STR_PAD_LEFT); // YYYY-MM-DD
            ?>
            <div class="form-group">
                <input type="checkbox" id="ngay<?php echo $ngay; ?>" name="ngay[<?php echo $ngayFull; ?>]" value="<?php echo $ngayFull; ?>" checked 
                    onclick="toggleGio('<?php echo $ngay; ?>')">
                <label for="ngay<?php echo $ngay; ?>"><b>Ngày <?php echo $ngay; ?></b></label>
                <br>
                <?php foreach ($khungGio as $gio) { ?>
                    <input type="checkbox" class="gio<?php echo $ngay; ?>" name="gio[<?php echo $ngayFull; ?>][]" value="<?php echo $gio; ?>" checked>
                    <label><?php echo $gio; ?></label>
                <?php } ?>
            </div>
            <hr>
            <?php
        }
        ?>
        </div>
        <button type="submit" class="btn btn-primary">Thêm Ngày Giờ</button>
    </form>
</div>

<script>
function toggleGio(ngay) {
    let isChecked = document.getElementById('ngay' + ngay).checked;
    let checkboxes = document.querySelectorAll('.gio' + ngay);
    checkboxes.forEach(cb => cb.disabled = !isChecked);
}
</script>