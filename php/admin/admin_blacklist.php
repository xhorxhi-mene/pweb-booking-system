<?php
require_once '../script/sessione.php';
session_start();
require_once '../script/connessione.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_slots'])) {
    $conn->query("DELETE FROM blacklist_allowed_slots");
    if (isset($_POST['slots'])) {
        $stmt_ins = $conn->prepare("INSERT INTO blacklist_allowed_slots (slot_time) VALUES (?)");
        foreach ($_POST['slots'] as $s) {
            if (preg_match('/^\d{2}:\d{2}$/', $s)) {
                $stmt_ins->bind_param("s", $s);
                $stmt_ins->execute();
            }
        }
        $stmt_ins->close();
    }
}

$cattivi = $conn->query("SELECT user_id, nome, cognome, email, phone, no_show_count FROM users WHERE is_blacklisted = 1");
$blacklisted_users = [];
if ($cattivi && $cattivi->num_rows > 0) {
    while ($row = $cattivi->fetch_assoc()) {
        $row['nome'] = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
        $row['cognome'] = htmlspecialchars($row['cognome'], ENT_QUOTES, 'UTF-8');
        $row['email'] = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
        $row['phone'] = htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8');
        $blacklisted_users[] = $row;
    }
}

$res_permessi = $conn->query("SELECT slot_time FROM blacklist_allowed_slots");
$permessi = [];
while ($p = $res_permessi->fetch_assoc()) {
    $permessi[] = substr($p['slot_time'], 0, 5);
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Blacklist - Salone</title>
    <link rel="stylesheet" href="../../css/style.css">
    <script>
        const blacklistedUsers = <?php echo json_encode($blacklisted_users); ?>;
    </script>
    <script src="../../js/admin_blacklist.js"></script>
</head>
<body>
    <header>
        <h1 id="blist">Gestione Restrizioni Blacklist</h1>
        <nav>
            <a href="admin_dashboard.php">&#8592; Dashboard</a> |
            <a href="../../index.php">Home</a> |
            <a href="admin_clienti.php" id="client">Anagrafica Clienti</a>
        </nav>
    </header>

    <main class="blacklist-layout">
        <section class="blacklist-table-section">
            <h2>Utenti in Blacklist</h2>
            <div id="interaction">
                <input type="text" id="search-bar" placeholder="Cerca utente per nome, cognome, email o telefono...">
            </div>
            <div id="blacklist-table-container" class="admin-window"></div>
        </section>

        <section class="blacklist-form-section">
            <h2>Orari Permessi</h2>
            <p>Seleziona gli slot visibili agli utenti con accesso limitato:</p>
            <form method="POST" action="admin_blacklist.php">
                <div class="checkbox-grid">
                    <?php
                    for ($h = 9; $h < 19; $h++) {
                        foreach (["00", "30"] as $m) {
                            $ts      = sprintf("%02d:%s", $h, $m);
                            $checked = in_array($ts, $permessi) ? "checked" : "";
                            echo "<label class='checkbox-item'>"
                               . "<input type='checkbox' name='slots[]' value='$ts' $checked> $ts"
                               . "</label>";
                        }
                    }
                    ?>
                </div>
                <button type="submit" name="update_slots" class="btn-select permises">
                    Salva Impostazioni
                </button>
            </form>
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
