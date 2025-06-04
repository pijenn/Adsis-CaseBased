<?php
session_start();
require_once '../common/db.php';
require_once '../includes/functions.php';

if (!is_admin()) {
    redirect('../index.php');
}

// Ambil semua ujian
$stmt = $pdo->query("SELECT * FROM exams");
$exams = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Lihat Daftar Ujian</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Daftar Ujian</h1>
    <a href="tambah_ujian.php">Tambah Ujian Baru</a>
    
    <?php if (count($exams) > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Judul</th>
                <th>Deskripsi</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Aksi</th>
            </tr>
            <?php foreach ($exams as $exam): ?>
            <tr>
                <td><?= $exam['id'] ?></td>
                <td><?= htmlspecialchars($exam['title']) ?></td>
                <td><?= htmlspecialchars($exam['description']) ?></td>
                <td><?= date('d M Y H:i', strtotime($exam['start_time'])) ?></td>
                <td><?= date('d M Y H:i', strtotime($exam['end_time'])) ?></td>
                <td>
                    <a href="edit_ujian.php?id=<?= $exam['id'] ?>">Edit</a> | 
                    <a href="tambah_soal.php?exam_id=<?= $exam['id'] ?>">Tambah Soal</a> | 
                    <a href="lihat_soal.php?exam_id=<?= $exam['id'] ?>">Lihat Soal</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>Belum ada ujian yang dibuat.</p>
    <?php endif; ?>
    
    <a href="../dashboard.php">Kembali ke Dashboard</a>
</body>
</html>
