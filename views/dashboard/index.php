<?php
session_start();
require_once '../../helpers/auth.php';
verificarSesion();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h1>Bienvenido, <?php echo $_SESSION['Usuario_Nombre']; ?></h1>
    <p>Sesión iniciada correctamente.</p>
    <a href="../auth/logout.php" class="btn btn-danger">Cerrar sesión</a>
</body>
</html>