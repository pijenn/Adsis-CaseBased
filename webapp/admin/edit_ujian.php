<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    redirect('../index.php');
}

$id = $_GET['id'] ?? 0;

// Ambil data ujian
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$id]);
$exam = $stmt->fetch();

if (!$exam) {
    die("Ujian tidak ditemukan");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    $stmt = $pdo->prepare("UPDATE exams SET title=?, description=?, start_time=?, end_time=? WHERE id=?");
    $stmt->execute([$title, $description, $start_time, $end_time, $id]);
    
    $_SESSION['success'] = "Ujian berhasil diperbarui!";
    header('Location: lihat_ujian.php');
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Ujian</title>
</head>
<body>
    <h1>Edit Ujian: <?= htmlspecialchars($exam['title']) ?></h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color:green;"><?= $_SESSION['success'] ?></p>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <form method="POST">
        <label>Judul Ujian:</label>
        <input type="text" name="title" value="<?= htmlspecialchars($exam['title']) ?>" required><br>
        
        <label>Deskripsi:</label>
        <textarea name="description" required><?= htmlspecialchars($exam['description']) ?></textarea><br>
        
        <label>Waktu Mulai:</label>
        <input type="datetime-local" name="start_time" 
               value="<?= date('Y-m-d\TH:i', strtotime($exam['start_time'])) ?>" required><br>
        
        <label>Waktu Selesai:</label>
        <input type="datetime-local" name="end_time" 
               value="<?= date('Y-m-d\TH:i', strtotime($exam['end_time'])) ?>" required><br>
        
        <button type="submit">Simpan Perubahan</button>
    </form>
    
    <a href="lihat_ujian.php">Kembali ke Daftar Ujian</a>
</body>
</html>
