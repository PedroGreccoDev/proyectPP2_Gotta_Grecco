<?php 
session_start();

require_once '../../config/database.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/funciones.php';
require_once '../../helpers/funciones_proveedores.php';

verificarSesion();

$MiConexion = ConexionBD();

// HU-11: solo Compras y Administracion pueden dar de alta
if (!TienePermiso(RolesGestionarProveedores())) {
    $ModuloDenegado = 'el módulo de proveedores';
    require '../errors/401.php';
    exit;
}

if (!empty($_POST['BotonGuardar'])) {

    $Errores = Validar_Proveedor($MiConexion, 0, true);

    if (count($Errores) > 0) {
        // PRG: se guardan los datos y los errores para mostrarlos una sola vez
        $_SESSION['Form_Datos'] = array(
            'razon_social' => Dato_Post('razon_social'),
            'cuit'         => Dato_Post('cuit'),
            'telefono'     => Dato_Post('telefono'),
            'mail'         => Dato_Post('mail'),
            'direccion'    => Dato_Post('direccion')
        );
        $_SESSION['Form_Errores'] = $Errores;
        $_SESSION['Mensaje'] = reset($Errores) . '.';
        $_SESSION['Mensaje_Tipo'] = 'danger';

        header('Location: nuevo.php');
        exit;
    }

    if (Insertar_Proveedor($MiConexion) !== false) {
        $_SESSION['Mensaje'] = 'Proveedor creado con éxito';
        $_SESSION['Mensaje_Tipo'] = 'success';
        header('Location: index.php');
        exit;
    } else {
        $_SESSION['Mensaje'] = 'No se pudo guardar el proveedor. Intentá nuevamente.';
        $_SESSION['Mensaje_Tipo'] = 'danger';
        header('Location: nuevo.php');
        exit;
    }
}

// ------------------------------------------
// Datos del formulario y mensajes (flash)
// ------------------------------------------
$Datos = array(
    'razon_social' => '',
    'cuit'         => '',
    'telefono'     => '',
    'mail'         => '',
    'direccion'    => ''
);

$Errores = array();

if (!empty($_SESSION['Form_Datos'])) {
    $Datos = $_SESSION['Form_Datos'];
    unset($_SESSION['Form_Datos']);
}

if (!empty($_SESSION['Form_Errores'])) {
    $Errores = $_SESSION['Form_Errores'];
    unset($_SESSION['Form_Errores']);
}

$Mensaje = '';
$MensajeTipo = 'success';

if (!empty($_SESSION['Mensaje'])) {
    $Mensaje = $_SESSION['Mensaje'];
    if (!empty($_SESSION['Mensaje_Tipo'])) {
        $MensajeTipo = $_SESSION['Mensaje_Tipo'];
    }
    unset($_SESSION['Mensaje']);
    unset($_SESSION['Mensaje_Tipo']);
}

$ConErrores = '0';
if (count($Errores) > 0) {
    $ConErrores = '1';
}

$TituloPagina = 'Nuevo proveedor';
$MenuActivo = 'proveedores';
$ScriptPagina = 'proveedores.js';

require '../layouts/header.php';
?>

<div class="panel-card panel-card-form">

    <div class="panel-card-head">
        <h1>Nuevo proveedor</h1>
    </div>

    <?php if ($Mensaje != '') { ?>
        <div class="alert alert-<?php echo Esc($MensajeTipo); ?> py-2"><?php echo Esc($Mensaje); ?></div>
    <?php } ?>

    <form method="POST" id="formProveedor" data-errores="<?php echo $ConErrores; ?>" novalidate>

        <div class="mb-3">
            <label class="form-label" for="razon_social">Razón social *</label>
            <input type="text" name="razon_social" id="razon_social" maxlength="255"
                   class="form-control <?php if (isset($Errores['razon_social'])) { echo 'campo-error'; } ?>"
                   value="<?php echo Esc($Datos['razon_social']); ?>">
            <div class="mensaje-error" id="err_razon_social"><?php if (isset($Errores['razon_social'])) { echo Esc($Errores['razon_social']); } ?></div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="cuit">CUIT *</label>
                <input type="text" name="cuit" id="cuit" maxlength="13" placeholder="30-71234567-1"
                       class="form-control <?php if (isset($Errores['cuit'])) { echo 'campo-error'; } ?>"
                       value="<?php echo Esc($Datos['cuit']); ?>">
                <div class="mensaje-error" id="err_cuit"><?php if (isset($Errores['cuit'])) { echo Esc($Errores['cuit']); } ?></div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" maxlength="50"
                       class="form-control <?php if (isset($Errores['telefono'])) { echo 'campo-error'; } ?>"
                       value="<?php echo Esc($Datos['telefono']); ?>">
                <div class="mensaje-error" id="err_telefono"><?php if (isset($Errores['telefono'])) { echo Esc($Errores['telefono']); } ?></div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="mail">Correo electrónico</label>
            <input type="text" name="mail" id="mail" maxlength="255"
                   class="form-control <?php if (isset($Errores['mail'])) { echo 'campo-error'; } ?>"
                   value="<?php echo Esc($Datos['mail']); ?>">
            <div class="mensaje-error" id="err_mail"><?php if (isset($Errores['mail'])) { echo Esc($Errores['mail']); } ?></div>
        </div>

        <div class="mb-4">
            <label class="form-label" for="direccion">Dirección</label>
            <input type="text" name="direccion" id="direccion" maxlength="255"
                   class="form-control <?php if (isset($Errores['direccion'])) { echo 'campo-error'; } ?>"
                   value="<?php echo Esc($Datos['direccion']); ?>">
            <div class="mensaje-error" id="err_direccion"><?php if (isset($Errores['direccion'])) { echo Esc($Errores['direccion']); } ?></div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" name="BotonGuardar" value="1" id="botonGuardar" class="btn btn-verde">Guardar</button>
        </div>

    </form>

</div>

<?php require '../layouts/footer.php'; ?>
