<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) redirect('../index.php');

// Ambil semua ujian
$stmt = $pdo->query("SELECT * FROM exams");
$exams = $stmt->fetchAll();

$selected_exam = null;
$results = [];

if (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id'];
    
    // Ambil detail ujian
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->execute([$exam_id]);
    $selected_exam = $stmt->fetch();
    
    // Ambil hasil ujian
    $stmt = $pdo->prepare("
        SELECT u.username, 
               SUM(a.score) AS total_score,
               (SELECT SUM(point) FROM questions WHERE exam_id = ?) AS max_score
        FROM answers a
        JOIN users u ON a.user_id = u.id
        WHERE a.question_id IN (SELECT id FROM questions WHERE exam_id = ?)
        GROUP BY a.user_id
    ");
    $stmt->execute([$exam_id, $exam_id]);
    $results = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lihat Nilai</title>
</head>
<body>
    <h1>Lihat Nilai Ujian</h1>
    
    <form method="GET">
        <select name="exam_id" required>
            <option value="">Pilih Ujian</option>
            <?php foreach ($exams as $exam): ?>
                <option value="<?= $exam['id'] ?>" <?= isset($_GET['exam_id']) && $_GET['exam_id'] == $exam['id'] ? 'selected' : '' ?>>
                    <?= $exam['title'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Tampilkan</button>
    </form>
    
    <?php if ($selected_exam): ?>
        <h2>Hasil Ujian: <?= $selected_exam['title'] ?></h2>
        <table border="1">
            <tr>
                <th>Username</th>
                <th>Total Nilai</th>
                <th>Nilai Maksimal</th>
                <th>Persentase</th>
            </tr>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?= $result['username'] ?></td>
                    <td><?= $result['total_score'] ?></td>
                    <td><?= $result['max_score'] ?></td>
                    <td><?= round(($result['total_score'] / $result['max_score']) * 100, 2) ?>%</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
    
    <a href="../dashboard.php">Kembali</a>
</body>
</html>
