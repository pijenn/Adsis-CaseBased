<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) redirect('../index.php');

$user_id = $_SESSION['user_id'];

// Ambil ujian yang pernah diikuti
$stmt = $pdo->prepare("
    SELECT e.id, e.title
    FROM exams e
    JOIN questions q ON e.id = q.exam_id
    JOIN answers a ON q.id = a.question_id
    WHERE a.user_id = ?
    GROUP BY e.id
");
$stmt->execute([$user_id]);
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
        SELECT q.question_text, q.point, a.answer_text, a.score
        FROM answers a
        JOIN questions q ON a.question_id = q.id
        WHERE a.user_id = ? AND q.exam_id = ?
    ");
    $stmt->execute([$user_id, $exam_id]);
    $results = $stmt->fetchAll();

    // Hitung total nilai
    $total_score = array_sum(array_column($results, 'score'));
    $max_score = array_sum(array_column($results, 'point'));
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hasil Ujian</title>
</head>
<body>
    <h1>Hasil Ujian</h1>

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
        <p>Total Nilai: <?= round(($total_score/$max_score)*100,2) ?> </p>

        <h3>Detail Jawaban:</h3>
        <table border="1">
            <tr>
                <th>Soal</th>
                <th>Jawaban Anda</th>
                <th>Nilai</th>
                <th>Poin Maks</th>
            </tr>
            <?php foreach ($results as $result): ?>
                <tr>
                    <td><?= $result['question_text'] ?></td>
                    <td><?= $result['answer_text'] ?></td>
                    <td><?= $result['score'] ?? 'Belum dinilai' ?></td>
                    <td><?= $result['point'] ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <a href="../dashboard.php">Kembali</a>
</body>
</html>
