<?php
    require_once 'sessione.php';
    session_start();
    require_once 'connessione.php';

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        die("Accesso negato.");
    }

    $id_cliente = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    if ($id_cliente > 0) {
        $stmt = $conn->prepare("UPDATE users SET no_show_count = no_show_count + 1 WHERE user_id = ?");
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $stmt->close();

        $stmt_bl = $conn->prepare("UPDATE users SET is_blacklisted = 1 WHERE user_id = ? AND no_show_count >= 3");
        $stmt_bl->bind_param("i", $id_cliente);
        $stmt_bl->execute();
        $stmt_bl->close();
    }

    header("Location: ../admin/admin_dashboard.php");
    exit();
?>
