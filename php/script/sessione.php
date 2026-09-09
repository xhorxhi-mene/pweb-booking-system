<?php
    //Hardening sessione (cap. 2 libro).
    ini_set('session.cookie_httponly', 1);  // Cookie non accessibile da JavaScript
    ini_set('session.use_strict_mode', 1);  // Rifiuta session ID non inizializzati dal server
    ini_set('session.use_only_cookies', 1); // Vieta session ID nella URL
?>
