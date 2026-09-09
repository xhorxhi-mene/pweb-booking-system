<?php

    if (isset($_SESSION['user_id']) && isset($conn)) {
        $check_user_id = $_SESSION['user_id'];
        
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE user_id = ?");
        if ($check_stmt) {
            $check_stmt->bind_param("i", $check_user_id);
            $check_stmt->execute();
            
            $check_res = $check_stmt->get_result();

            if ($check_res->num_rows === 0) {
                // L'utente non esiste più nel database (Ghost Session)
                $check_stmt->close();
                
                session_unset();
                session_destroy();
                
                header("Location: ../script/login.php"); 
                exit();
            }
            $check_stmt->close();
        }
    }
?>