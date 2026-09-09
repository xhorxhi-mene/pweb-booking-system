<?php
    require_once 'sessione.php';
    session_start();
    require_once 'connessione.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../index.php");
        exit();
    }

    $id   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $stmt = $conn->prepare("UPDATE users SET is_blacklisted = 0, no_show_count = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ../admin/admin_blacklist.php");
    exit();
?>
