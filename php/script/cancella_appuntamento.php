<?php
    require_once 'sessione.php';
    session_start();
    require_once 'connessione.php';

    if (!isset($_SESSION['user_id'])) {
        die("Accesso negato.");
    }

    $id_app   = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    $user_id  = $_SESSION['user_id'];
    $is_admin = ($_SESSION['role'] === 'admin');

    $stmt = $conn->prepare("SELECT user_id, start_time FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $id_app);
    $stmt->execute();
    $app = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$app) {
        die("Appuntamento non trovato.");
    }

    if (!$is_admin) {
        if ($app['user_id'] != $user_id) {
            die("Non puoi cancellare appuntamenti altrui.");
        }

        $ora_inizio  = new DateTime($app['start_time']);
        $ora_attuale = new DateTime();

        // Calcolo in secondi: evita il bug di $diff->h che ignora i minuti
        $secondi_rimanenti = $ora_inizio->getTimestamp() - $ora_attuale->getTimestamp();

        if ($secondi_rimanenti < 7200) {
            die("Errore: Puoi cancellare solo fino a 2 ore prima dell'inizio.");
        }
    }

    $del = $conn->prepare("DELETE FROM appointments WHERE appointment_id = ?");
    $del->bind_param("i", $id_app);
    $del->execute();
    $del->close();

    $redirect = $is_admin ? "../admin/admin_dashboard.php" : "../cliente/area_cliente.php";
    header("Location: $redirect?status=deleted");
    exit();
?>
