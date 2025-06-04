<?php
session_start();
require_once 'common/db.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Selamat datang, <?= $_SESSION['username'] ?></h1>
    <p>Role: <?= ucfirst($user_role) ?></p>
    
    <?php if (is_admin()): ?>
        <h2>Menu Admin</h2>
        <ul>
            <li><a href="admin/tambah_ujian.php">Tambah Ujian</a></li>
            <li><a href="admin/tambah_soal.php">Tambah Soal</a></li>
            <li><a href="admin/lihat_ujian.php">Lihat Ujian</a></li>
            <li><a href="admin/lihat_nilai.php">Lihat Nilai</a></li>
            <li><a href="admin/daftar_jawaban_essai.php">Nilai Jawaban Esai</a></li>
        </ul>
    <?php else: ?>
        <h2>Menu Peserta</h2>
        <ul>
            <li><a href="user/daftar_ujian.php">Daftar Ujian</a></li>
            <li><a href="user/hasil_ujian.php">Hasil Ujian</a></li>
        </ul>
    <?php endif; ?>
    
    <a href="logout.php">Logout</a>
</body>
</html>
