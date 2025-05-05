<?php
require_once '../config/db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM MON WHERE MAMON = '$id'";
    $query = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($query);
}

if (isset($_POST['sbm'])) {
    $Tenmon = $_POST['Tenmon'];
    $Dongia = $_POST['Dongia'];
    $Thanhphan = $_POST['Thanhphan'];
    $Maloai = $_POST['Maloai'];
    $Mabuoi = $_POST['Mabuoi'];

    // Xử lý ảnh
    $Hinhanh = $_FILES['Hinhanh']['name'];
    $Hinhanh_tmp = $_FILES['Hinhanh']['tmp_name'];
    if ($Hinhanh != '') {
        move_uploaded_file($Hinhanh_tmp, 'img/' . $Hinhanh);
    } else {
        $Hinhanh = $row['HINHANH'];
    }

    // Cập nhật dữ liệu
    $sql = "UPDATE MON SET TENMON='$Tenmon', HINHANH='$Hinhanh', DONGIA='$Dongia', THANHPHAN='$Thanhphan', MALOAI='$Maloai', MABUOI='$Mabuoi' WHERE MAMON='$id'";
    $query = mysqli_query($conn, $sql);

    header('location: index.php?page_layout=Danhsach');
}
?>

<div class="container mt-4">
    <div class="card">
        <div class="card-header bg-info text-white">
            <h2>Sửa Món Ăn</h2>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="">Tên món</label>
                    <input type="text" name="Tenmon" class="form-control" value="<?php echo $row['TENMON']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="">Hình ảnh</label>
                    <input type="file" name="Hinhanh" class="form-control" accept="image/*">
                    <img src="img/<?php echo $row['HINHANH']; ?>" width="100" class="mt-2">
                </div>
                <div class="form-group">
                    <label for="">Đơn giá</label>
                    <input type="text" name="Dongia" class="form-control" value="<?php echo $row['DONGIA']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="">Thành phần</label>
                    <input type="text" name="Thanhphan" class="form-control" value="<?php echo $row['THANHPHAN']; ?>" required>
                </div>
                <div class="form-group">
                    <label for="">Loại món ăn</label>
                    <select name="Maloai" class="form-control" required>
                        <?php
                        $sql_loai = "SELECT * FROM LOAI";
                        $query_loai = mysqli_query($conn, $sql_loai);
                        while ($loai = mysqli_fetch_assoc($query_loai)) {
                            $selected = ($loai['MALOAI'] == $row['MALOAI']) ? 'selected' : '';
                            echo "<option value='{$loai['MALOAI']}' $selected>{$loai['TENLOAI']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="">Buổi</label>
                    <select name="Mabuoi" class="form-control" required>
                        <?php
                        $sql_buoi = "SELECT * FROM BUOI";
                        $query_buoi = mysqli_query($conn, $sql_buoi);
                        while ($buoi = mysqli_fetch_assoc($query_buoi)) {
                            $selected = ($buoi['MABUOI'] == $row['MABUOI']) ? 'selected' : '';
                            echo "<option value='{$buoi['MABUOI']}' $selected>{$buoi['TENBUOI']}</option>";
                        }
                        ?>
                    </select>
                </div>
                <button name="sbm" class="btn btn-success" type="submit">Cập Nhật</button>
            </form>
        </div>
    </div>
</div>
