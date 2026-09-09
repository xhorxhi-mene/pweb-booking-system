<?php
require_once '../script/sessione.php';
session_start();
require_once '../script/connessione.php';
date_default_timezone_set('Europe/Rome');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../script/login.php");
    exit();
}
require_once '../script/controllo_anti_ghost.php';

$u_id = $_SESSION['user_id'];

$query_futuro = "SELECT a.appointment_id, s.name AS servizio, s.duration_minutes, a.start_time
                 FROM appointments a
                 JOIN services s ON a.service_id = s.service_id
                 WHERE a.user_id = ? AND a.start_time > NOW()
                 ORDER BY a.start_time ASC LIMIT 1";
$stmt_f = $conn->prepare($query_futuro);
$stmt_f->bind_param("i", $u_id);
$stmt_f->execute();
$res_futuro = $stmt_f->get_result();

$app_futuro_data = null;
if ($app_futuro = $res_futuro->fetch_assoc()) {
    $start = new DateTime($app_futuro['start_time']);
    $now = new DateTime();
    $secondi_rimanenti = $start->getTimestamp() - $now->getTimestamp();
    
    $app_futuro_data = [
        'appointment_id' => $app_futuro['appointment_id'],
        'servizio' => htmlspecialchars($app_futuro['servizio'], ENT_QUOTES, 'UTF-8'),
        'duration_minutes' => $app_futuro['duration_minutes'],
        'data_formattata' => $start->format('d/m/Y'),
        'ora_formattata' => $start->format('H:i'),
        'puo_cancellare' => ($secondi_rimanenti >= 7200) // true se > 2 ore
    ];
}
$stmt_f->close();

$query_passato = "SELECT a.appointment_id, s.name AS servizio, a.start_time
                  FROM appointments a
                  JOIN services s ON a.service_id = s.service_id
                  WHERE a.user_id = ? AND a.start_time <= NOW()
                  ORDER BY a.start_time DESC";
$stmt_p = $conn->prepare($query_passato);
$stmt_p->bind_param("i", $u_id);
$stmt_p->execute();
$res_passato = $stmt_p->get_result();

$storico_data = [];
while ($row_p = $res_passato->fetch_assoc()) {
    $dt_passato = new DateTime($row_p['start_time']);
    $storico_data[] = [
        'data_formattata' => $dt_passato->format('d/m/Y'),
        'ora_formattata' => $dt_passato->format('H:i'),
        'servizio' => htmlspecialchars($row_p['servizio'], ENT_QUOTES, 'UTF-8')
    ];
}
$stmt_p->close();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La mia Dashboard - Salone</title>
    <link rel="stylesheet" href="../../css/style.css">
    
    <script>
        const appFuturo = <?php echo json_encode($app_futuro_data); ?>;
        const storicoAppuntamenti = <?php echo json_encode($storico_data); ?>;
    </script>
    
    <script defer src="../../js/area_cliente.js"></script>
</head>
<body>
    <header>
        <h1>Bentornato, <?php echo htmlspecialchars($_SESSION['nome']); ?></h1>
        <nav>
            <a href="../../index.php">Home</a> |
            <a href="../script/logout.php" id="logout">Esci</a>
        </nav>
    </header>

    <main>
        <nav id="dashboard-nav" class="dashboard-header">
            <h2>La tua Dashboard</h2>
        </nav>

        <hr>

        <h3>Il tuo prossimo appuntamento</h3>
        <div id="futuro-container"></div>

        <hr style="margin-top: 50px;">

        <h3>Storico Appuntamenti</h3>
        <div id="storico-container" class="admin-window storico-window"></div>
    </main>
    <footer>
        <div class="footer-content">
            <p><a href="../../manuale.html" class="footer-link">Manuale utente</a></p>
            <p>Studente: Xhorxhi Mene - Matricola: 690972</p>
            <p><strong>Gestore Appuntamenti</strong> &copy; 2026 | Progetto Didattico PWEB</p>
        </div>
    </footer>
</body>
</html>