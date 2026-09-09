<?php
require_once 'sessione.php';
require_once 'connessione.php';
session_start();

// Sicurezza: se l'utente è già loggato, blocca l'accesso al login e rimanda alla home
if (isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}

// Generazione token CSRF (cap. 3 libro): protegge il form da attacchi Cross-Site Request Forgery
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Verifica rigorosa del token CSRF[cite: 2]
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Errore: token di sicurezza non valido. Ricarica la pagina.");
    }

    // Validazione email server-side con filter_var (cap. 2 libro)
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $pass  = $_POST['password'];

    if ($email === false) {
        $errore = "Formato email non valido.";
    } else {
        // Prepared statement per prevenire SQL Injection: il DB tratta il dato come testo[cite: 2]
        $stmt = $conn->prepare("SELECT user_id, nome, cognome, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($pass, $user['password'])) {
                // Rigenerazione ID sessione al login (cap. 2 libro): previene session fixation[cite: 2]
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['nome']    = $user['nome'];
                $_SESSION['cognome'] = $user['cognome'];
                $_SESSION['role']    = $user['role'];
                $_SESSION['last_activity'] = time();

                // Chiusura sessione: Cruciale per evitare race conditions prima del redirect[cite: 2]
                session_write_close();

                header("Location: ../../index.php");
                exit();
            } else {
                // Messaggio generico: non rivela se l'email esiste (security by design)
                $errore = "Dati non corretti.";
            }
        } else {
            $errore = "Dati non corretti.";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Salone</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        input:invalid { border-color: black; }
    </style>
</head>
<body>
    <div class="reg-form">
        <h1>Accesso al Salone</h1>

        <?php if ($errore): ?>
            <!-- Sanificazione avanzata in output per prevenire XSS -->
            <div class="alert error"><?php echo htmlspecialchars($errore, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <!-- Token CSRF nascosto nel form con htmlspecialchars  -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <input type="email" name="email" placeholder="Email" class="custom-input" required>
            <input type="password" name="password" placeholder="Password" class="custom-input" required>
            
            <button type="submit" class="btn-select log">Accedi</button>
        </form>

        <a href="../../index.php" class="btn-select home">Torna alla home</a>

        <p style="text-align:center; margin-top:20px; font-size:0.9em;">
            Non hai un account? <a href="registrazione.php" style="color: #3498db; text-decoration: none; font-weight: bold;">Registrati qui</a>
        </p>
    </div>
</body>
</html>