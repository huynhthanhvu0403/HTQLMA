<?php
require 'vendor/autoload.php';
require_once '../../config/db.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

$data = [];

if ($type === 'nam') {
    for ($year = $current_year; $year >= $current_year - 2; $year--) {
        $sql = "SELECT SUM(chitiethoadon.SOLUONG * mon.DONGIA) AS TONGTIEN, 
                       SUM(chitiethoadon.SOLUONG) AS TONGSL
                FROM hoadon
                JOIN chitiethoadon ON hoadon.MAHD = chitiethoadon.MAHD
                JOIN mon ON chitiethoadon.MAMON = mon.MAMON
                WHERE YEAR(hoadon.HD_DATE) = $year";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $data[] = ["Năm $year", '', (int)$row['TONGSL'], (int)$row['TONGTIEN']];
    }
} else {
    $where = "";
    if ($type === 'thang') {
        $where = "MONTH(hoadon.HD_DATE) = $current_month AND YEAR(hoadon.HD_DATE) = $current_year";
    } elseif ($type === 'quy') {
        $start_month = ($current_quarter - 1) * 3 + 1;
        $end_month = $start_month + 2;
        $where = "MONTH(hoadon.HD_DATE) BETWEEN $start_month AND $end_month AND YEAR(hoadon.HD_DATE) = $current_year";
    }

    $sql = "SELECT chitiethoadon.MAMON, 
                   SUM(chitiethoadon.SOLUONG * mon.DONGIA) AS TONGTIEN,
                   mon.DONGIA,
                   SUM(chitiethoadon.SOLUONG) AS TONGSL
            FROM hoadon
            JOIN chitiethoadon ON hoadon.MAHD = chitiethoadon.MAHD
            JOIN mon ON chitiethoadon.MAMON = mon.MAMON
            WHERE $where
            GROUP BY chitiethoadon.MAMON, mon.DONGIA";
    $result = mysqli_query($conn, $sql);

    $doanh_thu = array_fill_keys(array_keys($mon_list), ['TONGTIEN' => 0, 'DONGIA' => 0, 'TONGSL' => 0]);
    while ($row = mysqli_fetch_assoc($result)) {
        $doanh_thu[$row['MAMON']] = [
            'TONGTIEN' => (int)$row['TONGTIEN'],
            'DONGIA' => (int)$row['DONGIA'],
            'TONGSL' => (int)$row['TONGSL']
        ];
    }

    foreach ($mon_list as $mamon => $tenmon) {
        $data[] = [
            $tenmon,
            $doanh_thu[$mamon]['DONGIA'],
            $doanh_thu[$mamon]['TONGSL'],
            $doanh_thu[$mamon]['TONGTIEN']
        ];
    }
}

// Tạo file Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Tên món / Năm');
$sheet->setCellValue('B1', 'Đơn giá (VNĐ)');
$sheet->setCellValue('C1', 'Số lượng');
$sheet->setCellValue('D1', 'Tổng doanh thu (VNĐ)');

// Ghi dữ liệu từ dòng 2
$rowIndex = 2;
foreach ($data as $item) {
    $sheet->setCellValue("A$rowIndex", $item[0]);
    $sheet->setCellValue("B$rowIndex", $item[1]);
    $sheet->setCellValue("C$rowIndex", $item[2]);
    $sheet->setCellValue("D$rowIndex", $item[3]);
    $rowIndex++;
}

// Xuất file
$writer = new Xlsx($spreadsheet);
$filename = 'doanh_thu_' . $type . '_' . date('Ymd_His') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
$writer->save('php://output');
exit;