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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Akun CBT</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .bg-cbt-blue-theme {
            background-color: #0B2447; /* Biru sangat tua sebagai dasar */
            background-image: linear-gradient(to bottom, #19376D, #0B2447); /* Gradient biru tua */
        }
        .form-container {
            background-color: rgba(25, 55, 109, 0.2); /* Biru semi-transparan yang lebih gelap */
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(57, 78, 127, 0.5); /* Border tipis untuk kontras */
        }
        .input-field {
            background-color: transparent;
            border-bottom: 1px solid rgba(165, 193, 240, 0.5); /* Border bawah biru muda */
            color: #E0E7FF; /* Warna teks input biru sangat muda / putih kebiruan */
        }
        .input-field::placeholder {
            color: rgba(165, 193, 240, 0.7); /* Placeholder biru muda */
        }
        .input-field:focus {
            border-bottom-color: #A5C1F0; /* Border fokus biru muda lebih terang */
            outline: none;
        }
        .btn-register { /* Mengubah nama kelas agar lebih spesifik */
            background-color: #576CBC; /* Tombol biru medium */
            color: #FFFFFF; /* Teks tombol putih */
        }
        .btn-register:hover {
            background-color: #3E54A3; /* Hover tombol biru lebih tua */
        }
        .text-link-blue {
            color: #A5C1F0; /* Link biru muda */
        }
        .text-link-blue:hover {
            color: #C7D2FE; /* Hover link biru lebih terang */
        }
        .error-banner {
            background-color: rgba(239, 68, 68, 0.1); /* Latar merah semi transparan */
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #FECACA; /* Teks error merah muda */
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-cbt-blue-theme min-h-screen flex flex-col items-center justify-center p-6 relative overflow-hidden">

    <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-blue-500/20 rounded-full animate-pulse"></div>
    <div class="absolute -top-20 -right-20 w-80 h-80 bg-blue-600/20 rounded-full animate-pulse animation-delay-2000"></div>

    <div class="form-container p-8 md:p-12 rounded-3xl shadow-2xl w-full max-w-md z-10">
        <h1 class="text-4xl font-bold text-center mb-10 text-slate-100">Registrasi Akun</h1>

        <?php if (isset($error) && $error): ?>
            <div class="error-banner p-3 rounded-lg mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <div class="mb-6">
                <label for="username" class="block text-sm font-medium text-slate-300 mb-1">Username</label>
                <input type="text" id="username" name="username" placeholder="Buat username Anda" required
                       class="input-field w-full px-0 py-2 text-lg focus:ring-0">
            </div>

            <div class="mb-8">
                <label for="password" class="block text-sm font-medium text-slate-300 mb-1">Password</label>
                <input type="password" id="password" name="password" placeholder="Buat password Anda" required
                       class="input-field w-full px-0 py-2 text-lg focus:ring-0">
            </div>

            <button type="submit"
                    class="btn-register w-full py-3 px-4 border border-transparent rounded-xl shadow-sm text-lg font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400 transition duration-150 ease-in-out">
                Daftar
            </button>
        </form>

        <p class="mt-10 text-center text-sm text-slate-400">
            Sudah punya akun?
            <a href="index.php" class="font-medium text-link-blue">
                Login disini
            </a>
        </p>
    </div>

    <style>
      .animation-delay-2000 {
        animation-delay: 2s;
      }
    </style>
</body>
</html>