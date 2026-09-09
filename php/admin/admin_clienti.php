<?php
require_once '../script/sessione.php';
session_start();
require_once '../script/connessione.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit();
}

// Gestione eliminazione cliente via AJAX (fetch da admin_clienti.js)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user_id'])) {
    $delete_id = intval($_POST['delete_user_id']);
    $stmt_del  = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'customer'");
    $stmt_del->bind_param("i", $delete_id);
    echo $stmt_del->execute() ? "OK" : "ERROR";
    $stmt_del->close();
    exit();
}

$query = "SELECT nome, cognome, phone, email, no_show_count, is_blacklisted, user_id
          FROM users
          WHERE role = 'customer'
          ORDER BY cognome ASC";
$res = $conn->query($query);

$clienti = [];
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $row['nome'] = htmlspecialchars($row['nome'], ENT_QUOTES, 'UTF-8');
        $row['cognome'] = htmlspecialchars($row['cognome'], ENT_QUOTES, 'UTF-8');
        $row['phone'] = htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8');
        $row['email'] = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
        $clienti[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anagrafica Clienti - Salone</title>
    <link rel="stylesheet" href="../../css/style.css">
    <script>
        const clienti = <?php echo json_encode($clienti); ?>;
    </script>
    <script src="../../js/admin_clienti.js"></script>
</head>
<body>
    <header>
        <h1 id="client">Anagrafica Clienti</h1>
        <nav>
            <a href="admin_dashboard.php">&#8592; Dashboard</a> |
            <a href="../../index.php">Home</a> |
            <a href="admin_blacklist.php" id="blist">Gestione Blacklist</a>
        </nav>
    </header>

    <main>
        <section>
            <h2>Elenco Clienti Registrati</h2>
            <div id="interaction">
                <input type="text" id="search-bar" placeholder="Cerca per nome, telefono o email...">
                <div id="service-box">
                    <label for="service">Filtra: </label>
                    <select id="service">
                        <option value="alpha">Ordine Alfabetico</option>
                        <option value="recent">Data Registrazione</option>
                        <option value="blacklist">Solo in Blacklist</option>
                        <option value="attivi">Solo Attivi</option>
                    </select>
                </div>
            </div>
            <div id="clienti-table-container" class="admin-window"></div>
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
