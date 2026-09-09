<?php
    require_once 'sessione.php';
    require_once 'connessione.php';
    session_start();

    // Sicurezza: se l'utente è già loggato, blocca l'accesso e rimanda alla home
    if (isset($_SESSION['user_id'])) {
        header("Location: ../../index.php");
        exit();
    }

    // Generazione token CSRF
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $messaggio = "";
    $tipo_messaggio = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Verifica token CSRF
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            die("Errore: token di sicurezza non valido. Ricarica la pagina.");
        }

        $nome = $_POST['nome'];
        $cognome = $_POST['cognome'];
        $telefono = $_POST['phone'];
        $raw_password = $_POST['password'];

        // Validazione email server-side con filter_var
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

        if ($email === false) {
            $messaggio = "Errore: formato email non valido.";
            $tipo_messaggio = "error";
        } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,}$/', $raw_password)) {
            // Validazione Regex: Minimo 8 caratteri, di cui almeno una lettera e un numero
            $messaggio = "Sicurezza debole: la password deve contenere almeno 8 caratteri, includendo almeno una lettera e un numero.";
            $tipo_messaggio = "error";
        } else {
            // Hash sicuro della password
            $password = password_hash($raw_password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (nome, cognome, email, password, phone, role) VALUES (?, ?, ?, ?, ?, 'customer')";
            $stmt = $conn->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sssss", $nome, $cognome, $email, $password, $telefono);

                if ($stmt->execute()) {
                    $nuovo_id = $stmt->insert_id;

                    // Rigenerazione ID sessione al login automatico post-registrazione
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $nuovo_id;
                    $_SESSION['role'] = 'customer';
                    $_SESSION['nome'] = $nome;
                    $_SESSION['last_activity'] = time();
                    
                    // Chiusura sessione: Cruciale per evitare race conditions prima dei redirect
                    session_write_close();

                    $messaggio = "Registrazione completata con successo!";
                    $tipo_messaggio = "success";
                } else {
                    if ($stmt->errno == 1062) {
                        if (strpos($stmt->error, 'email') !== false) {
                            $messaggio = "Errore: Questa email risulta già registrata.";
                        } elseif (strpos($stmt->error, 'phone') !== false) {
                            $messaggio = "Errore: Numero di telefono già associato a un altro account.";
                        } else {
                            $messaggio = "Errore: Email o numero di telefono già presenti nel sistema.";
                        }
                    } else {
                        $messaggio = "Si è verificato un errore tecnico. Riprova più tardi.";
                    }
                    $tipo_messaggio = "error";
                }
                $stmt->close();
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione - Salon Booking Planner</title>
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        input:invalid { border-color: black; }
    </style>
</head>
<body>
<div class="reg-form">

    <?php if ($tipo_messaggio === "success"): ?>
        <div style="text-align:center; padding:20px 0;">
            <h2 style="color:#2ecc71; margin-bottom:20px;">Benvenuto!</h2>
            <!-- Prevenzione XSS sul messaggio -->
            <div class="alert success" style="margin-bottom:20px;">
                <?php echo htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <p style="color:#7f8c8d; font-size:0.9em;">
                Verrai reindirizzato automaticamente alla Home.
            </p>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "../../index.php";
            }, 3000);
        </script>

    <?php else: ?>
        <h1>Crea il tuo Account</h1>

        <?php if ($messaggio): ?>
            <!-- Prevenzione XSS sul messaggio d'errore -->
            <div class="alert error"><?php echo htmlspecialchars($messaggio, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <form action="registrazione.php" method="POST">
            <!-- Token CSRF protetto da XSS -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <div style="display:flex; gap:10px; margin-bottom: 10px;">
                <input type="text" name="nome" placeholder="Nome" class="custom-input"
                       maxlength="40" required
                       pattern="^[A-Za-z\u00C0-\u00FF' \-]{2,40}$"
                       title="Minimo 2 caratteri, solo lettere">
                <input type="text" name="cognome" placeholder="Cognome" class="custom-input"
                       maxlength="40" required
                       pattern="^[A-Za-z\u00C0-\u00FF' \-]{2,40}$"
                       title="Minimo 2 caratteri, solo lettere">
            </div>

            <input type="email" name="email" class="custom-input"
                   placeholder="Email (serve per il login)"
                   maxlength="100" required>

            <input type="tel" name="phone" class="custom-input"
                   placeholder="Numero di Telefono"
                   maxlength="12" required
                   pattern="^[0-9]{7,12}$"
                   title="Da 7 a 12 cifre numeriche">

            <input type="password" name="password" class="custom-input"
                   placeholder="Scegli una Password" required>

            <button type="submit" class="btn-select log">Registrati Ora</button>
        </form>

        <a href="../../index.php" class="btn-select home">Torna alla home</a>

        <p style="text-align:center; margin-top:20px; font-size:0.9em;">
            Hai già un account? <a href="login.php" style="color: #3498db; text-decoration: none; font-weight: bold;">Accedi qui</a>
        </p>

    <?php endif; ?>
</div>

<script src="../../js/main.js"></script>
</body>
</html>