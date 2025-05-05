<?php

// Lấy danh sách loại món và buổi ăn từ CSDL
$sqlLoai = "SELECT MALOAI, TENLOAI FROM LOAI";
$queryLoai = mysqli_query($conn, $sqlLoai);

$sqlBuoi = "SELECT MABUOI, TENBUOI FROM BUOI";
$queryBuoi = mysqli_query($conn, $sqlBuoi);

if (isset($_POST['sbm'])) {
    // Tạo mã món tự động dạng Mxxxxx
    $queryLastID = mysqli_query($conn, "SELECT MAMON FROM MON ORDER BY MAMON DESC LIMIT 1");
    $lastID = mysqli_fetch_assoc($queryLastID);
    $nextID = (int)substr($lastID['MAMON'], 1) + 1;
    $MAMON = 'M' . str_pad($nextID, 4, '0', STR_PAD_LEFT);

    $Tenmon = $_POST['Tenmon'];
    $MALOAI = $_POST['MALOAI'];
    $MABUOI = $_POST['MABUOI'];
    $Dongia = $_POST['Dongia'];
    $Thanhphan = $_POST['Thanhphan'];

    // Xử lý hình ảnh
    $Hinhanh = $_FILES['Hinhanh']['name'];
    $Hinhanh_tmp = $_FILES['Hinhanh']['tmp_name'];
    $target_dir = "img/" . basename($Hinhanh);

    if (move_uploaded_file($Hinhanh_tmp, $target_dir)) {
        // Thêm dữ liệu vào bảng MON
        $sql = "INSERT INTO MON (MAMON, MALOAI, MABUOI, TENMON, HINHANH, DONGIA, THANHPHAN)
                VALUES ('$MAMON', '$MALOAI', '$MABUOI', '$Tenmon', '$Hinhanh', '$Dongia', '$Thanhphan')";
        $query = mysqli_query($conn, $sql);

        if ($query) {
            header('Location: index.php?page_layout=Danhsach');
            exit();
        } else {
            echo "Lỗi khi thêm dữ liệu.";
        }
    } else {
        echo "Lỗi khi tải ảnh.";
    }
}
?>

<div class="container-fluid">
    <div class="card mt-4">
        <div class="card-header bg-info text-white">
            <h2>Thêm Món Ăn</h2>
        </div>

        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="">Tên món</label>
                    <input type="text" name="Tenmon" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="">Loại món</label>
                    <select name="MALOAI" class="form-control" required>
                        <?php while ($row = mysqli_fetch_assoc($queryLoai)) { ?>
                            <option value="<?= $row['MALOAI'] ?>"><?= $row['TENLOAI'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="">Buổi ăn</label>
                    <select name="MABUOI" class="form-control" required>
                        <?php while ($row = mysqli_fetch_assoc($queryBuoi)) { ?>
                            <option value="<?= $row['MABUOI'] ?>"><?= $row['TENBUOI'] ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="">Hình ảnh</label>
                    <input type="file" name="Hinhanh" class="form-control" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label for="">Đơn giá</label>
                    <input type="number" step="0.01" name="Dongia" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="">Thành phần</label>
                    <input type="text" name="Thanhphan" class="form-control" required>
                </div>

                <button name="sbm" class="btn btn-success" type="submit">Thêm</button>
            </form>
        </div>
    </div>
</div>
