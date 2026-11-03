<?php 

function verificarSesion() {
    if (empty($_SESSION['Usuario_ID'])) {
        header('Location: /sdegestionpp2/views/auth/login.php');
        exit;
    }
}