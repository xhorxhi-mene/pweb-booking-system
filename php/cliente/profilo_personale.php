<?php
require_once '../script/sessione.php';
session_start();
require_once '../script/connessione.php';

// logout automatico dopo 30 minuti di inattivita' (Cap. 2 libro)
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

// verifica che l'utente esista ancora nel DB
require_once '../script/controllo_anti_ghost.php';

// Generazione token CSRF (Cap. 3 libro): protegge il form di cambio password
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$u_id = $_SESSION['user_id'];
$messaggio = "";
$tipo_alert = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verifica rigorosa del token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Errore: token di sicurezza non valido. Ricarica la pagina.");
    }

    $old_pass  = $_POST['old_password'];
    $new_pass  = $_POST['new_password'];
    $conf_pass = $_POST['confirm_password'];

    // Recupero la password attuale crittografata dal DB (Prepared Statement)
    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $u_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $user = $res->fetch_assoc();
    $stmt->close();

    // Verifico la password vecchia
    if (!$user || !password_verify($old_pass, $user['password'])) {
        $messaggio = "La password attuale non è corretta.";
        $tipo_alert = "error";
    } elseif ($new_pass !== $conf_pass) {
        $messaggio = "Le nuove password non coincidono.";
        $tipo_alert = "warning";
    } elseif ($old_pass === $new_pass) {
        $messaggio = "La nuova password non puo' essere uguale a quella vecchia";
        $tipo_alert = "warning";
    } else {
        // Validazione Regex (Cap. 2 libro): Minimo 8 caratteri, di cui almeno una lettera e un numero
        if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $new_pass)){
            $messaggio = "Sicurezza debole: la password deve contenere almeno 8 caratteri, includendo almeno una lettera e un numero.";
            $tipo_alert = "warning";
        } else {
            // Hash sicuro della nuova password
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            
            $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $update_stmt->bind_param("si", $new_hash, $u_id);
            
            if ($update_stmt->execute()) {
                $messaggio = "Password aggiornata con successo!";
                $tipo_alert = "success";
                
                // rigenerazione ID sessione (Cap. 2): Cruciale dopo un cambio di credenziali
                session_regenerate_id(true);
                session_write_close();
            } else {
                $messaggio = "Si è verificato un errore di sistema. Riprova.";
                $tipo_alert = "error";
            }
            $update_stmt->close();
        }
    }
}

$stmt_dati = $conn->prepare("SELECT nome, cognome, email, role FROM users WHERE user_id = ?");
$stmt_dati->bind_param("i", $u_id);
$stmt_dati->execute();
$res_dati = $stmt_dati->get_result();
$dati_utente = $res_dati->fetch_assoc();
$stmt_dati->close();

// Sanificazione dati in output per prevenire XSS
$nome_html  = htmlspecialchars($dati_utente['nome'], ENT_QUOTES, 'UTF-8');
$cognome_html = htmlspecialchars($dati_utente['cognome'], ENT_QUOTES, 'UTF-8');
$email_html = htmlspecialchars($dati_utente['email'], ENT_QUOTES, 'UTF-8');
$ruolo_html = htmlspecialchars($dati_utente['role'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Il mio Profilo - Salone</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <header>
        <h1>Il tuo Profilo</h1>
        <nav>
            <a href="../../index.php">Home</a> |
            <?php if ($dati_utente['role'] === 'admin'): ?>
                <a href="../admin/admin_dashboard.php">Dashboard</a> |
            <?php else: ?>
                <a href="area_cliente.php">Dashboard</a> |
            <?php endif; ?>
            <a href="../script/logout.php" id="logout">Esci</a>
        </nav>
    </header>

    <main>
        <div class="profile-container">
            
            <div class="info-card profile-card">
                <h2 class="profile-card-title">Dati Account</h2>
                
                <div class="profile-data-row">
                    <strong class="profile-data-label">Nome Completo</strong>
                    <p class="profile-data-value"><?php echo $nome_html . "<strong> " . $cognome_html; ?> </strong></p>
                </div>
                
                <div class="profile-data-row">
                    <strong class="profile-data-label">Indirizzo Email</strong>
                    <p class="profile-data-value"><?php echo $email_html; ?></p>
                </div>

                <div class="profile-data-row">
                    <strong class="profile-data-label">Tipologia Account</strong>
                    <p class="profile-role-value">
                        <?php echo ($ruolo_html === 'admin') ? 'Amministratore' : 'Cliente'; ?>
                    </p>
                </div>
            </div>

            <div class="reg-form profile-card profile-form-card">
                <h2>Modifica Password</h2>
                
                <?php if ($messaggio): ?>
                    <div class="alert <?php echo $tipo_alert; ?> profile-alert">
                        <?php echo $messaggio; ?>
                    </div>
                <?php endif; ?>

                <form action="profilo_personale.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                    
                    <label for="old_password" class="profile-form-label">Password Attuale</label>
                    <input type="password" id="old_password" name="old_password" class="custom-input" required>
                    
                    <label for="new_password" class="profile-form-label">Nuova Password</label>
                    <input type="password" id="new_password" name="new_password" class="custom-input" required>
                    <p class="profile-form-hint">(Min. 8 caratteri, una lettera, un numero)</p>
                    
                    <label for="confirm_password" class="profile-form-label">Conferma Nuova Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="custom-input" required>
                    
                    <button type="submit" class="btn-select profile-submit-btn">Aggiorna Sicurezza</button>
                </form>
            </div>
            
        </div>
    </main>
    <footer>
        <div class="footer-content">
            <p><a href="../../manuale.html" class="footer-link">Manuale utente</a></p>
            <p>Studente: Xhorxhi Mene - Matricola: 690972</p>
            <p><strong>Gestore Appuntamenti</strong> &copy; 2026 | Progetto Didattico PWEB</p>
        </div>
    </footer>
<script src="../../js/main.js"></script>
</body>
</html>