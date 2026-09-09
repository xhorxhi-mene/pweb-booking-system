<?php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "mene_690972";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Errore di connessione: " . $conn->connect_error);
    }

    if (!$conn->set_charset("utf8mb4")) {
        printf("Errore charset utf8mb4: %s\n", $conn->error);
    }
?>
