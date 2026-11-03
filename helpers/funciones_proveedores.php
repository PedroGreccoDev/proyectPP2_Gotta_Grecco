<?php 

// ==========================================
// CONSTANTES (ids de las tablas de catalogo)
// ==========================================

// estado_orden_compra: 1 Pendiente, 2 Aprobada, 3 Rechazada, 4 Recibida
// una orden "pendiente" es una que todavia no se recibio ni se rechazo
define('ESTADO_OC_PENDIENTE', 1);
define('ESTADO_OC_APROBADA', 2);

// estado_factura: 1 Pendiente, 2 Pagada, 3 Vencida
define('ESTADO_FACTURA_PAGADA', 2);

// accion_auditoria
define('ACCION_ALTA', 1);
define('ACCION_MODIFICACION', 2);
define('ACCION_BAJA_LOGICA', 3);
define('ACCION_REACTIVACION', 4);

// ==========================================
// UTILIDADES
// ==========================================

// lee un campo de $_POST, sin etiquetas ni espacios de los costados
function Dato_Post($vCampo) {
    if (isset($_POST[$vCampo])) {
        return trim(strip_tags($_POST[$vCampo]));
    } else {
        return '';
    }
}

// arma el valor para un INSERT/UPDATE: texto escapado entre comillas, o NULL si esta vacio
function ValorSQL($vConexion, $vTexto) {
    if ($vTexto === null || $vTexto === '') {
        return 'NULL';
    } else {
        return "'" . mysqli_real_escape_string($vConexion, (string) $vTexto) . "'";
    }
}

function Obtener_NombreRol($vConexion, $vIdRol) {
    $Nombre = '';

    $SQL = "SELECT nombre_rol FROM roles WHERE id_rol = " . intval($vIdRol);

    $rs = mysqli_query($vConexion, $SQL);
    $data = mysqli_fetch_array($rs);

    if (!empty($data)) {
        $Nombre = $data['nombre_rol'];
        if ($Nombre == 'administracion') {
            $Nombre = 'Administración';
        }
        $Nombre = ucfirst($Nombre);
    }

    return $Nombre;
}

// arma el query string del listado manteniendo busqueda, filtro, orden y pagina
function Armar_Query_Listado($vBusqueda, $vEstado, $vOrden, $vPagina) {
    return http_build_query(array(
        'q'      => $vBusqueda,
        'estado' => $vEstado,
        'orden'  => $vOrden,
        'pagina' => $vPagina
    ));
}

// ==========================================
// CUIT
// ==========================================

// deja solo los digitos (acepta 30-71234567-1 o 30712345671)
function LimpiarCUIT($vCuit) {
    return preg_replace('/[^0-9]/', '', (string) $vCuit);
}

// 11 digitos y digito verificador correcto (algoritmo modulo 11)
function ValidarCUIT($vCuit) {
    $Cuit = LimpiarCUIT($vCuit);

    if (strlen($Cuit) != 11) {
        return false;
    }

    $Pesos = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
    $Suma = 0;

    for ($i = 0; $i < 10; $i++) {
        $Suma = $Suma + (intval($Cuit[$i]) * $Pesos[$i]);
    }

    $Verificador = 11 - ($Suma % 11);

    if ($Verificador == 11) {
        $Verificador = 0;
    }

    if ($Verificador == 10) {
        return false;
    }

    return intval($Cuit[10]) == $Verificador;
}

// 30712345671 -> 30-71234567-1
function FormatearCUIT($vCuit) {
    $Cuit = LimpiarCUIT($vCuit);

    if (strlen($Cuit) != 11) {
        return $Cuit;
    }

    return substr($Cuit, 0, 2) . '-' . substr($Cuit, 2, 8) . '-' . substr($Cuit, 10, 1);
}

// ==========================================
// PROVEEDORES
// ==========================================

// devuelve el proveedor que tiene ese CUIT (sin contar $vIdExcluir) o un array vacio
function Buscar_Proveedor_Por_Cuit($vConexion, $vCuit, $vIdExcluir) {
    $Proveedor = array();

    $Cuit = LimpiarCUIT($vCuit);
    $IdExcluir = intval($vIdExcluir);

    $SQL = "SELECT id_proveedor, activo
            FROM proveedores
            WHERE cuit = $Cuit
            AND id_proveedor <> $IdExcluir";

    $rs = mysqli_query($vConexion, $SQL);
    $data = mysqli_fetch_array($rs);

    if (!empty($data)) {
        $Proveedor['ID']     = $data['id_proveedor'];
        $Proveedor['ACTIVO'] = $data['activo'];
    }

    return $Proveedor;
}

// HU-10: validaciones del lado servidor (control definitivo)
// $vIdActual = 0 en el alta, el id del proveedor en la modificacion
// $vValidarCuit = false en la modificacion (el CUIT es inmutable)
// devuelve un array campo => mensaje (vacio si esta todo bien)
function Validar_Proveedor($vConexion, $vIdActual, $vValidarCuit) {
    $Errores = array();

    $RazonSocial = Dato_Post('razon_social');
    $Telefono    = Dato_Post('telefono');
    $Mail        = Dato_Post('mail');
    $Direccion   = Dato_Post('direccion');

    if ($RazonSocial == '') {
        $Errores['razon_social'] = 'Campo obligatorio';
    } elseif (strlen($RazonSocial) > 255) {
        $Errores['razon_social'] = 'Máximo 255 caracteres';
    }

    if ($vValidarCuit) {
        $Cuit = LimpiarCUIT(Dato_Post('cuit'));

        if ($Cuit == '') {
            $Errores['cuit'] = 'Campo obligatorio';
        } elseif (!ValidarCUIT($Cuit)) {
            $Errores['cuit'] = 'CUIT inválido';
        } else {
            $Existente = Buscar_Proveedor_Por_Cuit($vConexion, $Cuit, $vIdActual);

            if (!empty($Existente)) {
                if ($Existente['ACTIVO'] == 1) {
                    $Errores['cuit'] = 'Ya existe un proveedor con ese CUIT';
                } else {
                    $Errores['cuit'] = 'Ya existe un proveedor con ese CUIT, pero está inactivo. Reactivalo desde el listado';
                }
            }
        }
    }

    if ($Telefono != '' && !preg_match('/^[0-9+\-\s()]+$/', $Telefono)) {
        $Errores['telefono'] = 'El teléfono solo admite números, espacios y guiones';
    }

    if ($Mail != '') {
        if (!filter_var($Mail, FILTER_VALIDATE_EMAIL)) {
            $Errores['mail'] = 'Correo electrónico inválido';
        } elseif (strlen($Mail) > 255) {
            $Errores['mail'] = 'Máximo 255 caracteres';
        }
    }

    if (strlen($Direccion) > 255) {
        $Errores['direccion'] = 'Máximo 255 caracteres';
    }

    return $Errores;
}

// arma el WHERE del listado segun la busqueda y el filtro de estado
function Armar_Filtro_Proveedores($vConexion, $vBusqueda, $vEstado) {
    $WHERE = " WHERE 1=1";

    if ($vEstado == 'activos') {
        $WHERE = $WHERE . " AND activo = 1";
    } elseif ($vEstado == 'inactivos') {
        $WHERE = $WHERE . " AND activo = 0";
    }

    if ($vBusqueda != '') {
        $Texto = mysqli_real_escape_string($vConexion, $vBusqueda);
        $Digitos = LimpiarCUIT($vBusqueda);

        $WHERE = $WHERE . " AND (razon_social LIKE '%$Texto%'";

        // solo se busca por CUIT si lo escrito tiene digitos (si no, el LIKE '%%' traeria todo)
        if ($Digitos != '') {
            $WHERE = $WHERE . " OR CAST(cuit AS CHAR) LIKE '%$Digitos%'";
        }

        $WHERE = $WHERE . ")";
    }

    return $WHERE;
}

function Contar_Proveedores($vConexion, $vBusqueda, $vEstado) {
    $SQL = "SELECT COUNT(*) AS total FROM proveedores" . Armar_Filtro_Proveedores($vConexion, $vBusqueda, $vEstado);

    $rs = mysqli_query($vConexion, $SQL);
    $data = mysqli_fetch_array($rs);

    return intval($data['total']);
}

function Listar_Proveedores($vConexion, $vBusqueda, $vEstado, $vOrden, $vPagina, $vPorPagina) {
    $Proveedores = array();

    $PorPagina = intval($vPorPagina);
    $Offset = (intval($vPagina) - 1) * $PorPagina;

    $OrderBy = "razon_social ASC";
    if ($vOrden == 'razon_desc') {
        $OrderBy = "razon_social DESC";
    }

    $SQL = "SELECT id_proveedor, razon_social, cuit, telefono, mail, direccion, activo
            FROM proveedores"
            . Armar_Filtro_Proveedores($vConexion, $vBusqueda, $vEstado) .
            " ORDER BY $OrderBy
            LIMIT $PorPagina OFFSET $Offset";

    $rs = mysqli_query($vConexion, $SQL);
    $i = 0;

    while ($data = mysqli_fetch_array($rs)) {
        $Proveedores[$i]['ID']           = $data['id_proveedor'];
        $Proveedores[$i]['RAZON_SOCIAL'] = $data['razon_social'];
        $Proveedores[$i]['CUIT']         = $data['cuit'];
        $Proveedores[$i]['TELEFONO']     = $data['telefono'];
        $Proveedores[$i]['MAIL']         = $data['mail'];
        $Proveedores[$i]['DIRECCION']    = $data['direccion'];
        $Proveedores[$i]['ACTIVO']       = $data['activo'];
        $i++;
    }

    return $Proveedores;
}

function Obtener_Proveedor($vConexion, $vId) {
    $Proveedor = array();

    $Id = intval($vId);

    $SQL = "SELECT id_proveedor, razon_social, cuit, telefono, mail, direccion, activo
            FROM proveedores
            WHERE id_proveedor = $Id";

    $rs = mysqli_query($vConexion, $SQL);
    $data = mysqli_fetch_array($rs);

    if (!empty($data)) {
        $Proveedor['ID']           = $data['id_proveedor'];
        $Proveedor['RAZON_SOCIAL'] = $data['razon_social'];
        $Proveedor['CUIT']         = $data['cuit'];
        $Proveedor['TELEFONO']     = (string) $data['telefono'];
        $Proveedor['MAIL']         = (string) $data['mail'];
        $Proveedor['DIRECCION']    = (string) $data['direccion'];
        $Proveedor['ACTIVO']       = $data['activo'];
    }

    return $Proveedor;
}

// HU-06: devuelve el id del proveedor creado, o false si fallo
function Insertar_Proveedor($vConexion) {
    $RazonSocial = ValorSQL($vConexion, Dato_Post('razon_social'));
    $Cuit        = LimpiarCUIT(Dato_Post('cuit'));
    $Telefono    = ValorSQL($vConexion, Dato_Post('telefono'));
    $Mail        = ValorSQL($vConexion, Dato_Post('mail'));
    $Direccion   = ValorSQL($vConexion, Dato_Post('direccion'));

    $SQL = "INSERT INTO proveedores (razon_social, cuit, telefono, mail, direccion, activo)
            VALUES ($RazonSocial, $Cuit, $Telefono, $Mail, $Direccion, 1)";

    if (mysqli_query($vConexion, $SQL)) {
        $IdNuevo = mysqli_insert_id($vConexion);
        Registrar_Auditoria($vConexion, 'proveedores', $IdNuevo, ACCION_ALTA, '', '', Dato_Post('razon_social'));
        return $IdNuevo;
    } else {
        return false;
    }
}

// HU-08: actualiza solo los campos que cambiaron (el CUIT no se toca nunca)
// $vActual es el array que devolvio Obtener_Proveedor
// devuelve la cantidad de campos modificados (0 si no hubo cambios) o false si fallo
function Actualizar_Proveedor($vConexion, $vId, $vActual) {
    $Id = intval($vId);

    $Campos = array('razon_social', 'telefono', 'mail', 'direccion');
    $Cambiados = array();
    $Anteriores = array();
    $Nuevos = array();
    $Set = '';
    $Cantidad = 0;

    for ($i = 0; $i < count($Campos); $i++) {
        $Campo = $Campos[$i];
        $Nuevo = Dato_Post($Campo);
        $Anterior = $vActual[strtoupper($Campo)];

        if ($Nuevo != $Anterior) {
            if ($Cantidad > 0) {
                $Set = $Set . ', ';
            }
            $Set = $Set . $Campo . ' = ' . ValorSQL($vConexion, $Nuevo);

            $Anteriores[$Cantidad] = $Anterior;
            $Nuevos[$Cantidad] = $Nuevo;
            $Cambiados[$Cantidad] = $Campo;
            $Cantidad++;
        }
    }

    if ($Cantidad == 0) {
        return 0;
    }

    $SQL = "UPDATE proveedores SET $Set WHERE id_proveedor = $Id";

    if (mysqli_query($vConexion, $SQL)) {
        for ($i = 0; $i < $Cantidad; $i++) {
            Registrar_Auditoria($vConexion, 'proveedores', $Id, ACCION_MODIFICACION, $Cambiados[$i], $Anteriores[$i], $Nuevos[$i]);
        }
        return $Cantidad;
    } else {
        return false;
    }
}

// HU-09: baja logica ($vActivo = 0) o reactivacion ($vActivo = 1)
function Cambiar_Estado_Proveedor($vConexion, $vId, $vActivo) {
    $Id = intval($vId);

    if ($vActivo == 1) {
        $Nuevo = 1;
        $Anterior = 0;
        $Accion = ACCION_REACTIVACION;
    } else {
        $Nuevo = 0;
        $Anterior = 1;
        $Accion = ACCION_BAJA_LOGICA;
    }

    $SQL = "UPDATE proveedores SET activo = $Nuevo WHERE id_proveedor = $Id";

    if (mysqli_query($vConexion, $SQL)) {
        Registrar_Auditoria($vConexion, 'proveedores', $Id, $Accion, 'activo', $Anterior, $Nuevo);
        return true;
    } else {
        return false;
    }
}

// HU-09 escenario 2: ordenes de compra abiertas y facturas sin pagar del proveedor
function Operaciones_Pendientes_Proveedor($vConexion, $vId) {
    $Pendientes = array();
    $Id = intval($vId);

    $SQL = "SELECT id_orden
            FROM ordenes_compra
            WHERE id_proveedor = $Id
            AND id_estado_orden_compra IN (" . ESTADO_OC_PENDIENTE . ", " . ESTADO_OC_APROBADA . ")
            ORDER BY id_orden ASC";

    $rs = mysqli_query($vConexion, $SQL);
    $Codigos = '';
    $i = 0;

    while ($data = mysqli_fetch_array($rs)) {
        if ($i > 0) {
            $Codigos = $Codigos . ', ';
        }
        $Codigos = $Codigos . 'OC-' . str_pad($data['id_orden'], 4, '0', STR_PAD_LEFT);
        $i++;
    }

    $Pendientes['ORDENES'] = $i;
    $Pendientes['CODIGOS'] = $Codigos;

    $SQL = "SELECT COUNT(*) AS total
            FROM facturas
            WHERE id_proveedor = $Id
            AND id_estado_factura <> " . ESTADO_FACTURA_PAGADA;

    $rs = mysqli_query($vConexion, $SQL);
    $data = mysqli_fetch_array($rs);

    $Pendientes['FACTURAS'] = intval($data['total']);

    return $Pendientes;
}

// ==========================================
// AUDITORIA (HU-11)
// ==========================================

// guarda quien, cuando, que accion y los valores anterior y nuevo
function Registrar_Auditoria($vConexion, $vEntidad, $vIdRegistro, $vIdAccion, $vCampo, $vAnterior, $vNuevo) {
    $IdUsuario = intval($_SESSION['Usuario_ID']);
    $IdRegistro = intval($vIdRegistro);
    $IdAccion = intval($vIdAccion);

    $Entidad  = ValorSQL($vConexion, $vEntidad);
    $Campo    = ValorSQL($vConexion, $vCampo);
    $Anterior = ValorSQL($vConexion, $vAnterior);
    $Nuevo    = ValorSQL($vConexion, $vNuevo);

    $SQL = "INSERT INTO auditoria (id_usuario, entidad, id_registro, id_accion, campo, valor_anterior, valor_nuevo)
            VALUES ($IdUsuario, $Entidad, $IdRegistro, $IdAccion, $Campo, $Anterior, $Nuevo)";

    if (mysqli_query($vConexion, $SQL)) {
        return true;
    } else {
        return false;
    }
}

function Listar_Auditoria($vConexion, $vEntidad) {
    $Registros = array();

    $Entidad = mysqli_real_escape_string($vConexion, $vEntidad);

    $SQL = "SELECT a.fecha, u.nombre, u.apellido, ac.descripcion AS accion,
            a.campo, a.valor_anterior, a.valor_nuevo, p.razon_social
            FROM auditoria a
            INNER JOIN usuarios u ON a.id_usuario = u.id_usuario
            INNER JOIN accion_auditoria ac ON a.id_accion = ac.id_accion
            LEFT JOIN proveedores p ON a.id_registro = p.id_proveedor
            WHERE a.entidad = '$Entidad'
            ORDER BY a.fecha DESC, a.id_auditoria DESC
            LIMIT 200";

    $rs = mysqli_query($vConexion, $SQL);
    $i = 0;

    while ($data = mysqli_fetch_array($rs)) {
        $Registros[$i]['FECHA']     = $data['fecha'];
        $Registros[$i]['USUARIO']   = $data['nombre'] . ' ' . $data['apellido'];
        $Registros[$i]['ACCION']    = $data['accion'];
        $Registros[$i]['CAMPO']     = $data['campo'];
        $Registros[$i]['ANTERIOR']  = $data['valor_anterior'];
        $Registros[$i]['NUEVO']     = $data['valor_nuevo'];
        $Registros[$i]['PROVEEDOR'] = $data['razon_social'];
        $i++;
    }

    return $Registros;
}
