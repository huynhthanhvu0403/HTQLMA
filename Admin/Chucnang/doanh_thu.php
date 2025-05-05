<?php
require_once '../config/db.php';

// Xử lý tham số GET
$type = isset($_GET['type']) ? $_GET['type'] : 'thang';
$current_year = date('Y');
$current_month = date('n');
$current_quarter = ceil($current_month / 3);

// Lấy danh sách món
$mon_query = mysqli_query($conn, "SELECT MAMON, TENMON FROM mon");
$mon_list = [];
while ($row = mysqli_fetch_assoc($mon_query)) {
    $mon_list[$row['MAMON']] = $row['TENMON'];
}

$labels = [];
$data = [];
$total_revenue = 0;

if ($type === 'nam') {
    for ($year = $current_year; $year >= $current_year - 2; $year--) {
        $sql = "SELECT SUM(chitiethoadon.SOLUONG * mon.DONGIA) AS TONGTIEN
                FROM hoadon
                JOIN chitiethoadon ON hoadon.MAHD = chitiethoadon.MAHD
                JOIN mon ON chitiethoadon.MAMON = mon.MAMON
                WHERE YEAR(hoadon.HD_DATE) = $year";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $tong = (int)$row['TONGTIEN'];
        $labels[] = "Năm $year";
        $data[] = $tong;
        $total_revenue += $tong;
    }
} else {
    $doanh_thu = array_fill_keys(array_keys($mon_list), 0);

    if ($type === 'thang') {
        $where = "MONTH(hoadon.HD_DATE) = $current_month AND YEAR(hoadon.HD_DATE) = $current_year";
    } elseif ($type === 'quy') {
        $start_month = ($current_quarter - 1) * 3 + 1;
        $end_month = $start_month + 2;
        $where = "MONTH(hoadon.HD_DATE) BETWEEN $start_month AND $end_month AND YEAR(hoadon.HD_DATE) = $current_year";
    }

    $sql = "SELECT chitiethoadon.MAMON, SUM(chitiethoadon.SOLUONG * mon.DONGIA) AS TONGTIEN
            FROM hoadon
            JOIN chitiethoadon ON hoadon.MAHD = chitiethoadon.MAHD
            JOIN mon ON chitiethoadon.MAMON = mon.MAMON
            WHERE $where
            GROUP BY chitiethoadon.MAMON";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($result)) {
        $doanh_thu[$row['MAMON']] = (int)$row['TONGTIEN'];
        $total_revenue += $row['TONGTIEN'];
    }

    $labels = array_values($mon_list);
    $data = array_values($doanh_thu);
}
?>

<div class="container mt-4">
    <h3 class="text-center">Thống kê doanh thu theo <?php echo ucfirst($type); ?></h3>
    <div class="text-center mb-3">
    <a href="Chucnang/excel.php?type=<?php echo $type; ?>" class="btn btn-success">Xuất Excel</a>
</div>

    <form method="GET" action="index.php" class="row g-3 justify-content-center mb-4">
        <input type="hidden" name="page_layout" value="Doanhthu">
        <div class="col-md-3">
            <label>Loại thời gian</label>
            <select name="type" id="type-select" class="form-control" onchange="this.form.submit()">
                <option value="thang" <?php if($type=='thang') echo 'selected'; ?>>Tháng hiện tại</option>
                <option value="quy" <?php if($type=='quy') echo 'selected'; ?>>Quý hiện tại</option>
                <option value="nam" <?php if($type=='nam') echo 'selected'; ?>>3 năm gần nhất</option>
            </select>
        </div>
    </form>

    <?php if ($type !== 'nam'): ?>
    <div class="text-center mb-3">
        <strong>Tổng doanh thu: </strong><?php echo number_format($total_revenue, 0, ',', '.') . ' VNĐ'; ?>
    </div>
    <?php endif; ?>

    <canvas id="revenueChart"></canvas>

    <div class="row mt-5">
        <div class="col-md-6">
            <h5 class="text-center">Xu hướng doanh thu</h5>
            <canvas id="lineChart"></canvas>
        </div>
        <div class="col-md-6">
            <h5 class="text-center">Tỷ lệ doanh thu</h5>
            <canvas id="pieChart"></canvas>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-12">
            <h5 class="text-center">So sánh</h5>
            <canvas id="horizontalChart"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = <?php echo json_encode($labels); ?>;
    const data = <?php echo json_encode($data); ?>;

    new Chart(document.getElementById('revenueChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('lineChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: data,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    new Chart(document.getElementById('pieChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Tỷ lệ doanh thu',
                data: data,
                backgroundColor: labels.map((_, i) => `hsl(${i * 30}, 70%, 60%)`)
            }]
        },
        options: {
            responsive: true
        }
    });

    new Chart(document.getElementById('horizontalChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: data,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
</script>
