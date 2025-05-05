<?php
include 'phpqrcode/qrlib.php'; // Nhúng thư viện QR Code

// URL cần mã hóa
$url = "http://localhost/CT263/menu.php";

// Tên file ảnh QR
$file = 'qr_images/qrcode.png'; 


// Tạo ảnh QR và lưu vào file
QRcode::png($url, $file);

echo "Đã tạo ảnh QR: " . $file;
?>
