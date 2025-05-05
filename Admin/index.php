<?php
require_once '../config/db.php';
session_start(); // Bắt đầu session

// Kiểm tra nếu nhân viên đã đăng nhập
if (!isset($_SESSION['hoten']) || empty($_SESSION['hoten'])) {
    header("Location: ../index.php");
    exit();
}

$hoten = $_SESSION['hoten'];
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà Hàng</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <header class="bg-secondary text-white py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <a href="index.php?page_layout=Ban" class="text-white text-decoration-none">Quản Lý Món Ăn</a>
                </h2>

                <!-- Dropdown User -->
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown"
                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Xin chào, <strong><?php echo $hoten; ?></strong>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="logout.php">Đăng xuất</a>
                    </div>
                </div>
            </div>

            <nav class="nav justify-content-center mt-2">
                <!-- Món ăn (dropdown) -->
                <div class="dropdown mx-2">
                    <a class="btn btn-primary " href="#" role="button" id="monDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Món ăn
                    </a>
                    <div class="dropdown-menu" aria-labelledby="monDropdown">
                        <a class="dropdown-item" href="index.php?page_layout=Chucnang">Danh sách món ăn</a>
                        <a class="dropdown-item" href="index.php?page_layout=Them">Thêm món ăn</a>
                    </div>
                </div>


                <a class="btn btn-primary mx-2" href="index.php?page_layout=Ban">Danh sách bàn</a>

                
                <div class="dropdown mx-2">
                    <a class="btn btn-primary " href="#" role="button" id="lichDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Lịch đặt
                    </a>
                    <div class="dropdown-menu" aria-labelledby="lichDropdown">
                        <a class="dropdown-item" href="index.php?page_layout=Lich">Lịch đặt</a>
                        <a class="dropdown-item" href="index.php?page_layout=Themlich">Thêm lịch</a>
                        <a class="dropdown-item" href="index.php?page_layout=Dsdat">DS Đặt</a>
                    </div>
                </div>

                <a class="btn btn-primary mx-2" href="index.php?page_layout=TTBAN">Danh sách bàn</a>

                <!-- Doanh thu -->
                <a class="btn btn-primary mx-2" href="index.php?page_layout=Doanhthu">Doanh thu</a>
            </nav>

            <hr>
        </div>
    </header>

    <main class="container mt-4">
        <?php
        if (isset($_GET['page_layout'])) {
            switch ($_GET['page_layout']) {
                case 'Chucnang':
                    require_once 'Chucnang/ql_monan.php';
                    break;
                case 'Them':
                    require_once 'Chucnang/them_mon_an.php';
                    break;
                case 'Sua':
                    require_once 'Chucnang/sua_mon_an.php';
                    break;
                case 'Xoa':
                    require_once 'Chucnang/xoa_mon_an.php';
                    break;
                case 'Ban':
                    require_once 'Chucnang/ds_ban.php';
                    break;
                case 'QR':
                    require_once 'Chucnang/qr.php';
                    break;
                case 'Goi':
                    require_once 'Chucnang/goi_mon.php';
                    break;
                case 'Datban':
                    require_once 'Chucnang/dat_ban.php';
                    break;
                case 'Dattruoc':
                    require_once 'Chucnang/dat_truoc.php';
                    break;
                case 'Dsdat':
                    require_once 'Chucnang/ds_dat.php';
                    break;
                case 'Thongtin':
                    require_once 'Chucnang/thongtin_ban.php';
                    break;
                case 'Lich':
                    require_once 'Chucnang/lich.php';
                    break;
                case 'Thanhtoan':
                    require_once 'Chucnang/thanh_toan.php';
                    break;
                case 'Themlich':
                    require_once 'Chucnang/them_ngay_gio.php';
                    break;
                case 'Doanhthu':
                    require_once 'Chucnang/doanh_thu.php';
                    break;
                case 'TTBAN':
                    require_once 'Chucnang/ban.php';
                    break;
                default:
                    require_once 'Chucnang/ql_monan.php';
                    break;
            }
        } else {
            require_once 'Chucnang/ql_monan.php';
        }
        ?>
    </main>

    <!-- Thêm Bootstrap JS và jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>