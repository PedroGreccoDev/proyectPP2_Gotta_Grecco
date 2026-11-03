<?php
session_start();
session_destroy();
header('Location: /sdegestionpp2/views/auth/login.php');
exit;