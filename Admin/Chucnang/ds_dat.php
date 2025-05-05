<?php
require_once '../config/db.php'; // Kết nối CSDL

$ngayHienTai = date("Y-m-d"); // Lấy ngày hiện tại

// Lấy danh sách đặt trước cùng thông tin khách hàng (Chỉ hiển thị hóa đơn chưa thanh toán)
$sql = "SELECT TT.MABAN, TT.DATE, TT.TIME, B.STT, KH.TENKH, KH.SDT, HD.MAHD
        FROM TRANGTHAI TT
        JOIN BAN B ON TT.MABAN = B.MABAN
        JOIN HOADON HD ON TT.MABAN = HD.MABAN AND TT.DATE = HD.HD_DATE
        JOIN KHACHHANG KH ON HD.MAKH = KH.MAKH
        WHERE TT.DATE >= '$ngayHienTai' 
              AND TT.TRANGTHAI = 'Đặt trước' 
              AND HD.HD_TRANGTHAI = 'Chưa thanh toán' 
        ORDER BY TT.DATE ASC, TT.TIME ASC";
$query = mysqli_query($conn, $sql);

if (isset($_POST['huy_ban'])) {
    $maban = $_POST['maban'];
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Xóa hóa đơn trước
    $sql_delete_hoadon = "DELETE FROM HOADON WHERE MABAN = '$maban' AND HD_DATE = '$date'";
    mysqli_query($conn, $sql_delete_hoadon);

    // Xóa đặt trước
    $sql_delete_trangthai = "DELETE FROM TRANGTHAI WHERE MABAN = '$maban' AND DATE = '$date' AND TIME = '$time'";
    if (mysqli_query($conn, $sql_delete_trangthai)) {
        echo "<script>alert('Hủy đặt trước thành công!'); window.location.href=window.location.href;</script>";
    } else {
        echo "<script>alert('Lỗi khi hủy đặt trước!');</script>";
    }
}

if (isset($_POST['dat_ban'])) {
    $maban = $_POST['maban'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $mahd = $_POST['mahd'];

    // Cập nhật trạng thái thành "Có khách"
    $sql_update = "UPDATE TRANGTHAI SET TRANGTHAI = 'Có Khách' WHERE MABAN = '$maban' AND DATE = '$date' AND TIME = '$time'";
    if (mysqli_query($conn, $sql_update)) {
        // Chuyển hướng đến trang Thongtin với dữ liệu đặt bàn
        header("Location: index.php?page_layout=Thongtin&maban=$maban&date=$date&time=$time&mahd=$mahd");
        exit();
    } else {
        echo "<script>alert('Lỗi khi đặt bàn!');</script>";
    }
}

?>

<div class="container mt-4">
    <h2 class="text-center">Danh Sách Đặt Trước</h2>
    <div style="max-height: 400px; overflow-y: auto; overflow-x: hidden;">
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Ngày</th>
                    <th>Khung Giờ</th>
                    <th>Bàn</th>
                    <th>Khách Hàng</th>
                    <th>Số điện thoại</th>
                    <th>Hành động</th>
                </tr>
            </thead>

            <tbody>
                <?php if (mysqli_num_rows($query) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                        <tr>
                            <td><?php echo $row['DATE']; ?></td>
                            <td><?php echo $row['TIME']; ?></td>
                            <td>Bàn <?php echo $row['STT']; ?></td>
                            <td><?php echo $row['TENKH']; ?></td>
                            <td><?php echo $row['SDT']; ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="maban" value="<?php echo $row['MABAN']; ?>">
                                    <input type="hidden" name="date" value="<?php echo $row['DATE']; ?>">
                                    <input type="hidden" name="time" value="<?php echo $row['TIME']; ?>">
                                    <input type="hidden" name="mahd" value="<?php echo $row['MAHD']; ?>">
                                    <button type="submit" name="dat_ban" class="btn btn-success"
                                        onclick="return confirm('Xác nhận đặt bàn này cho khách?');">Đặt bàn</button>
                                </form>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="maban" value="<?php echo $row['MABAN']; ?>">
                                    <input type="hidden" name="date" value="<?php echo $row['DATE']; ?>">
                                    <input type="hidden" name="time" value="<?php echo $row['TIME']; ?>">
                                    <button type="submit" name="huy_ban" class="btn btn-danger"
                                        onclick="return confirm('Bạn có chắc chắn muốn hủy đặt trước này?');">Hủy</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="5" class="text-center">Không có bàn nào được đặt trước!</td>
                    </tr>
                <?php } ?>
            </tbody>

        </table>
    </div>
</div>