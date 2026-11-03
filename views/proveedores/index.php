<?php 
session_start();

require_once '../../config/database.php';
require_once '../../helpers/auth.php';
require_once '../../helpers/funciones.php';
require_once '../../helpers/funciones_proveedores.php';

verificarSesion();

$MiConexion = ConexionBD();

// HU-11: solo Compras, Administracion y Gerente entran al modulo
if (!TienePermiso(RolesVerProveedores())) {
    $ModuloDenegado = 'el módulo de proveedores';
    require '../errors/401.php';
    exit;
}

$PuedeGestionar = TienePermiso(RolesGestionarProveedores());

// ------------------------------------------
// HU-09: baja logica / reactivacion (viene del modal de confirmacion)
// ------------------------------------------
if (!empty($_POST['BotonConfirmar'])) {

    // HU-11: el servidor tambien verifica el rol, no alcanza con ocultar los botones
    if (!$PuedeGestionar) {
        $ModuloDenegado = 'el módulo de proveedores';
        require '../errors/401.php';
        exit;
    }

    $IdProveedor = 0;
    if (isset($_POST['id_proveedor'])) {
        $IdProveedor = intval($_POST['id_proveedor']);
    }

    $Accion = '';
    if (isset($_POST['accion'])) {
        $Accion = $_POST['accion'];
    }

    $Proveedor = Obtener_Proveedor($MiConexion, $IdProveedor);

    if (empty($Proveedor)) {

        $_SESSION['Mensaje'] = 'El proveedor no existe';
        $_SESSION['Mensaje_Tipo'] = 'danger';

    } elseif ($Accion == 'desactivar') {

        if ($Proveedor['ACTIVO'] == 0) {
            $_SESSION['Mensaje'] = 'El proveedor ya estaba inactivo';
            $_SESSION['Mensaje_Tipo'] = 'warning';
        } else {
            $Pendientes = Operaciones_Pendientes_Proveedor($MiConexion, $IdProveedor);

            if ($Pendientes['ORDENES'] > 0 || $Pendientes['FACTURAS'] > 0) {
                // escenario 2: se bloquea la baja y se muestra el detalle en un modal
                $_SESSION['Bloqueo'] = array(
                    'NOMBRE'   => $Proveedor['RAZON_SOCIAL'],
                    'ORDENES'  => $Pendientes['ORDENES'],
                    'CODIGOS'  => $Pendientes['CODIGOS'],
                    'FACTURAS' => $Pendientes['FACTURAS']
                );
            } else {
                if (Cambiar_Estado_Proveedor($MiConexion, $IdProveedor, 0)) {
                    $_SESSION['Mensaje'] = '"' . $Proveedor['RAZON_SOCIAL'] . '" fue desactivado.';
                    $_SESSION['Mensaje_Tipo'] = 'success';
                } else {
                    $_SESSION['Mensaje'] = 'No se pudo desactivar el proveedor';
                    $_SESSION['Mensaje_Tipo'] = 'danger';
                }
            }
        }

    } elseif ($Accion == 'reactivar') {

        if ($Proveedor['ACTIVO'] == 1) {
            $_SESSION['Mensaje'] = 'El proveedor ya estaba activo';
            $_SESSION['Mensaje_Tipo'] = 'warning';
        } else {
            if (Cambiar_Estado_Proveedor($MiConexion, $IdProveedor, 1)) {
                $_SESSION['Mensaje'] = '"' . $Proveedor['RAZON_SOCIAL'] . '" fue reactivado.';
                $_SESSION['Mensaje_Tipo'] = 'success';
            } else {
                $_SESSION['Mensaje'] = 'No se pudo reactivar el proveedor';
                $_SESSION['Mensaje_Tipo'] = 'danger';
            }
        }

    }

    // PRG: se vuelve al listado mostrando todos, asi se ve el cambio de estado
    header('Location: index.php?estado=todos');
    exit;
}

// ------------------------------------------
// Mensajes flash (se muestran una sola vez)
// ------------------------------------------
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

$Bloqueo = array();
if (!empty($_SESSION['Bloqueo'])) {
    $Bloqueo = $_SESSION['Bloqueo'];
    unset($_SESSION['Bloqueo']);
}

// ------------------------------------------
// HU-07: busqueda, filtro, orden y paginacion
// ------------------------------------------
$Busqueda = '';
if (isset($_GET['q'])) {
    $Busqueda = trim(strip_tags($_GET['q']));
}

$Estado = 'activos';
if (isset($_GET['estado'])) {
    if ($_GET['estado'] == 'inactivos' || $_GET['estado'] == 'todos') {
        $Estado = $_GET['estado'];
    }
}

$Orden = 'razon_asc';
if (isset($_GET['orden'])) {
    if ($_GET['orden'] == 'razon_desc') {
        $Orden = 'razon_desc';
    }
}

$PorPagina = 10;
$Total = Contar_Proveedores($MiConexion, $Busqueda, $Estado);

$TotalPaginas = ceil($Total / $PorPagina);
if ($TotalPaginas < 1) {
    $TotalPaginas = 1;
}

$Pagina = 1;
if (isset($_GET['pagina'])) {
    $Pagina = intval($_GET['pagina']);
}
if ($Pagina < 1) {
    $Pagina = 1;
}
if ($Pagina > $TotalPaginas) {
    $Pagina = $TotalPaginas;
}

$Proveedores = Listar_Proveedores($MiConexion, $Busqueda, $Estado, $Orden, $Pagina, $PorPagina);

$TituloPagina = 'Proveedores';
$MenuActivo = 'proveedores';
$ScriptPagina = 'proveedores.js';

require '../layouts/header.php';
?>

<div class="panel-card">

    <div class="panel-card-head">
        <h1>Proveedores</h1>
        <?php if ($PuedeGestionar) { ?>
            <a href="nuevo.php" class="btn btn-verde">+ Nuevo proveedor</a>
        <?php } ?>
    </div>

    <?php if ($Mensaje != '') { ?>
        <div class="alert alert-<?php echo Esc($MensajeTipo); ?> py-2"><?php echo Esc($Mensaje); ?></div>
    <?php } ?>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control" placeholder="Buscar por razón social o CUIT..." value="<?php echo Esc($Busqueda); ?>">
        </div>
        <div class="col-md-3">
            <select name="estado" class="form-select" onchange="this.form.submit()">
                <option value="activos" <?php if ($Estado == 'activos') { echo 'selected'; } ?>>Estado: Activos</option>
                <option value="inactivos" <?php if ($Estado == 'inactivos') { echo 'selected'; } ?>>Estado: Inactivos</option>
                <option value="todos" <?php if ($Estado == 'todos') { echo 'selected'; } ?>>Estado: Todos</option>
            </select>
        </div>
        <div class="col-md-3">
            <select name="orden" class="form-select" onchange="this.form.submit()">
                <option value="razon_asc" <?php if ($Orden == 'razon_asc') { echo 'selected'; } ?>>Orden: Razón social A-Z</option>
                <option value="razon_desc" <?php if ($Orden == 'razon_desc') { echo 'selected'; } ?>>Orden: Razón social Z-A</option>
            </select>
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-outline-secondary w-100">Buscar</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table tabla-panel align-middle">
            <thead>
                <tr>
                    <th>Razón social</th>
                    <th>CUIT</th>
                    <th>Teléfono</th>
                    <th>Mail</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($Total == 0) { ?>
                    <tr>
                        <td colspan="6" class="vacio">
                            No se encontraron proveedores<br>
                            <small>Probá con otra razón social o CUIT</small>
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php for ($i = 0; $i < count($Proveedores); $i++) { ?>
                        <tr>
                            <td><?php echo Esc($Proveedores[$i]['RAZON_SOCIAL']); ?></td>
                            <td><?php echo Esc(FormatearCUIT($Proveedores[$i]['CUIT'])); ?></td>
                            <td><?php echo Esc($Proveedores[$i]['TELEFONO']); ?></td>
                            <td><?php echo Esc($Proveedores[$i]['MAIL']); ?></td>
                            <td>
                                <?php if ($Proveedores[$i]['ACTIVO'] == 1) { ?>
                                    <span class="badge badge-activo">Activo</span>
                                <?php } else { ?>
                                    <span class="badge badge-inactivo">Inactivo</span>
                                <?php } ?>
                            </td>
                            <td class="acciones">
                                <?php if ($PuedeGestionar) { ?>
                                    <?php if ($Proveedores[$i]['ACTIVO'] == 1) { ?>
                                        <a href="editar.php?id=<?php echo $Proveedores[$i]['ID']; ?>" class="accion-link accion-editar">Editar</a>
                                        <button type="button" class="accion-link accion-baja"
                                                data-bs-toggle="modal" data-bs-target="#modalBaja"
                                                data-accion="desactivar"
                                                data-id="<?php echo $Proveedores[$i]['ID']; ?>"
                                                data-nombre="<?php echo Esc($Proveedores[$i]['RAZON_SOCIAL']); ?>">Desactivar</button>
                                    <?php } else { ?>
                                        <button type="button" class="accion-link accion-editar"
                                                data-bs-toggle="modal" data-bs-target="#modalBaja"
                                                data-accion="reactivar"
                                                data-id="<?php echo $Proveedores[$i]['ID']; ?>"
                                                data-nombre="<?php echo Esc($Proveedores[$i]['RAZON_SOCIAL']); ?>">Reactivar</button>
                                    <?php } ?>
                                <?php } else { ?>
                                    <a href="editar.php?id=<?php echo $Proveedores[$i]['ID']; ?>" class="accion-link accion-editar">Ver</a>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center">
        <small class="text-muted">Mostrando <?php echo count($Proveedores); ?> de <?php echo $Total; ?> proveedores</small>

        <?php if ($TotalPaginas > 1) { ?>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php for ($p = 1; $p <= $TotalPaginas; $p++) { ?>
                        <li class="page-item <?php if ($p == $Pagina) { echo 'active'; } ?>">
                            <a class="page-link" href="index.php?<?php echo Esc(Armar_Query_Listado($Busqueda, $Estado, $Orden, $p)); ?>"><?php echo $p; ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        <?php } ?>
    </div>

</div>

<?php if ($PuedeGestionar) { ?>
    <?php require 'modal_baja.php'; ?>
    <?php require 'modal_bloqueo.php'; ?>
<?php } ?>

<?php require '../layouts/footer.php'; ?>
