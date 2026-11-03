<?php 

define('BASE_URL', '/sdegestionpp2');

// ids de la tabla roles (ver los INSERT del script de la base)
define('ROL_VENTAS', 1);
define('ROL_COMPRAS', 2);
define('ROL_ADMINISTRACION', 3);
define('ROL_GERENTE', 4);

function verificarSesion() {
    if (empty($_SESSION['Usuario_ID'])) {
        header('Location: ' . BASE_URL . '/views/auth/login.php');
        exit;
    }
}

// devuelve true si el rol del usuario logueado esta en la lista
function TienePermiso($vRolesPermitidos) {
    for ($i = 0; $i < count($vRolesPermitidos); $i++) {
        if ($vRolesPermitidos[$i] == $_SESSION['Usuario_Rol']) {
            return true;
        }
    }
    return false;
}

// HU-11: quien puede hacer que en proveedores
function RolesVerProveedores() {
    return array(ROL_COMPRAS, ROL_ADMINISTRACION, ROL_GERENTE);
}

function RolesGestionarProveedores() {
    return array(ROL_COMPRAS, ROL_ADMINISTRACION);
}

function RolesVerAuditoria() {
    return array(ROL_ADMINISTRACION, ROL_GERENTE);
}

// escapa texto para mostrarlo en HTML
function Esc($vTexto) {
    return htmlspecialchars((string) $vTexto, ENT_QUOTES, 'UTF-8');
}
