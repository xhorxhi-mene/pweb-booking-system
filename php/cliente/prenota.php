<?php
require_once '../script/sessione.php';
session_start();
require_once '../script/connessione.php';
require_once '../script/controllo_anti_ghost.php';

date_default_timezone_set('Europe/Rome');

// Session timeout: logout automatico dopo 30 minuti di inattività (cap. 2 libro)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
    session_unset();
    session_destroy();
    header("Location: ../script/login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../script/login.php");
    exit();
}

$u_id = $_SESSION['user_id'];

$stmt_u = $conn->prepare("SELECT is_blacklisted FROM users WHERE user_id = ?");
$stmt_u->bind_param("i", $u_id);
$stmt_u->execute();
$user_info = $stmt_u->get_result()->fetch_assoc();
$is_blacklisted = $user_info['is_blacklisted'] ?? 0;

$stmt_check = $conn->prepare("SELECT appointment_id FROM appointments WHERE user_id = ? AND start_time > NOW()");
$stmt_check->bind_param("i", $u_id);
$stmt_check->execute();
$ha_prenotazione_attiva = ($stmt_check->get_result()->num_rows > 0);

$permessi_bl = [];
if ($is_blacklisted) {
    $res_p = $conn->query("SELECT DATE_FORMAT(slot_time, '%H:%i') as ora FROM blacklist_allowed_slots");
    while($p = $res_p->fetch_assoc()) {
        $permessi_bl[] = $p['ora'];
    }
}

$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : null;
$mese = isset($_GET['m']) ? (int)$_GET['m'] : (int)date('m');
$anno = isset($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');

$prev_mese = $mese - 1; $prev_anno = $anno;
if ($prev_mese < 1) { $prev_mese = 12; $prev_anno--; }
$next_mese = $mese + 1; $next_anno = $anno;
if ($next_mese > 12) { $next_mese = 1; $next_anno++; }

$nomi_mesi = ["", "Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno",
              "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];

$primo_giorno_ts = mktime(0, 0, 0, $mese, 1, $anno);
$giorni_nel_mese = date('t', $primo_giorno_ts);
$pos_primo_giorno = (int)date('N', $primo_giorno_ts);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Prenotazione - Salone</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>

<header>
    <h1>Salone</h1>
    <nav>
        <p>Benvenuto, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong></p>
        <a href="../../index.php">Home</a> |
        <a href="area_cliente.php" id="current">I Miei Appuntamenti</a> |
        <a href="../script/logout.php" id="logout">Esci</a>
    </nav>
    <script>
        // Variabili base
        const giorniNelMese = <?php echo $giorni_nel_mese; ?>;
        const posPrimoGiorno = <?php echo $pos_primo_giorno; ?>;
        const mese = <?php echo $mese; ?>;
        const anno = <?php echo $anno; ?>;
        const serviceId = <?php echo $service_id ? $service_id : 'null'; ?>;
        const dataOggi = "<?php echo date('Y-m-d'); ?>";
        const oraAttuale = "<?php echo date('H:i'); ?>";

        // Variabili stato utente
        const isBlacklisted = <?php echo $is_blacklisted ? 'true' : 'false'; ?>;
        const permessiBl = <?php echo json_encode($permessi_bl); ?>;
        const haPrenotazioneAttiva = <?php echo $ha_prenotazione_attiva ? 'true' : 'false'; ?>;

        <?php if (isset($_GET['data_scelta'])): ?>
            const dataSelezionata = "<?php echo htmlspecialchars($_GET['data_scelta']); ?>";
            <?php
            // Calcolo della durata del servizio corrente
            $stmt_dur = $conn->prepare("SELECT duration_minutes FROM services WHERE service_id = ?");
            $stmt_dur->bind_param("i", $service_id);
            $stmt_dur->execute();
            $durata_servizio = $stmt_dur->get_result()->fetch_assoc()['duration_minutes'] ?? 30;

            $stmt_occ = $conn->prepare("
                SELECT DATE_FORMAT(a.start_time, '%H:%i') as ora, s.duration_minutes
                FROM appointments a
                JOIN services s ON a.service_id = s.service_id
                WHERE DATE(a.start_time) = ?
            ");
            $stmt_occ->bind_param("s", $_GET['data_scelta']);
            $stmt_occ->execute();
            $res_occ  = $stmt_occ->get_result();
            $occupati = [];
            while ($row_occ = $res_occ->fetch_assoc()) {
                $start_ts = strtotime($row_occ['ora']);
                $durata_app = (int)$row_occ['duration_minutes'];
                $slot_necessari = ceil($durata_app / 30);
                for ($i = 0; $i < $slot_necessari; $i++) {
                    $occupati[] = date('H:i', $start_ts + ($i * 30 * 60));
                }
            }
            ?>
            const orariOccupati = <?php echo json_encode($occupati); ?>;
            const durataServizioAttuale = <?php echo $durata_servizio; ?>;
        <?php else: ?>
            const dataSelezionata = null;
            const orariOccupati = [];
            const durataServizioAttuale = 30;
        <?php endif; ?>
    </script>
    <script src="../../js/prenotazione.js"></script>
</header>

<main class="booking-flow">

    <?php if (!$service_id): ?>
        <section>
            <h2>Cosa desideri fare oggi?</h2>
            <div class="services-list">
                <?php
                $res_servizi = $conn->query("SELECT * FROM services");
                while ($s = $res_servizi->fetch_assoc()): ?>
                    <div class="service-card">
                        <h3><?php echo htmlspecialchars($s['name']); ?></h3>
                        <p><?php echo (int)$s['duration_minutes']; ?> minuti</p>
                        <span class="price"><?php echo htmlspecialchars($s['price']); ?>&euro;</span>
                        <a href="prenota.php?service_id=<?php echo $s['service_id']; ?>" class="btn-select">Scegli questo</a>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

    <?php else: ?>
        <section>
            <div class="selection-summary">
                <p>Servizio scelto: <strong>
                    <?php
                    $stmt_s = $conn->prepare("SELECT name FROM services WHERE service_id = ?");
                    $stmt_s->bind_param("i", $service_id);
                    $stmt_s->execute();
                    $info_s = $stmt_s->get_result()->fetch_assoc();
                    echo htmlspecialchars($info_s['name'] ?? 'Servizio non trovato');
                    ?></strong>
                    (<a href="prenota.php">cambia</a>)
                </p>
                <?php if ($ha_prenotazione_attiva): ?>
                    <div class="alert warning">
                        <strong>Blocco attivo:</strong> Hai gi&agrave; una prenotazione futura. Potrai prenotare di nuovo non appena inizier&agrave; il tuo appuntamento.
                    </div>
                <?php endif; ?>
            </div>

            <h2>Quando vuoi venire?</h2>

            <div class="calendar-nav">
                <a href="prenota.php?service_id=<?php echo $service_id; ?>&m=<?php echo $prev_mese; ?>&y=<?php echo $prev_anno; ?>" class="btn-nav">&lt;</a>
                <span class="calendar-title"><?php echo $nomi_mesi[$mese] . " " . $anno; ?></span>
                <a href="prenota.php?service_id=<?php echo $service_id; ?>&m=<?php echo $next_mese; ?>&y=<?php echo $next_anno; ?>" class="btn-nav">&gt;</a>
            </div>

            <div id="calendario" class="calendar-month-grid" style="display: block;"></div>

        </section>

        <?php if (isset($_GET['data_scelta'])): ?>
            <section class="time-selection">
                <h3>Orari per il <?php echo date('d/m/Y', strtotime($_GET['data_scelta'])); ?></h3>
                <div id="orari-container" class="slots-grid"></div>
                <div id="msg"></div>
            </section>
        <?php endif; ?>

    <?php endif; ?>

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
