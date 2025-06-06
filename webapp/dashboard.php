<?php
session_start();
require_once 'common/db.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Fetch stats based on role
if (is_admin()) {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM exams");
    $total_exams = $stmt->fetch()['total'];
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM questions");
    $total_questions = $stmt->fetch()['total'];
} else {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM exams WHERE start_time > NOW() AND end_time > NOW()");
    $stmt->execute();
    $upcoming_exams = $stmt->fetch()['total'];
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM answers WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $completed_exams = $stmt->fetch()['total'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Website Ujian</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(to bottom, #e0e7ff, #f9fafb);
        }
        .sidebar {
            background-color: #f0f7ff;
            transition: all 0.3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar a {
            color: #1e40af;
            transition: all 0.3s ease;
        }
        .sidebar a:hover {
            background-color: #e0e7ff;
            color: #1e40af;
        }
        .header {
            background: linear-gradient(to right, #ffffff, #f0f7ff);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .main-content {
            background-color: transparent;
        }
        .stat-card {
            background: white;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        .welcome-card {
            background: linear-gradient(to right, #3b82f6, #1e40af);
            color: white;
        }
        .icon-circle {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 50%;
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
            <?php if (is_admin()): ?>
                <h2 class="text-sm font-semibold text-blue-700 mb-4">Menu Admin</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="admin/tambah_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                            <i class="fas fa-plus mr-3"></i> Tambah Ujian
                        </a>
                    </li>
                    <li>
                        <a href="admin/tambah_soal.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                            <i class="fas fa-question-circle mr-3"></i> Tambah Soal
                        </a>
                    </li>
                    <li>
                        <a href="admin/lihat_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                            <i class="fas fa-list mr-3"></i> Lihat Ujian
                        </a>
                    </li>
                    <li>
                        <a href="admin/lihat_nilai.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                            <i class="fas fa-chart-bar mr-3"></i> Lihat Nilai
                        </a>
                    </li>
                    <li>
                        <a href="admin/daftar_jawaban_essai.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                            <i class="fas fa-pen mr-3"></i> Nilai Jawaban Esai
                        </a>
                    </li>
                </ul>
            <?php else: ?>
                <h2 class="text-sm font-semibold text-blue-700 mb-4">Menu Peserta</h2>
                <ul class="space-y-2">
                    <li>
                        <a href="user/daftar_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                            <i class="fas fa-clipboard-list mr-3"></i> Daftar Ujian
                        </a>
                    </li>
                    <li>
                        <a href="user/hasil_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                            <i class="fas fa-file-alt mr-3"></i> Hasil Ujian
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
            <a href="logout.php" class="flex items-center p-3 rounded-lg text-red-600 hover:bg-red-100 transition mt-6">
                <i class="fas fa-sign-out-alt mr-3"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 ml-64">
        <!-- Header -->
        <div class="header flex justify-between items-center p-4">
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
            <div class="flex items-center space-x-4 bg-blue-50 border border-blue-100 p-4 rounded-lg shadow-sm">
                <div class="flex items-center text-blue-700 font-semibold">
                    <i class="fas fa-user-circle text-lg mr-2"></i>
                    <span>Selamat datang, <span class="text-blue-900 font-bold"><?= htmlspecialchars($_SESSION['username']) ?></span></span>
                </div>
                <div class="flex items-center text-blue-700 font-semibold">
                    <i class="fas fa-clock text-lg mr-2"></i>
                    <span><?= date('d M Y H:i') ?> WIB</span>
                </div>
                <div class="flex items-center text-green-700 font-semibold">
                <i class="fas fa-id-badge text-lg mr-2"></i>
                <span>Role: <span class="text-green-900 font-bold"><?= strtoupper($_SESSION['role']) ?></span></span>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content p-6">
            <!-- Welcome Card -->
            <div class="welcome-card rounded-lg shadow p-6 mb-6">
                <div class="flex items-center">
                    <div class="icon-circle mr-4">
                        <i class="fas fa-tachometer-alt text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold">Selamat Datang di Dashboard</h2>
                        <p class="text-sm opacity-80">Gunakan menu di sebelah kiri untuk mengakses fitur yang tersedia sesuai dengan peran Anda.</p>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (is_admin()): ?>
                    <div class="stat-card rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="icon-circle mr-4">
                                <i class="fas fa-book text-xl text-blue-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Total Ujian</h3>
                                <p class="text-2xl font-bold text-blue-600"><?= $total_exams ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="icon-circle mr-4">
                                <i class="fas fa-question-circle text-xl text-green-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Total Soal</h3>
                                <p class="text-2xl font-bold text-green-600"><?= $total_questions ?></p>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="stat-card rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="icon-circle mr-4">
                                <i class="fas fa-calendar-alt text-xl text-indigo-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Ujian Mendatang</h3>
                                <p class="text-2xl font-bold text-indigo-600"><?= $upcoming_exams ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="stat-card rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="icon-circle mr-4">
                                <i class="fas fa-check-circle text-xl text-purple-600"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800">Ujian Selesai</h3>
                                <p class="text-2xl font-bold text-purple-600"><?= $completed_exams ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>