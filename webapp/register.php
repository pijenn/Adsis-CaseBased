<?php
session_start();
require_once 'common/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'user';
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $role]);
        $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        $error = "Username sudah digunakan!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Registrasi</title>
</head>
<body>
    <h1>Registrasi Akun</h1>
    <?php if (isset($error)): ?>
        <p style="color:red;"><?= $error ?></p>
    <?php endif; ?>
    <form method="POST">
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Daftar</button>
    </form>
    <p>Sudah punya akun? <a href="index.php">Login disini</a></p>
</body>
</html>
