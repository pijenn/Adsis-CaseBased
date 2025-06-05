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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soal Ujian: <?= htmlspecialchars($exam['title']) ?> - Exam Platform</title>
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
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Selamat datang, <span class="font-medium"><?= $_SESSION['username'] ?></span></span>
                <span class="text-gray-600">Role: <span class="font-medium"><?= ucfirst($_SESSION['role']) ?></span></span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content p-6">
            <?php if (count($questions) > 0): ?>
                <div class="bg-white rounded-lg shadow">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th class="p-3 border-b text-gray-600">ID</th>
                                    <th class="p-3 border-b text-gray-600">Pertanyaan</th>
                                    <th class="p-3 border-b text-gray-600">Tipe</th>
                                    <th class="p-3 border-b text-gray-600">Poin</th>
                                    <th class="p-3 border-b text-gray-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questions as $question): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 border-b"><?= $question['id'] ?></td>
                                        <td class="p-3 border-b"><?= htmlspecialchars(substr($question['question_text'], 0, 50)) ?>...</td>
                                        <td class="p-3 border-b"><?= $question['type'] === 'pilihan_ganda' ? 'Pilihan Ganda' : 'Essai' ?></td>
                                        <td class="p-3 border-b"><?= $question['point'] ?></td>
                                        <td class="p-3 border-b">
                                            <a href="edit_soal.php?id=<?= $question['id'] ?>" 
                                               class="inline-block px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 mr-2 transition">
                                                Edit
                                            </a>
                                            <a href="hapus_soal.php?id=<?= $question['id'] ?>" 
                                               class="inline-block px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                                               onclick="return confirm('Apakah Anda yakin?')">
                                                Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-gray-600">Belum ada soal untuk ujian ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>