<?php
    require_once '../script/sessione.php';
    session_start();
    require_once '../script/connessione.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: ../script/login.php");
        exit();
    }

    require_once '../script/controllo_anti_ghost.php';


    $user_id    = $_SESSION['user_id'];
    $service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;
    $data       = isset($_GET['data']) ? $_GET['data'] : null;
    $ora        = isset($_GET['ora'])  ? $_GET['ora']  : null;

    if (!$service_id || !$data || !$ora) {
        die("Errore: Dati della prenotazione incompleti.");
    }

    // Validazione formato input (cap. 2 libro - regex come allowed list)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data)) {
        die("Errore: Formato data non valido.");
    }
    if (!preg_match('/^\d{2}:\d{2}$/', $ora)) {
        die("Errore: Formato ora non valido.");
    }

    $stmt_service = $conn->prepare("SELECT duration_minutes FROM services WHERE service_id = ?");
    $stmt_service->bind_param("i", $service_id);
    $stmt_service->execute();
    $service_data = $stmt_service->get_result()->fetch_assoc();
    $stmt_service->close();

    if (!$service_data) {
        die("Errore: Servizio non trovato.");
    }

    $start_dt = new DateTime($data . " " . $ora . ":00");
    $end_dt   = clone $start_dt;
    $end_dt->modify("+" . $service_data['duration_minutes'] . " minutes");

    $start_time = $start_dt->format('Y-m-d H:i:s');
    $end_time   = $end_dt->format('Y-m-d H:i:s');

    $stmt_check = $conn->prepare(
        "SELECT appointment_id FROM appointments
        WHERE DATE(start_time) = ? AND DATE_FORMAT(start_time, '%H:%i') = ?"
    );
    $stmt_check->bind_param("ss", $data, $ora);
    $stmt_check->execute();
    $gia_occupato = ($stmt_check->get_result()->num_rows > 0);
    $stmt_check->close();

    if ($gia_occupato) {
        die("Errore: Lo slot selezionato &egrave; stato appena occupato. Torna indietro e scegli un altro orario.");
    }

    $stmt_insert = $conn->prepare("INSERT INTO appointments (user_id, service_id, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("iiss", $user_id, $service_id, $start_time, $end_time);

    if ($stmt_insert->execute()) {
        header("Location: ../../index.php?status=success");
    } else {
        echo "Errore durante il salvataggio: " . htmlspecialchars($conn->error);
    }

    $stmt_insert->close();
    $conn->close();
    exit();
?>