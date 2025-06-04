<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) redirect('../index.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    $stmt = $pdo->prepare("INSERT INTO exams (title, description, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $description, $start_time, $end_time]);
    $success = "Ujian berhasil ditambahkan!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Ujian</title>
</head>
<body>
    <h1>Tambah Ujian Baru</h1>
    <?php if (isset($success)): ?>
        <p style="color:green;"><?= $success ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="title" placeholder="Judul Ujian" required><br>
        <textarea name="description" placeholder="Deskripsi Ujian" required></textarea><br>
        <label>Waktu Mulai:</label>
        <input type="datetime-local" name="start_time" required><br>
        <label>Waktu Selesai:</label>
        <input type="datetime-local" name="end_time" required><br>
        <button type="submit">Simpan</button>
    </form>
    <a href="../dashboard.php">Kembali</a>
</body>
</html>
