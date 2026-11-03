<?php 
// Menu lateral. Cada item: clave, texto, ruta ('#' = modulo todavia sin armar)
$Menu = array(
    array('inicio',      'Inicio',      '/views/dashboard/index.php'),
    array('ventas',      'Ventas',      '#'),
    array('productos',   'Productos',   '#'),
    array('proveedores', 'Proveedores', '/views/proveedores/index.php'),
    array('compras',     'Compras',     '#'),
    array('facturas',    'Facturas',    '#'),
    array('usuarios',    'Usuarios',    '#')
);

// la auditoria solo aparece para los roles que pueden verla
if (TienePermiso(RolesVerAuditoria())) {
    $Menu[] = array('auditoria', 'Auditoría', '/views/auditoria/index.php');
}
?>
<aside class="panel-sidebar">

    <div class="panel-logo">
        <span class="logo-gg">G<span class="logo-amp">&amp;</span>G</span>
        <span class="logo-line"></span>
        <span class="logo-sub">MANAGER</span>
    </div>

    <nav class="panel-nav">
        <?php for ($i = 0; $i < count($Menu); $i++) { ?>
            <?php 
            $Clase = '';
            if ($Menu[$i][0] == $MenuActivo) {
                $Clase = 'activo';
            }

            $Href = '#';
            if ($Menu[$i][2] != '#') {
                $Href = BASE_URL . $Menu[$i][2];
            }
            ?>
            <a href="<?php echo $Href; ?>" class="<?php echo $Clase; ?>"><?php echo $Menu[$i][1]; ?></a>
        <?php } ?>
    </nav>

</aside>
