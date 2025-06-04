<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    redirect('../index.php');
}

// Ambil semua jawaban esai yang belum dinilai
$stmt = $pdo->prepare("
    SELECT a.id, a.answer_text, u.username, q.question_text, q.point, e.title AS exam_title
    FROM answers a
    JOIN users u ON a.user_id = u.id
    JOIN questions q ON a.question_id = q.id
    JOIN exams e ON q.exam_id = e.id
    WHERE q.type = 'essai' AND a.score IS NULL
");
$stmt->execute();
$answers = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Jawaban Esai Belum Dinilai</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Jawaban Esai Belum Dinilai</h1>
    
    <?php if (count($answers) > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Ujian</th>
                <th>Peserta</th>
                <th>Soal</th>
                <th>Jawaban</th>
                <th>Poin Maks</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($answers as $answer): ?>
            <tr>
                <td><?= $answer['id'] ?></td>
                <td><?= htmlspecialchars($answer['exam_title']) ?></td>
                <td><?= htmlspecialchars($answer['username']) ?></td>
                <td><?= htmlspecialchars(substr($answer['question_text'], 0, 50)) ?>...</td>
                <td><?= htmlspecialchars(substr($answer['answer_text'], 0, 50)) ?>...</td>
                <td><?= $answer['point'] ?></td>
                <td><a href="nilai_essai.php?id=<?= $answer['id'] ?>">Beri Nilai</a></td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Tidak ada jawaban esai yang perlu dinilai.</p>
    <?php endif; ?>
    
    <a href="../dashboard.php">Kembali ke Dashboard</a>
</body>
</html>
