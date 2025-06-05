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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Soal - Exam Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script>
        function toggleOptions() {
            const type = document.getElementById('type').value;
            const optionsContainer = document.getElementById('options-container');
            optionsContainer.style.display = (type === 'pilihan_ganda') ? 'block' : 'none';
        }
        document.addEventListener('DOMContentLoaded', toggleOptions);
    </script>
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
            <h2 class="text-sm font-semibold text-blue-700 mb-4">Menu Admin</h2>
            <ul class="space-y-2">
                <li>
                    <a href="tambah_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                        <i class="fas fa-plus mr-3"></i> Tambah Ujian
                    </a>
                </li>
                <li>
                    <a href="tambah_soal.php" class="flex items-center p-3 rounded-lg text-blue-600 bg-blue-100">
                        <i class="fas fa-question-circle mr-3"></i> Tambah Soal
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
            <h1 class="text-2xl font-semibold text-gray-800">Tambah Soal</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Selamat datang, <span class="font-medium"><?= $_SESSION['username'] ?></span></span>
                <span class="text-gray-600">Role: <span class="font-medium"><?= ucfirst($_SESSION['role']) ?></span></span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content p-6">
            <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
                <?php if (isset($success)): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="exam_id" class="block text-sm font-medium text-gray-700">Pilih Ujian</label>
                        <select name="exam_id" id="exam_id" required 
                                class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Ujian</option>
                            <?php foreach ($exams as $exam): ?>
                                <option value="<?= $exam['id'] ?>"><?= $exam['title'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label for="question_text" class="block text-sm font-medium text-gray-700">Pertanyaan</label>
                        <textarea name="question_text" id="question_text" placeholder="Pertanyaan" required 
                                  class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 h-24"></textarea>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700">Tipe</label>
                        <select id="type" name="type" onchange="toggleOptions()" required 
                                class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="pilihan_ganda">Pilihan Ganda</option>
                            <option value="essai">Essai</option>
                        </select>
                    </div>

                    <div>
                        <label for="point" class="block text-sm font-medium text-gray-700">Poin</label>
                        <input type="number" name="point" id="point" placeholder="Poin" required 
                               class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div id="options-container" class="space-y-2" style="display:none;">
                        <h3 class="text-md font-semibold text-gray-700">Opsi Jawaban</h3>
                        <?php for ($i = 0; $i < 4; $i++): ?>
                            <input type="text" name="options[]" placeholder="Opsi <?= $i + 1 ?>" 
                                   class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <?php endfor; ?>
                        <div>
                            <label for="correct_answer" class="block text-sm font-medium text-gray-700">Jawaban Benar</label>
                            <select name="correct_answer" id="correct_answer" 
                                    class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="0">Opsi 1</option>
                                <option value="1">Opsi 2</option>
                                <option value="2">Opsi 3</option>
                                <option value="3">Opsi 4</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Simpan
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>