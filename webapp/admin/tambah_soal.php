<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) redirect('../index.php');

// Ambil daftar ujian
$stmt = $pdo->query("SELECT id, title FROM exams");
$exams = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = $_POST['exam_id'];
    $question_text = $_POST['question_text'];
    $type = $_POST['type'];
    $point = $_POST['point'];
    
    $stmt = $pdo->prepare("INSERT INTO questions (exam_id, question_text, type, point) VALUES (?, ?, ?, ?)");
    $stmt->execute([$exam_id, $question_text, $type, $point]);
    $question_id = $pdo->lastInsertId();
    
    if ($type === 'pilihan_ganda') {
        foreach ($_POST['options'] as $index => $option_text) {
            $is_correct = ($index == $_POST['correct_answer']) ? 1 : 0;
            $stmt = $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
            $stmt->execute([$question_id, $option_text, $is_correct]);
        }
    }
    
    $success = "Soal berhasil ditambahkan!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Soal</title>
    <script>
        function toggleOptions() {
            const type = document.getElementById('type').value;
            document.getElementById('options-container').style.display = 
                (type === 'pilihan_ganda') ? 'block' : 'none';
        }
    </script>
</head>
<body onload="toggleOptions()">
    <h1>Tambah Soal</h1>
    <?php if (isset($success)): ?>
        <p style="color:green;"><?= $success ?></p>
    <?php endif; ?>
    <form method="POST">
        <select name="exam_id" required>
            <option value="">Pilih Ujian</option>
            <?php foreach ($exams as $exam): ?>
                <option value="<?= $exam['id'] ?>"><?= $exam['title'] ?></option>
            <?php endforeach; ?>
        </select><br>
        <textarea name="question_text" placeholder="Pertanyaan" required></textarea><br>
        <select id="type" name="type" onchange="toggleOptions()" required>
            <option value="pilihan_ganda">Pilihan Ganda</option>
            <option value="essai">Essai</option>
        </select><br>
        <input type="number" name="point" placeholder="Poin" required><br>
        
        <div id="options-container" style="display:none;">
            <h3>Opsi Jawaban</h3>
            <?php for ($i=1; $i<=4; $i++): ?>
                <input type="text" name="options[]" placeholder="Opsi <?= $i ?>"><br>
            <?php endfor; ?>
            <label>Jawaban Benar: </label>
            <select name="correct_answer">
                <option value="0">Opsi 1</option>
                <option value="1">Opsi 2</option>
                <option value="2">Opsi 3</option>
                <option value="3">Opsi 4</option>
            </select><br>
        </div>
        
        <button type="submit">Simpan</button>
    </form>
    <a href="../dashboard.php">Kembali</a>
</body>
</html>
