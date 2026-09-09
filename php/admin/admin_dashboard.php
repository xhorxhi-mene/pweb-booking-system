<?php
require_once '../script/sessione.php';
session_start();
require_once '../script/connessione.php';

// Session timeout: logout automatico dopo 30 minuti di inattività (cap. 2 libro)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
    session_unset();
    session_destroy();
    header("Location: ../script/login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

$query = "SELECT a.appointment_id, u.user_id, u.nome, u.cognome, u.phone, u.no_show_count,
                 s.name as servizio, a.start_time, a.end_time
          FROM appointments a
          JOIN users u ON a.user_id = u.user_id
          JOIN services s ON a.service_id = s.service_id
          ORDER BY a.start_time ASC";

$risultato = $conn->query($query);

$appointments = [];
if ($risultato && $risultato->num_rows > 0) {
    while ($row = $risultato->fetch_assoc()) {
        $row['date_formatted'] = date('d/m/Y', strtotime($row['start_time']));
        $row['time_formatted'] = date('H:i', strtotime($row['start_time']));
        $row['nome']     = htmlspecialchars($row['nome'],     ENT_QUOTES, 'UTF-8');
        $row['cognome']  = htmlspecialchars($row['cognome'],  ENT_QUOTES, 'UTF-8');
        $row['phone']    = htmlspecialchars($row['phone'],    ENT_QUOTES, 'UTF-8');
        $row['servizio'] = htmlspecialchars($row['servizio'], ENT_QUOTES, 'UTF-8');
        $appointments[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pannello Admin - Gestione Salone</title>
    <link rel="stylesheet" href="../../css/style.css">
    <script>
        const prenotazioni = <?php echo json_encode($appointments); ?>;
    </script>
    <script src="../../js/admin_dashboard.js"></script>
</head>
<body>
    <header>
        <h1>Dashboard Amministratore</h1>
        Benvenuto, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong>
        <nav>
            <a href="../../index.php">Home</a> |
            <a href="../script/logout.php" id="logout">Esci</a>
        </nav>
    </header>

    <main>
        <section>
            <h2>Appuntamenti in programma</h2>
            <div id="interaction" class="interaction-bar">
                <input type="text" id="search-bar" class="search-input custom-input" placeholder="Cerca per cliente o telefono...">
                
                <div id="time-box" class="filter-box">
                    <label for="time-filter">Periodo:</label>
                    <select id="time-filter" class="custom-input">
                        <option value="tutti">Tutti</option>
                        <option value="oggi" selected>Solo Oggi</option>
                        <option value="futuri">Prossimi (Futuri)</option>
                        <option value="passati">Storico (Passati)</option>
                    </select>
                </div>

                <div id="date-box" class="filter-box">
                    <label for="date-filter">Giorno:</label>
                    <input type="date" id="date-filter" class="custom-input">
                </div>

                <div id="service-box" class="filter-box">
                    <label for="service">Servizio: </label>
                    <select id="service" class="custom-input">
                        <option>Tutti</option>
                        <option>Taglio classico</option>
                        <option>Barba</option>
                        <option>Colore</option>
                    </select>
                </div>
            </div>
            <div id="admin-table-container" class="admin-window"></div>
        </section>

        <section class="admin-btn-section">
            <h3>Strumenti di Gestione</h3>
            <div id="admin_d">
                <a href="admin_clienti.php" class="btn-select anagrafica">Anagrafica Clienti</a>
                <a href="admin_blacklist.php" class="btn-select blacklist">Configura Blacklist</a>
            </div>
        </section>
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
