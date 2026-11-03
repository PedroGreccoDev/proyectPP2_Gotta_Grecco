<?php 
session_start();

require_once '../../config/database.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/funciones.php';
require_once '../../helpers/funciones_proveedores.php';

verificarSesion();

$MiConexion = ConexionBD();

// HU-11: Compras, Administracion y Gerente pueden abrir la pantalla; el Gerente la ve en solo lectura
if (!TienePermiso(RolesVerProveedores())) {
    $ModuloDenegado = 'el módulo de proveedores';
    require '../errors/401.php';
    exit;
}

$PuedeGestionar = TienePermiso(RolesGestionarProveedores());

$IdProveedor = 0;
if (isset($_GET['id'])) {
    $IdProveedor = intval($_GET['id']);
}

$Proveedor = Obtener_Proveedor($MiConexion, $IdProveedor);

if (empty($Proveedor)) {
    $_SESSION['Mensaje'] = 'El proveedor no existe';
    $_SESSION['Mensaje_Tipo'] = 'danger';
    header('Location: index.php');
    exit;
}

// un proveedor inactivo no se edita: primero hay que reactivarlo
if ($PuedeGestionar && $Proveedor['ACTIVO'] == 0) {
    $_SESSION['Mensaje'] = 'Un proveedor inactivo no se puede editar. Reactivalo desde el listado.';
    $_SESSION['Mensaje_Tipo'] = 'warning';
    header('Location: index.php?estado=inactivos');
    exit;
}

if (!empty($_POST['BotonGuardar'])) {

    // HU-11: el servidor tambien verifica el rol
    if (!$PuedeGestionar) {
        $ModuloDenegado = 'el módulo de proveedores';
        require '../errors/401.php';
        exit;
    }

    // HU-08 escenario 2: el CUIT es inmutable aunque el dato llegue por fuera de la pantalla
    $CuitEnviado = LimpiarCUIT(Dato_Post('cuit'));

    if ($CuitEnviado != $Proveedor['CUIT']) {
        $_SESSION['Mensaje'] = 'El CUIT no puede modificarse';
        $_SESSION['Mensaje_Tipo'] = 'danger';
        header('Location: editar.php?id=' . $IdProveedor);
        exit;
    }

    $Errores = Validar_Proveedor($MiConexion, $IdProveedor, false);

    if (count($Errores) > 0) {
        $_SESSION['Form_Datos'] = array(
            'razon_social' => Dato_Post('razon_social'),
            'telefono'     => Dato_Post('telefono'),
            'mail'         => Dato_Post('mail'),
            'direccion'    => Dato_Post('direccion')
        );
        $_SESSION['Form_Errores'] = $Errores;
        $_SESSION['Mensaje'] = reset($Errores) . '.';
        $_SESSION['Mensaje_Tipo'] = 'danger';
    } else {
        $Resultado = Actualizar_Proveedor($MiConexion, $IdProveedor, $Proveedor);

        if ($Resultado === false) {
            $_SESSION['Mensaje'] = 'No se pudieron guardar los cambios';
            $_SESSION['Mensaje_Tipo'] = 'danger';
        } elseif ($Resultado == 0) {
            $_SESSION['Mensaje'] = 'No hay cambios para guardar';
            $_SESSION['Mensaje_Tipo'] = 'info';
        } else {
            $_SESSION['Mensaje'] = 'Cambios guardados.';
            $_SESSION['Mensaje_Tipo'] = 'success';
        }
    }

    header('Location: editar.php?id=' . $IdProveedor);
    exit;
}

// ------------------------------------------
// Datos del formulario y mensajes (flash)
// ------------------------------------------
$Datos = array(
    'razon_social' => $Proveedor['RAZON_SOCIAL'],
    'telefono'     => $Proveedor['TELEFONO'],
    'mail'         => $Proveedor['MAIL'],
    'direccion'    => $Proveedor['DIRECCION']
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

// en modo solo lectura (Gerente) los campos no se pueden tocar
$Lectura = '';
$TituloForm = 'Editar proveedor';
if (!$PuedeGestionar) {
    $Lectura = 'readonly';
    $TituloForm = 'Detalle de proveedor';
}

$TituloPagina = $TituloForm;
$MenuActivo = 'proveedores';
$ScriptPagina = 'proveedores.js';

require '../layouts/header.php';
?>

<div class="panel-card panel-card-form">

    <div class="panel-card-head">
        <h1><?php echo $TituloForm; ?></h1>
        <?php if ($Proveedor['ACTIVO'] == 0) { ?>
            <span class="badge badge-inactivo">Inactivo</span>
        <?php } ?>
    </div>

    <?php if ($Mensaje != '') { ?>
        <div class="alert alert-<?php echo Esc($MensajeTipo); ?> py-2"><?php echo Esc($Mensaje); ?></div>
    <?php } ?>

    <form method="POST" <?php if ($PuedeGestionar) { echo 'id="formProveedor"'; } ?> data-errores="<?php echo $ConErrores; ?>" novalidate>

        <div class="mb-3">
            <label class="form-label" for="razon_social">Razón social *</label>
            <input type="text" name="razon_social" id="razon_social" maxlength="255" <?php echo $Lectura; ?>
                   class="form-control <?php if (isset($Errores['razon_social'])) { echo 'campo-error'; } ?>"
                   value="<?php echo Esc($Datos['razon_social']); ?>">
            <div class="mensaje-error" id="err_razon_social"><?php if (isset($Errores['razon_social'])) { echo Esc($Errores['razon_social']); } ?></div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label" for="cuit">CUIT * &#128274;</label>
                <input type="text" name="cuit" id="cuit" readonly
                       class="form-control campo-bloqueado"
                       value="<?php echo Esc(FormatearCUIT($Proveedor['CUIT'])); ?>">
                <div class="form-text">El CUIT no puede modificarse</div>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label" for="telefono">Teléfono</label>
                <input type="text" name="telefono" id="telefono" maxlength="50" <?php echo $Lectura; ?>
                       class="form-control <?php if (isset($Errores['telefono'])) { echo 'campo-error'; } ?>"
                       value="<?php echo Esc($Datos['telefono']); ?>">
                <div class="mensaje-error" id="err_telefono"><?php if (isset($Errores['telefono'])) { echo Esc($Errores['telefono']); } ?></div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label" for="mail">Correo electrónico</label>
            <input type="text" name="mail" id="mail" maxlength="255" <?php echo $Lectura; ?>
                   class="form-control <?php if (isset($Errores['mail'])) { echo 'campo-error'; } ?>"
                   value="<?php echo Esc($Datos['mail']); ?>">
            <div class="mensaje-error" id="err_mail"><?php if (isset($Errores['mail'])) { echo Esc($Errores['mail']); } ?></div>
        </div>

        <div class="mb-4">
            <label class="form-label" for="direccion">Dirección</label>
            <input type="text" name="direccion" id="direccion" maxlength="255" <?php echo $Lectura; ?>
                   class="form-control <?php if (isset($Errores['direccion'])) { echo 'campo-error'; } ?>"
                   value="<?php echo Esc($Datos['direccion']); ?>">
            <div class="mensaje-error" id="err_direccion"><?php if (isset($Errores['direccion'])) { echo Esc($Errores['direccion']); } ?></div>
        </div>

        <div class="d-flex justify-content-end gap-2">
            <?php if ($PuedeGestionar) { ?>
                <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" name="BotonGuardar" value="1" id="botonGuardar" class="btn btn-verde">Guardar cambios</button>
            <?php } else { ?>
                <a href="index.php" class="btn btn-outline-secondary">Volver al listado</a>
            <?php } ?>
        </div>

    </form>

</div>

<?php require '../layouts/footer.php'; ?>
