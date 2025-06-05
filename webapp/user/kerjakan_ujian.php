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

// Cek apakah user sudah mengikuti ujian ini
$stmt = $pdo->prepare("
    SELECT COUNT(*) as answer_count 
    FROM answers a
    JOIN questions q ON a.question_id = q.id
    WHERE a.user_id = ? AND q.exam_id = ?
");
$stmt->execute([$user_id, $exam_id]);
$answer_count = $stmt->fetch()['answer_count'];

if ($answer_count > 0) {
    // Jika sudah ada jawaban, redirect dengan pesan
    $_SESSION['error'] = "Anda sudah mengikuti ujian ini sebelumnya. Anda hanya diperbolehkan mengikuti ujian satu kali.";
    header('Location: daftar_ujian.php');
    exit;
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

            // Insert jawaban baru (tidak perlu cek existing karena sudah dicek di atas)
            $stmt = $pdo->prepare("INSERT INTO answers (user_id, question_id, answer_text) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $question['id'], $answer_text]);

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
    // Setelah jawaban disimpan, redirect ke daftar ujian untuk mencegah resubmission
    $_SESSION['success'] = $success;
    header('Location: daftar_ujian.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerjakan Ujian: <?= htmlspecialchars($exam['title']) ?> - Exam Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .sidebar {
            background-color: #f0f7ff;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #e0e7ff;
            color: #1e40af;
        }
        .header {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .main-content {
            background-color: #f9fafb;
        }
        .question-card {
            transition: transform 0.2s ease;
        }
        .question-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar w-64 h-full p-6 fixed">
        <div class="flex items-center mb-8">
            <i class="fas fa-book-open text-2xl text-blue-600 mr-2"></i>
            <h1 class="text-xl font-bold text-blue-800">Exam Platform</h1>
        </div>
        <nav>
            <h2 class="text-sm font-semibold text-blue-700 mb-4">Menu Peserta</h2>
            <ul class="space-y-2">
                <li>
                    <a href="daftar_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                        <i class="fas fa-clipboard-list mr-3"></i> Daftar Ujian
                    </a>
                </li>
                <li>
                    <a href="hasil_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                        <i class="fas fa-file-alt mr-3"></i> Hasil Ujian
                    </a>
                </li>
            </ul>
            <a href="../dashboard.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition mt-6">
                <i class="fas fa-tachometer-alt mr-3"></i> Kembali ke Dashboard
            </a>
            <a href="../logout.php" class="flex items-center p-3 rounded-lg text-red-600 hover:bg-red-100 transition mt-2">
                <i class="fas fa-sign-out-alt mr-3"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 ml-64">
        <!-- Header -->
        <div class="header flex justify-between items-center p-4">
            <h1 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($exam['title']) ?></h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Selamat datang, <span class="font-medium"><?= $_SESSION['username'] ?></span></span>
                <span class="text-gray-600">Role: <span class="font-medium"><?= ucfirst($_SESSION['role']) ?></span></span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content p-6">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <p class="text-gray-600 mb-4"><?= htmlspecialchars($exam['description']) ?></p>
            </div>

            <form method="POST" class="space-y-6">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="question-card bg-white rounded-lg shadow p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-800 mb-2">
                            Soal <?= $index + 1 ?> (<?= $question['point'] ?> poin)
                        </h3>
                        <p class="text-gray-700 mb-4"><?= htmlspecialchars($question['question_text']) ?></p>
                        
                        <?php if ($question['type'] === 'pilihan_ganda'): ?>
                            <?php
                            $stmt = $pdo->prepare("SELECT * FROM options WHERE question_id = ?");
                            $stmt->execute([$question['id']]);
                            $options = $stmt->fetchAll();
                            ?>
                            <div class="space-y-2">
                                <?php foreach ($options as $option): ?>
                                    <label class="flex items-center space-x-2 cursor-pointer">
                                        <input type="radio" 
                                               name="question_<?= $question['id'] ?>" 
                                               value="<?= $option['id'] ?>" 
                                               required 
                                               class="text-blue-600 focus:ring-blue-500">
                                        <span class="text-gray-700"><?= htmlspecialchars($option['option_text']) ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <textarea name="question_<?= $question['id'] ?>" rows="4" required 
                                      class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"></textarea>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="bg-white rounded-lg shadow p-6">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Simpan Jawaban
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>