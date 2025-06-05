<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) redirect('../index.php');

// Ambil semua ujian
$now = date('Y-m-d H:i:s');
$stmt = $pdo->query("SELECT * FROM exams");
$exams = $stmt->fetchAll();

// Categorize exams
$current_exams = [];
$upcoming_exams = [];
$closed_exams = [];

foreach ($exams as $exam) {
    if ($now >= $exam['start_time'] && $now <= $exam['end_time']) {
        $current_exams[] = $exam;
    } elseif ($now < $exam['start_time']) {
        $upcoming_exams[] = $exam;
    } else {
        $closed_exams[] = $exam;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Ujian</title>
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
        .exam-card {
            transition: transform 0.2s ease;
        }
        .exam-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="flex h-screen">
    <!-- Sidebar -->
    <div class="sidebar w-64 h-full p-6 fixed">
        <div class="flex items-center mb-8">
            <i class="fas fa-book-open text-2xl text-blue-600 mr-2"></i>
            <h1 class="text-xl font-bold text-blue-800">Ujian FILKOM</h1>
        </div>
        <nav>
            <h2 class="text-sm font-semibold text-blue-700 mb-4">Menu Peserta</h2>
            <ul class="space-y-2">
                <li>
                    <a href="daftar_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 bg-blue-100">
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
            <h1 class="text-2xl font-semibold text-gray-800">Daftar Ujian</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Selamat datang, <span class="font-medium"><?= $_SESSION['username'] ?></span></span>
                <span class="text-gray-600">Role: <span class="font-medium"><?= ucfirst($_SESSION['role']) ?></span></span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content p-6">
            <!-- Current Exams -->
            <?php if (count($current_exams) > 0): ?>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ujian Saat Ini</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <?php foreach ($current_exams as $exam): ?>
                        <div class="exam-card bg-white rounded-lg shadow p-6 border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= $exam['title'] ?></h3>
                            <p class="text-gray-600 mb-3"><?= $exam['description'] ?></p>
                            <p class="text-sm text-gray-500 mb-4">
                                <i class="fas fa-clock mr-1"></i> 
                                <?= date('d M Y H:i', strtotime($exam['start_time'])) ?> - 
                                <?= date('d M Y H:i', strtotime($exam['end_time'])) ?>
                            </p>
                            <a href="kerjakan_ujian.php?exam_id=<?= $exam['id'] ?>" 
                               class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                Kerjakan
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Upcoming Exams -->
            <?php if (count($upcoming_exams) > 0): ?>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ujian Mendatang</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                    <?php foreach ($upcoming_exams as $exam): ?>
                        <div class="exam-card bg-white rounded-lg shadow p-6 border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= $exam['title'] ?></h3>
                            <p class="text-gray-600 mb-3"><?= $exam['description'] ?></p>
                            <p class="text-sm text-gray-500 mb-4">
                                <i class="fas fa-clock mr-1"></i> 
                                <?= date('d M Y H:i', strtotime($exam['start_time'])) ?> - 
                                <?= date('d M Y H:i', strtotime($exam['end_time'])) ?>
                            </p>
                            <span class="text-gray-500">Belum Dimulai</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Closed Exams -->
            <?php if (count($closed_exams) > 0): ?>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Ujian Tertutup</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($closed_exams as $exam): ?>
                        <div class="exam-card bg-white rounded-lg shadow p-6 border border-gray-100">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2"><?= $exam['title'] ?></h3>
                            <p class="text-gray-600 mb-3"><?= $exam['description'] ?></p>
                            <p class="text-sm text-gray-500 mb-4">
                                <i class="fas fa-clock mr-1"></i> 
                                <?= date('d M Y H:i', strtotime($exam['start_time'])) ?> - 
                                <?= date('d M Y H:i', strtotime($exam['end_time'])) ?>
                            </p>
                            <span class="text-gray-500">Sudah Ditutup</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (count($current_exams) === 0 && count($upcoming_exams) === 0 && count($closed_exams) === 0): ?>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-gray-600">Tidak ada ujian yang tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>