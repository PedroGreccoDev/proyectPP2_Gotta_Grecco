<!-- Modal de confirmacion: sirve para desactivar y para reactivar (lo completa proveedores.js) -->
<div class="modal fade" id="modalBaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="index.php" class="modal-content">
            <div class="modal-body p-4">

                <h5 class="fw-bold" id="baja_titulo">¿Desactivar proveedor?</h5>
                <p class="text-muted mb-4" id="baja_texto"></p>

                <input type="hidden" name="id_proveedor" id="baja_id" value="">
                <input type="hidden" name="accion" id="baja_accion" value="">

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="BotonConfirmar" value="1" id="baja_boton" class="btn btn-danger">Desactivar</button>
                </div>

            </div>
        </form>
    </div>
</div>
