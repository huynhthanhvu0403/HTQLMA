<?php
require_once 'config/db.php';

$sql = "SELECT M.MAMON, M.TENMON, M.HINHANH, M.DONGIA, L.TENLOAI 
        FROM MON M 
        JOIN LOAI L ON M.MALOAI = L.MALOAI 
        ORDER BY L.MALOAI, M.TENMON";
$result = $conn->query($sql);

// Nhóm món ăn theo TENLOAI
$mon_theo_loai = [];
while ($row = $result->fetch_assoc()) {
    $mon_theo_loai[$row['TENLOAI']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Danh Sách Món</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .menu-title {
            font-size: 2.5rem;
            font-weight: bold;
            color: #343a40;
            margin-bottom: 2rem;
        }
        .category-title {
            font-size: 1.8rem;
            font-weight: bold;
            color: #007bff;
            margin-top: 2rem;
            margin-bottom: 1.5rem;
        }
        .card {
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #dee2e6;
        }
        .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 1.5rem;
        }
        .card-title {
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }
        .card-text {
            font-size: 1.1rem;
            color: #dc3545;
        }
        .menu-container {
            max-height: 600px;
            overflow-y: auto;
            overflow-x: hidden;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4 menu-title">MENU</h2>
    <div class="menu-container">
        <?php foreach ($mon_theo_loai as $tenloai => $mons) { ?>
            <h3 class="category-title"><?php echo $tenloai; ?></h3>
            <div class="row g-4">
                <?php foreach ($mons as $row) { ?>
                    <div class="col-md-4">
                        <div class="card shadow-sm">
                            <img src="Admin/img/<?php echo $row['HINHANH']; ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo $row['TENMON']; ?>" 
                                 onerror="this.src='Admin/img/placeholder.jpg';">
                            <div class="card-body text-center">
                                <h5 class="card-title text-dark fw-bold"><?php echo $row['TENMON']; ?></h5>
                                <p class="card-text text-danger fw-bold">
                                    Giá: <?php echo number_format($row['DONGIA'], 0, ',', '.'); ?> VND
                                </p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
$conn->close();
?>