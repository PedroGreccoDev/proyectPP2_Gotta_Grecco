<?php 
session_start();

require_once '../../config/database.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/funciones.php';
require_once '../../helpers/funciones_proveedores.php';

verificarSesion();

$MiConexion = ConexionBD();

$TituloPagina = 'Inicio';
$MenuActivo = 'inicio';

require '../layouts/header.php';
?>

<div class="panel-card">
    <h1>Bienvenido, <?php echo Esc($_SESSION['Usuario_Nombre']); ?></h1>
    <p class="text-muted mb-0">Sesión iniciada correctamente.</p>
</div>

<?php require '../layouts/footer.php'; ?>
