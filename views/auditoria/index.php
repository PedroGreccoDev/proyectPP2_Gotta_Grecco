<?php 
session_start();

require_once '../../config/database.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/funciones.php';
require_once '../../helpers/funciones_proveedores.php';

verificarSesion();

$MiConexion = ConexionBD();

// solo Administracion y Gerente consultan la auditoria
if (!TienePermiso(RolesVerAuditoria())) {
    $ModuloDenegado = 'el módulo de auditoría';
    require '../errors/401.php';
    exit;
}

$Registros = Listar_Auditoria($MiConexion, 'proveedores');

$TituloPagina = 'Auditoría';
$MenuActivo = 'auditoria';

require '../layouts/header.php';
?>

<div class="panel-card">

    <div class="panel-card-head">
        <h1>Auditoría · Proveedores</h1>
    </div>

    <div class="table-responsive">
        <table class="table tabla-panel align-middle">
            <thead>
                <tr>
                    <th>Fecha y hora</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Proveedor</th>
                    <th>Campo</th>
                    <th>Anterior → Nuevo</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($Registros) == 0) { ?>
                    <tr>
                        <td colspan="6" class="vacio">Todavía no hay cambios registrados</td>
                    </tr>
                <?php } else { ?>
                    <?php for ($i = 0; $i < count($Registros); $i++) { ?>
                        <?php 
                        // "—" cuando no hay valor; en el alta solo se muestra el valor nuevo
                        $Campo = '—';
                        if ($Registros[$i]['CAMPO'] != '') {
                            $Campo = $Registros[$i]['CAMPO'];
                        }

                        $Anterior = '—';
                        if ($Registros[$i]['ANTERIOR'] != '') {
                            $Anterior = $Registros[$i]['ANTERIOR'];
                        }

                        $Nuevo = '—';
                        if ($Registros[$i]['NUEVO'] != '') {
                            $Nuevo = $Registros[$i]['NUEVO'];
                        }

                        if ($Registros[$i]['ANTERIOR'] == '' && $Registros[$i]['CAMPO'] == '') {
                            $Cambio = $Nuevo;
                        } else {
                            $Cambio = $Anterior . ' → ' . $Nuevo;
                        }
                        ?>
                        <tr>
                            <td><?php echo Esc(date('d/m/Y H:i', strtotime($Registros[$i]['FECHA']))); ?></td>
                            <td><?php echo Esc($Registros[$i]['USUARIO']); ?></td>
                            <td><?php echo Esc($Registros[$i]['ACCION']); ?></td>
                            <td><?php echo Esc($Registros[$i]['PROVEEDOR']); ?></td>
                            <td><?php echo Esc($Campo); ?></td>
                            <td><?php echo Esc($Cambio); ?></td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <small class="text-muted">Se muestran los últimos 200 movimientos.</small>

</div>

<?php require '../layouts/footer.php'; ?>
