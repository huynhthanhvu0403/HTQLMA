<?php
    $id = $_GET['id'];
    $sql = "DELETE FROM mon where MAMON = '$id'";
    $query = mysqli_query($conn, $sql);
    header('location: index.php?page_layout=Danhsach');
?>