<?php 
// Se incluye desde otras paginas cuando el rol no tiene permiso.
// Requiere: sesion iniciada, auth.php y funciones_proveedores.php cargados, y $MiConexion.
// Opcional: $ModuloDenegado (texto, ej: 'el módulo de proveedores')

http_response_code(403);

if (empty($ModuloDenegado)) {
    $ModuloDenegado = 'este módulo';
}

$TituloPagina = 'Acceso denegado';
$MenuActivo = '';

$NombreRolDenegado = Obtener_NombreRol($MiConexion, $_SESSION['Usuario_Rol']);

require __DIR__ . '/../layouts/header.php';
?>

<div class="panel-card denegado">
    <div class="denegado-icono">!</div>
    <h2>Acceso denegado</h2>
    <p>Tu rol (<?php echo Esc($NombreRolDenegado); ?>) no tiene permisos para <?php echo Esc($ModuloDenegado); ?>.</p>
    <a href="<?php echo BASE_URL; ?>/views/dashboard/index.php" class="btn btn-verde">Volver al inicio</a>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
