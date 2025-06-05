<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_logged_in()) redirect('../index.php');

$user_id = $_SESSION['user_id'];

// Ambil ujian yang pernah diikuti
$stmt = $pdo->prepare("
    SELECT e.id, e.title
    FROM exams e
    JOIN questions q ON e.id = q.exam_id
    JOIN answers a ON q.id = a.question_id
    WHERE a.user_id = ?
    GROUP BY e.id
");
$stmt->execute([$user_id]);
$exams = $stmt->fetchAll();

$selected_exam = null;
$results = [];

if (isset($_GET['exam_id'])) {
    $exam_id = $_GET['exam_id'];

    // Ambil detail ujian
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
    $stmt->execute([$exam_id]);
    $selected_exam = $stmt->fetch();

    // Ambil hasil ujian
    $stmt = $pdo->prepare("
        SELECT q.question_text, q.point, a.answer_text, a.score
        FROM answers a
        JOIN questions q ON a.question_id = q.id
        WHERE a.user_id = ? AND q.exam_id = ?
    ");
    $stmt->execute([$user_id, $exam_id]);
    $results = $stmt->fetchAll();

    // Hitung total nilai
    $total_score = array_sum(array_column($results, 'score'));
    $max_score = array_sum(array_column($results, 'point'));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Ujian - Exam Platform</title>
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
            <h2 class="text-sm font-semibold text-blue-700 mb-4">Menu Peserta</h2>
            <ul class="space-y-2">
                <li>
                    <a href="daftar_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition">
                        <i class="fas fa-clipboard-list mr-3"></i> Daftar Ujian
                    </a>
                </li>
                <li>
                    <a href="hasil_ujian.php" class="flex items-center p-3 rounded-lg text-blue-600 bg-blue-100">
                        <i class="fas fa-file-alt mr-3"></i> Hasil Ujian
                    </a>
                </li>
            </ul>
            <a href="../dashboard.php" class="flex items-center p-3 rounded-lg text-blue-600 hover:bg-blue-100 transition mt-6">
                <i class="fas fa-tachometer-alt mr-3"></i> Kembali ke Dashboard
            </a>
            <a href="../logout.php" class="flex items-center p-3 rounded-lg text-red-600 hover:bg-red-100 transition mt-6">
                <i class="fas fa-sign-out-alt mr-3"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="flex-1 ml-64">
        <!-- Header -->
        <div class="header flex justify-between items-center p-4">
            <h1 class="text-2xl font-semibold text-gray-800">Hasil Ujian</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Selamat datang, <span class="font-medium"><?= $_SESSION['username'] ?></span></span>
                <span class="text-gray-600">Role: <span class="font-medium"><?= ucfirst($_SESSION['role']) ?></span></span>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="main-content p-6">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" class="space-y-4">
                    <div>
                        <label for="exam_id" class="block text-sm font-medium text-gray-700">Pilih Ujian</label>
                        <select name="exam_id" id="exam_id" required class="mt-1 block w-full p-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Ujian</option>
                            <?php foreach ($exams as $exam): ?>
                                <option value="<?= $exam['id'] ?>" <?= isset($_GET['exam_id']) && $_GET['exam_id'] == $exam['id'] ? 'selected' : '' ?>>
                                    <?= $exam['title'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Tampilkan</button>
                </form>
            </div>

            <?php if ($selected_exam): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Hasil Ujian: <?= $selected_exam['title'] ?></h2>
                    <p class="text-lg text-gray-700 mb-4"><strong>Total Nilai:</strong> <?= round(($total_score / $max_score) * 100, 2) ?>%</p>

                    <h3 class="text-md font-semibold text-gray-800 mb-3">Detail Jawaban:</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-blue-50">
                                    <th class="p-3 border-b text-gray-600">Soal</th>
                                    <th class="p-3 border-b text-gray-600">Jawaban Anda</th>
                                    <th class="p-3 border-b text-gray-600">Nilai</th>
                                    <th class="p-3 border-b text-gray-600">Poin Maks</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $result): ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="p-3 border-b"><?= $result['question_text'] ?></td>
                                        <td class="p-3 border-b"><?= $result['answer_text'] ?></td>
                                        <td class="p-3 border-b"><?= $result['score'] ?? 'Belum dinilai' ?></td>
                                        <td class="p-3 border-b"><?= $result['point'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
