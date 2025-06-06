<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    redirect('../index.php');
}

$answer_id = $_GET['id'] ?? 0;

// Ambil detail jawaban
$stmt = $pdo->prepare("
    SELECT a.id, a.answer_text, a.score, u.username, q.question_text, q.point, e.title AS exam_title
    FROM answers a
    JOIN users u ON a.user_id = u.id
    JOIN questions q ON a.question_id = q.id
    JOIN exams e ON q.exam_id = e.id
    WHERE a.id = ?
");
$stmt->execute([$answer_id]);
$answer = $stmt->fetch();

if (!$answer) {
    die("Jawaban tidak ditemukan");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $score = $_POST['score'];
    
    // Validasi: nilai tidak boleh melebihi poin soal
    if ($score > $answer['point']) {
        $error = "Nilai tidak boleh melebihi poin soal (maks: {$answer['point']})";
    } else {
        $stmt = $pdo->prepare("UPDATE answers SET score = ? WHERE id = ?");
        $stmt->execute([$score, $answer_id]);
        
        $_SESSION['success'] = "Nilai berhasil disimpan!";
        header('Location: daftar_jawaban_essai.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beri Nilai Esai</title>
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
        .text-container {
            max-height: 200px;
            overflow-y: auto;
            line-height: 1.6;
        }
        @media (max-width: 640px) {
            .text-container {
                max-height: 150px;
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
                    <a href="daftar_jawaban_essai.php" class="flex items-center p-3 rounded-lg text-blue-600 bg-blue-100">
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
            <h1 class="text-2xl font-semibold text-gray-800">Beri Nilai Esai</h1>
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
            <div class="bg-white rounded-lg shadow p-6 max-w-3xl">
                <?php if (isset($error)): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <h2 class="text-lg font-semibold text-gray-800 mb-2">Ujian: <?= htmlspecialchars($answer['exam_title']) ?></h2>
                <h3 class="text-md font-medium text-gray-700 mb-2">Peserta: <?= htmlspecialchars($answer['username']) ?></h3>
                
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-600 mb-1">Soal:</h4>
                    <div class="text-container text-gray-700 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <?= nl2br(htmlspecialchars($answer['question_text'])) ?>
                    </div>
                </div>

                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-600 mb-1">Jawaban:</h4>
                    <div class="text-container text-gray-700 bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <?= nl2br(htmlspecialchars($answer['answer_text'])) ?>
                    </div>
                </div>

                <form method="POST" class="space-y-4">
                    <div>
                        <label for="score" class="block text-sm font-medium text-gray-700">
                            Nilai (maks: <?= $answer['point'] ?>)
                        </label>
                        <input type="number" name="score" id="score" min="0" max="<?= $answer['point'] ?>" required 
                               class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Simpan Nilai
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>