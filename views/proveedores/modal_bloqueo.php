<?php if (!empty($Bloqueo)) { ?>
<?php 
// texto de cada operacion pendiente (en singular o plural)
$TextoOrdenes = '';
if ($Bloqueo['ORDENES'] > 0) {
    if ($Bloqueo['ORDENES'] == 1) {
        $TextoOrdenes = '1 orden de compra pendiente';
    } else {
        $TextoOrdenes = $Bloqueo['ORDENES'] . ' órdenes de compra pendientes';
    }
    $TextoOrdenes = $TextoOrdenes . ' (' . $Bloqueo['CODIGOS'] . ')';
}

$TextoFacturas = '';
if ($Bloqueo['FACTURAS'] > 0) {
    if ($Bloqueo['FACTURAS'] == 1) {
        $TextoFacturas = '1 factura sin pagar';
    } else {
        $TextoFacturas = $Bloqueo['FACTURAS'] . ' facturas sin pagar';
    }
}
?>
<!-- Modal de bloqueo: proveedores.js lo abre solo cuando data-mostrar="1" -->
<div class="modal fade" id="modalBloqueo" tabindex="-1" aria-hidden="true" data-mostrar="1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body p-4">

                <h5 class="fw-bold text-danger">No se puede desactivar</h5>
                <p class="mb-2">"<?php echo Esc($Bloqueo['NOMBRE']); ?>" tiene operaciones pendientes:</p>

                <ul>
                    <?php if ($TextoOrdenes != '') { ?>
                        <li><?php echo Esc($TextoOrdenes); ?></li>
                    <?php } ?>
                    <?php if ($TextoFacturas != '') { ?>
                        <li><?php echo Esc($TextoFacturas); ?></li>
                    <?php } ?>
                </ul>

                <p class="text-muted">Finalizá esas operaciones antes de darlo de baja.</p>

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-verde" data-bs-dismiss="modal">Entendido</button>
                </div>

            </div>
        </div>
    </div>
</div>
<?php } ?>
