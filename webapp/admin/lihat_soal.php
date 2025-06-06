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

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $question_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT exam_id FROM questions WHERE id = ?");
    $stmt->execute([$question_id]);
    $question = $stmt->fetch();

    if ($question) {
        $exam_id = $question['exam_id'];
        // Hapus opsi terkait jika ada
        $stmt = $pdo->prepare("DELETE FROM options WHERE question_id = ?");
        $stmt->execute([$question_id]);
        // Hapus soal
        $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
        $stmt->execute([$question_id]);
        $_SESSION['success'] = "Soal berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Soal tidak ditemukan.";
    }
    header("Location: lihat_soal.php?exam_id=$exam_id");
    exit;
}

// Ambil soal untuk ujian ini
$stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll();

// Handle form submission (edit/save)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($questions as $question) {
        $question_id = $question['id'];
        $new_text = $_POST['question_text_' . $question_id] ?? $question['question_text'];
        $new_type = $_POST['type_' . $question_id] ?? $question['type'];
        $new_point = $_POST['point_' . $question_id] ?? $question['point'];

        // Update question
        $stmt = $pdo->prepare("UPDATE questions SET question_text = ?, type = ?, point = ? WHERE id = ?");
        $stmt->execute([$new_text, $new_type, $new_point, $question_id]);

        // Handle options for multiple-choice questions
        if ($new_type === 'pilihan_ganda') {
            $options = $_POST['options_' . $question_id] ?? [];
            $correct_answer = $_POST['correct_answer_' . $question_id] ?? 0;

            // Delete existing options
            $stmt = $pdo->prepare("DELETE FROM options WHERE question_id = ?");
            $stmt->execute([$question_id]);

            // Insert updated options
            foreach ($options as $index => $option_text) {
                if (!empty($option_text)) {
                    $is_correct = ($index == $correct_answer) ? 1 : 0;
                    $stmt = $pdo->prepare("INSERT INTO options (question_id, option_text, is_correct) VALUES (?, ?, ?)");
                    $stmt->execute([$question_id, $option_text, $is_correct]);
                }
            }
        }
    }
    $_SESSION['success'] = "Soal berhasil disimpan!";
    header("Location: lihat_soal.php?exam_id=$exam_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soal Ujian: <?= htmlspecialchars($exam['title']) ?></title>
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
        .question-row {
            display: flex;
            flex-wrap: nowrap;
            gap: 1rem;
            align-items: center;
        }
        .options-container {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
    </style>
</head>
<body class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar w-64 h-full p-6 fixed">
        <div class="flex items-center mb-8">
            <i class="fas fa-book-open text-2xl text-blue-600 mr-2"></i>
            <h1 class="text-xl font-bold text-blue-800">Website Ujian</h1>
        </div>
        <nav>
            <h2 class="text-sm font-semibold text-blue-700 mb-4">Menu Admin</h2>
            <ul class="space-y-2">
                <li>
                    <a href="tambah_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                        <i class="fas fa-plus mr-3"></i> Tambah Ujian
                    </a>
                </li>
                <li>
                    <a href="tambah_soal.php?exam_id=<?= $exam_id ?>" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                        <i class="fas fa-plus mr-3"></i> Tambah Soal Baru
                    </a>
                </li>
                <li>
                    <a href="lihat_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                        <i class="fas fa-list mr-3"></i> Lihat Ujian
                    </a>
                </li>
                <li>
                    <a href="lihat_nilai.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                        <i class="fas fa-chart-bar mr-3"></i> Lihat Nilai
                    </a>
                </li>
                <li>
                    <a href="daftar_jawaban_essai.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                        <i class="fas fa-pen mr-3"></i> Nilai Jawaban Esai
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
            <h1 class="text-2xl font-semibold text-gray-800">Soal Ujian: <?= htmlspecialchars($exam['title']) ?></h1>
        <div class="flex items-center space-x-4 bg-blue-50 border border-blue-100 p-4 rounded-lg shadow-sm ml-6">
            <div class="flex items-center text-blue-700 font-semibold">
                <i class="fas fa-user-circle text-lg mr-2"></i>
                <span>Selamat datang, <span class="text-blue-900 font-bold"><?= htmlspecialchars($_SESSION['username']) ?></span></span>
            </div>
            <div class="flex items-center text-green-700 font-semibold">
                <i class="fas fa-id-badge text-lg mr-2"></i>
                <span>Role: <span class="text-green-900 font-bold"><?= strtoupper($_SESSION['role']) ?></span></span>
            </div>
        </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content p-6">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                    <?= $_SESSION['success'] ?>
                    <?php unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                    <?= $_SESSION['error'] ?>
                    <?php unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php if (count($questions) > 0): ?>
                <form method="POST" class="space-y-6">
                    <?php foreach ($questions as $question): ?>
                        <div class="bg-white rounded-lg shadow p-4">
                            <div class="question-row">
                                <div class="w-16">
                                    <label class="block text-sm font-medium text-gray-700">ID</label>
                                    <input type="text" value="<?= $question['id'] ?>" readonly class="w-full p-2 border border-gray-300 rounded-lg bg-gray-100">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700">Pertanyaan</label>
                                    <input type="text" name="question_text_<?= $question['id'] ?>" value="<?= htmlspecialchars($question['question_text']) ?>" 
                                           class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="w-32">
                                    <label class="block text-sm font-medium text-gray-700">Tipe</label>
                                    <select name="type_<?= $question['id'] ?>" class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option value="pilihan_ganda" <?= $question['type'] === 'pilihan_ganda' ? 'selected' : '' ?>>Pilihan Ganda</option>
                                        <option value="essai" <?= $question['type'] === 'essai' ? 'selected' : '' ?>>Essai</option>
                                    </select>
                                </div>
                                <div class="w-20">
                                    <label class="block text-sm font-medium text-gray-700">Poin</label>
                                    <input type="number" name="point_<?= $question['id'] ?>" value="<?= $question['point'] ?>" 
                                           class="w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div class="w-24">
                                    <label class="block text-sm font-medium text-gray-700">Aksi</label>
                                    <a href="lihat_soal.php?exam_id=<?= $exam_id ?>&action=delete&id=<?= $question['id'] ?>" 
                                       class="inline-block px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                                       onclick="return confirm('Apakah Anda yakin?')">
                                        Hapus
                                    </a>
                                </div>
                            </div>

                            <?php if ($question['type'] === 'pilihan_ganda'): ?>
                                <div class="mt-4">
                                    <?php
                                    $stmt = $pdo->prepare("SELECT * FROM options WHERE question_id = ?");
                                    $stmt->execute([$question['id']]);
                                    $options = $stmt->fetchAll();
                                    ?>
                                    <div class="options-container">
                                        <?php for ($i = 0; $i < 4; $i++): ?>
                                            <div class="flex items-center gap-2">
                                                <label class="block text-sm font-medium text-gray-700 w-20">Opsi <?= $i + 1 ?></label>
                                                <input type="text" name="options_<?= $question['id'] ?>[]" 
                                                       value="<?= $options[$i]['option_text'] ?? '' ?>" 
                                                       class="flex-1 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="mt-2 flex items-center gap-2">
                                        <label class="block text-sm font-medium text-gray-700 w-20">Jawaban Benar</label>
                                        <select name="correct_answer_<?= $question['id'] ?>" class="w-32 p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                            <?php for ($i = 0; $i < 4; $i++): ?>
                                                <option value="<?= $i ?>" <?= isset($options[$i]) && $options[$i]['is_correct'] ? 'selected' : '' ?>>
                                                    Opsi <?= $i + 1 ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="bg-white rounded-lg shadow p-4 mt-6 text-center">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            Simpan Soal
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-gray-600">Belum ada soal untuk ujian ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>