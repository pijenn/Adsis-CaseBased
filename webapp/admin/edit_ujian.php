<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    redirect('../index.php');
}

$id = $_GET['id'] ?? 0;

// Ambil data ujian
$stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->execute([$id]);
$exam = $stmt->fetch();

if (!$exam) {
    die("Ujian tidak ditemukan");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    $stmt = $pdo->prepare("UPDATE exams SET title=?, description=?, start_time=?, end_time=? WHERE id=?");
    $stmt->execute([$title, $description, $start_time, $end_time, $id]);
    
    $_SESSION['success'] = "Ujian berhasil diperbarui!";
    header('Location: lihat_ujian.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ujian</title>
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
                    <a href="tambah_soal.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
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
            <h1 class="text-2xl font-semibold text-gray-800">Edit Ujian: <?= htmlspecialchars($exam['title']) ?></h1>
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
            <div class="bg-white rounded-lg shadow p-6 max-w-2xl">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                        <?= $_SESSION['success'] ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Judul Ujian</label>
                        <input type="text" name="title" id="title" value="<?= htmlspecialchars($exam['title']) ?>" required 
                               class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                        <textarea name="description" id="description" required 
                                  class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 h-32"><?= htmlspecialchars($exam['description']) ?></textarea>
                    </div>

                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700">Waktu Mulai</label>
                        <input type="datetime-local" name="start_time" id="start_time" 
                               value="<?= date('Y-m-d\TH:i', strtotime($exam['start_time'])) ?>" required 
                               class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700">Waktu Selesai</label>
                        <input type="datetime-local" name="end_time" id="end_time" 
                               value="<?= date('Y-m-d\TH:i', strtotime($exam['end_time'])) ?>" required 
                               class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>