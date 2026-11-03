<?php 
// Encabezado del panel. La pagina que lo incluye debe haber hecho antes:
// session_start(), require de auth.php y funciones_proveedores.php, y definir $MiConexion
// Variables opcionales: $TituloPagina y $MenuActivo

if (empty($TituloPagina)) {
    $TituloPagina = 'Sistema de Gestión';
}

if (empty($MenuActivo)) {
    $MenuActivo = '';
}

$NombreRol = Obtener_NombreRol($MiConexion, $_SESSION['Usuario_Rol']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo Esc($TituloPagina); ?> | G&amp;G Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/public/assets/css/panel.css" rel="stylesheet">
</head>
<body class="panel-body">

<div class="panel-app">

    <?php require __DIR__ . '/sidebar.php'; ?>

    <main class="panel-main">

        <div class="panel-topbar">
            <span><?php echo Esc($_SESSION['Usuario_Nombre'] . ' ' . $_SESSION['Usuario_Apellido']); ?> · <?php echo Esc($NombreRol); ?></span>
            <a href="<?php echo BASE_URL; ?>/views/auth/logout.php">Cerrar sesión</a>
        </div>
