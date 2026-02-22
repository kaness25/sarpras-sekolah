<?php
include 'config.php';
$id = $_GET['id'];
mysqli_query($conn, "DELETE FROM alat WHERE id='$id'");
header("Location: dashboard_admin.php");
?>