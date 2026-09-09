<?php
session_start();
require_once 'php/script/connessione.php';

$is_logged   = isset($_SESSION['user_id']);
$nome_utente = $is_logged ? $_SESSION['nome'] : "";
$is_admin    = (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestore Appuntamenti- Home</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header>
        <h1>Agenda per parrucchieri</h1>
        <nav>
            <ul>
                <li><a href="index.php" id="current">Home</a>|</li>
                <?php if (!$is_logged): ?>
                    <li><a href="php/script/login.php">Accedi</a>|</li>
                    <li><a href="php/script/registrazione.php">Registrati</a></li>
                <?php elseif ($is_admin): ?>
                    <li><a href="php/cliente/profilo_personale.php">Profilo</a>|</li>
                    <li><a href="php/script/logout.php" id="logout">Esci</a></li>
                <?php else: ?>
                    <li><a href="php/cliente/area_cliente.php">I Miei Appuntamenti</a>|</li>
                    <li><a href="php/cliente/profilo_personale.php">Profilo</a>|</li>
                    <li><a href="php/script/logout.php" id="logout">Esci</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
            <div class="alert success">
                <strong>Ottimo!</strong> La tua prenotazione &egrave; stata registrata con successo.
            </div>
        <?php endif; ?>

        <section id="hero">
            <?php if ($is_logged): ?>
                <h2>Bentornato, <?php echo htmlspecialchars($nome_utente); ?>!</h2>
                <p><?php echo $is_admin
                    ? "Accesso Amministratore garantito. Gestisci il salone da qui."
                    : "Felice di rivederti. Sei pronto per il tuo prossimo look?"; ?>
                </p>
                <br>
            <?php else: ?>
                <h2>Pronto per un nuovo look?</h2>
                <p>Prenota il tuo appuntamento in pochi click, senza attese telefoniche.</p>
                <br>
            <?php endif; ?>

            <?php if ($is_admin): ?>
                <a href="php/admin/admin_dashboard.php" class="btn-prenota">Vai alla Dashboard</a>
            <?php else: ?>
                <a href="php/cliente/prenota.php" class="btn-prenota">Prenota Ora</a>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <p><a href="manuale.html" class="footer-link">Manuale utente</a></p>
            <p>Studente: Xhorxhi Mene - Matricola: 690972</p>
            <p><strong>Gestore Appuntamenti</strong> &copy; 2026 | Progetto Didattico PWEB</p>
        </div>
    </footer>

    <script src="js/main.js"></script>
</body>
</html>
