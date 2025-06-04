<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) redirect('../index.php');

// Ambil ujian yang masih aktif
$now = date('Y-m-d H:i:s');
$stmt = $pdo->prepare("SELECT * FROM exams WHERE start_time <= ? AND end_time >= ?");
$stmt->execute([$now, $now]);
$exams = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar Ujian</title>
</head>
<body>
    <h1>Daftar Ujian Tersedia</h1>

    <?php if (count($exams) > 0): ?>
        <ul>
            <?php foreach ($exams as $exam): ?>
                <li>
                    <h3><?= $exam['title'] ?></h3>
                    <p><?= $exam['description'] ?></p>
                    <p>Waktu: <?= date('d M Y H:i', strtotime($exam['start_time'])) ?> - 
                    <?= date('d M Y H:i', strtotime($exam['end_time'])) ?></p>
                    <a href="kerjakan_ujian.php?exam_id=<?= $exam['id'] ?>">Kerjakan</a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>Tidak ada ujian yang tersedia saat ini.</p>
    <?php endif; ?>

    <a href="../dashboard.php">Kembali</a>
</body>
</html>
