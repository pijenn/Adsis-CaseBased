<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    redirect('../index.php');
}

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $exam_id = $_GET['id'];
    // Hapus soal dan opsi terkait terlebih dahulu
    $stmt = $pdo->prepare("DELETE FROM options WHERE question_id IN (SELECT id FROM questions WHERE exam_id = ?)");
    $stmt->execute([$exam_id]);
    $stmt = $pdo->prepare("DELETE FROM questions WHERE exam_id = ?");
    $stmt->execute([$exam_id]);
    // Hapus ujian
    $stmt = $pdo->prepare("DELETE FROM exams WHERE id = ?");
    $stmt->execute([$exam_id]);
    $_SESSION['success'] = "Ujian berhasil dihapus!";
    header("Location: lihat_ujian.php");
    exit;
}

// Ambil semua ujian
$stmt = $pdo->query("SELECT * FROM exams");
$exams = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Daftar Ujian</title>
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
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        @media (max-width: 640px) {
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            .action-buttons a {
                padding: 0.25rem 0.5rem !important;
                font-size: 0.875rem;
            }
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
                    <a href="lihat_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 bg-blue-100">
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
            <h1 class="text-2xl font-semibold text-gray-800">Daftar Ujian</h1>
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

            <?php if (count($exams) > 0): ?>
                <div class="bg-white rounded-lg shadow">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th class="p-3 border-b text-gray-600">ID</th>
                                    <th class="p-3 border-b text-gray-600">Judul</th>
                                    <th class="p-3 border-b text-gray-600">Deskripsi</th>
                                    <th class="p-3 border-b text-gray-600">Waktu Mulai</th>
                                    <th class="p-3 border-b text-gray-600">Waktu Selesai</th>
                                    <th class="p-3 border-b text-gray-600">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($exams as $exam): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 border-b"><?= $exam['id'] ?></td>
                                        <td class="p-3 border-b"><?= htmlspecialchars($exam['title']) ?></td>
                                        <td class="p-3 border-b"><?= htmlspecialchars($exam['description']) ?></td>
                                        <td class="p-3 border-b"><?= date('d M Y H:i', strtotime($exam['start_time'])) ?></td>
                                        <td class="p-3 border-b"><?= date('d M Y H:i', strtotime($exam['end_time'])) ?></td>
                                        <td class="p-3 border-b">
                                            <div class="action-buttons">
                                                <a href="edit_ujian.php?id=<?= $exam['id'] ?>" 
                                                   class="inline-block px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                    Edit
                                                </a>
                                                <a href="tambah_soal.php?exam_id=<?= $exam['id'] ?>" 
                                                   class="inline-block px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                                                    Tambah Soal
                                                </a>
                                                <a href="lihat_soal.php?exam_id=<?= $exam['id'] ?>" 
                                                   class="inline-block px-3 py-1 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                                    Lihat Soal
                                                </a>
                                                <a href="lihat_ujian.php?action=delete&id=<?= $exam['id'] ?>" 
                                                   class="inline-block px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                                                   onclick="return confirm('Apakah Anda yakin?')">
                                                    Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow p-6 text-center">
                    <p class="text-gray-600">Belum ada ujian yang dibuat.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>