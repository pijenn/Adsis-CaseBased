<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    redirect('../index.php');
}

$exam_id = $_GET['exam_id'] ?? 0;

// Ambil detail ujian
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) {
    die("Ujian tidak ditemukan");
}

// Ambil soal untuk ujian ini
$stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Soal Ujian: <?= htmlspecialchars($exam['title']) ?></title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Soal Ujian: <?= htmlspecialchars($exam['title']) ?></h1>
    <a href="tambah_soal.php?exam_id=<?= $exam_id ?>">Tambah Soal Baru</a>
    
    <?php if (count($questions) > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Pertanyaan</th>
                <th>Tipe</th>
                <th>Poin</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($questions as $question): ?>
            <tr>
                <td><?= $question['id'] ?></td>
                <td><?= htmlspecialchars(substr($question['question_text'], 0, 50)) ?>...</td>
                <td><?= $question['type'] === 'pilihan_ganda' ? 'Pilihan Ganda' : 'Essai' ?></td>
                <td><?= $question['point'] ?></td>
                <td>
                    <a href="edit_soal.php?id=<?= $question['id'] ?>">Edit</a> | 
                    <a href="hapus_soal.php?id=<?= $question['id'] ?>" onclick="return confirm('Apakah Anda yakin?')">Hapus</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Belum ada soal untuk ujian ini.</p>
    <?php endif; ?>
    
    <a href="lihat_ujian.php">Kembali ke Daftar Ujian</a>
</body>
</html>
