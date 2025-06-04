<?php
function redirect($url) {
    header("Location: $url");
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function get_user_exams($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM exams");
    $stmt->execute();
    return $stmt->fetchAll();
}
?>
