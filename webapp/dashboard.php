<?php
session_start();
require_once 'common/db.php';
require_once 'includes/functions.php';

if (!is_logged_in()) {
    redirect('index.php');
}

$user_role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Exam Platform</title>
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
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Selamat datang, <span class="font-medium"><?= $_SESSION['username'] ?></span></span>
                <span class="text-gray-600">Role: <span class="font-medium"><?= ucfirst($user_role) ?></span></span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content p-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Selamat Datang di Dashboard</h2>
                <p class="text-gray-600">Gunakan menu di sebelah kiri untuk mengakses fitur yang tersedia sesuai dengan peran Anda.</p>
            </div>
        </div>
    </div>
</body>
</html>