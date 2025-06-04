<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) redirect('../index.php');

$user_id = $_SESSION['user_id'];
$exam_id = $_GET['exam_id'] ?? 0;

// Ambil detail ujian
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$exam_id]);
$exam = $stmt->fetch();

if (!$exam) redirect('daftar_ujian.php');

// Cek apakah ujian masih aktif
$now = date('Y-m-d H:i:s');
if ($now < $exam['start_time'] || $now > $exam['end_time']) {
    die("Ujian tidak tersedia pada saat ini.");
}

// Ambil soal ujian
$stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($questions as $question) {
        $answer_key = 'question_' . $question['id'];

        if (isset($_POST[$answer_key])) {
            $answer_text = $_POST[$answer_key];

            // Cek apakah sudah ada jawaban
            $stmt = $pdo->prepare("SELECT id FROM answers WHERE user_id = ? AND question_id = ?");
            $stmt->execute([$user_id, $question['id']]);
            $existing = $stmt->fetch();

            if ($existing) {
                // Update jawaban
                $stmt = $pdo->prepare("UPDATE answers SET answer_text = ? WHERE id = ?");
                $stmt->execute([$answer_text, $existing['id']]);
            } else {
                // Insert jawaban baru
                $stmt = $pdo->prepare("INSERT INTO answers (user_id, question_id, answer_text) VALUES (?, ?, ?)");
                $stmt->execute([$user_id, $question['id'], $answer_text]);
            }

            // Jika pilihan ganda, hitung nilai otomatis
            if ($question['type'] === 'pilihan_ganda') {
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) AS is_correct
                    FROM options
                    WHERE id = ? AND question_id = ? AND is_correct = 1
                ");
                $stmt->execute([$answer_text, $question['id']]);
                $is_correct = $stmt->fetch()['is_correct'];
                $score = $is_correct ? $question['point'] : 0;
                $stmt = $pdo->prepare("UPDATE answers SET score = ? WHERE user_id = ? AND question_id = ?");
                $stmt->execute([$score, $user_id, $question['id']]);
            }
        }
    }
    $success = "Jawaban berhasil disimpan!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kerjakan Ujian</title>
</head>
<body>
    <h1><?= $exam['title'] ?></h1>
    <p><?= $exam['description'] ?></p>
    <?php if (isset($success)): ?>
        <p style="color:green;"><?= $success ?></p>
    <?php endif; ?>
    <form method="POST">
        <?php foreach ($questions as $index => $question): ?>
            <div>
                <h3>Soal <?= $index+1 ?> (<?= $question['point'] ?> poin)</h3>
                <p><?= $question['question_text'] ?></p>
                <?php if ($question['type'] === 'pilihan_ganda'): ?>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM options WHERE question_id = ?");
                    $stmt->execute([$question['id']]);
                    $options = $stmt->fetchAll();
                    ?>
                    <ul>
                        <?php foreach ($options as $option): ?>
                            <li>
                                <input type="radio" 
                                       name="question_<?= $question['id'] ?>" 
                                       value="<?= $option['id'] ?>" 
                                       required>
                                <?= $option['option_text'] ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <textarea name="question_<?= $question['id'] ?>" rows="4" cols="50" required></textarea>
                <?php endif; ?>
            </div>
            <hr>
        <?php endforeach; ?>
        <button type="submit">Simpan Jawaban</button>
    </form>
    <a href="daftar_ujian.php">Kembali</a>
</body>
</html>
