<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    redirect('../index.php');
}

$answer_id = $_GET['id'] ?? 0;

// Ambil detail jawaban
$stmt = $pdo->prepare("
    SELECT a.id, a.answer_text, a.score, u.username, q.question_text, q.point, e.title AS exam_title
    FROM answers a
    JOIN users u ON a.user_id = u.id
    JOIN questions q ON a.question_id = q.id
    JOIN exams e ON q.exam_id = e.id
    WHERE a.id = ?
");
$stmt->execute([$answer_id]);
$answer = $stmt->fetch();

if (!$answer) {
    die("Jawaban tidak ditemukan");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = $_POST['score'];
    
    // Validasi: nilai tidak boleh melebihi poin soal
    if ($score > $answer['point']) {
        $error = "Nilai tidak boleh melebihi poin soal (maks: {$answer['point']})";
    } else {
        $stmt = $pdo->prepare("UPDATE answers SET score = ? WHERE id = ?");
        $stmt->execute([$score, $answer_id]);
        
        $_SESSION['success'] = "Nilai berhasil disimpan!";
        header('Location: daftar_jawaban_essai.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Beri Nilai Esai</title>
</head>
<body>
    <h1>Beri Nilai Esai</h1>
    
    <?php if (isset($error)): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>
    
    <h2>Ujian: <?= htmlspecialchars($answer['exam_title']) ?></h2>
    <h3>Peserta: <?= htmlspecialchars($answer['username']) ?></h3>
    <h4>Soal:</h4>
    <p><?= nl2br(htmlspecialchars($answer['question_text'])) ?></p>
    <h4>Jawaban:</h4>
    <p><?= nl2br(htmlspecialchars($answer['answer_text'])) ?></p>
    
    <form method="POST">
        <label>Nilai (maks: <?= $answer['point'] ?>):</label>
        <input type="number" name="score" min="0" max="<?= $answer['point'] ?>" required>
        <button type="submit">Simpan Nilai</button>
    </form>
    
    <a href="daftar_jawaban_essai.php">Kembali</a>
</body>
</html>
