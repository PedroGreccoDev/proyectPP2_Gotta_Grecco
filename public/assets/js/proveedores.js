// ==========================================================
// Modulo de proveedores
// - modal de confirmacion de baja / reactivacion (index)
// - modal de bloqueo por operaciones pendientes (index)
// - validacion inmediata del formulario (nuevo / editar)
// El servidor vuelve a validar todo: esto es solo el aviso rapido.
// ==========================================================

// CUIT: 11 digitos y digito verificador correcto (igual que ValidarCUIT en PHP)
function cuitValido(valor) {
    var cuit = valor.replace(/[^0-9]/g, '');

    if (cuit.length !== 11) {
        return false;
    }

    var pesos = [5, 4, 3, 2, 7, 6, 5, 4, 3, 2];
    var suma = 0;

    for (var i = 0; i < 10; i++) {
        suma += parseInt(cuit.charAt(i), 10) * pesos[i];
    }

    var verificador = 11 - (suma % 11);

    if (verificador === 11) {
        verificador = 0;
    }

    if (verificador === 10) {
        return false;
    }

    return parseInt(cuit.charAt(10), 10) === verificador;
}

// 30712345671 -> 30-71234567-1
function formatearCuit(valor) {
    var cuit = valor.replace(/[^0-9]/g, '');

    if (cuit.length !== 11) {
        return valor;
    }

    return cuit.substr(0, 2) + '-' + cuit.substr(2, 8) + '-' + cuit.substr(10, 1);
}

document.addEventListener('DOMContentLoaded', function () {

    // ------------------------------------------------------
    // Modal de baja / reactivacion
    // ------------------------------------------------------
    var modalBaja = document.getElementById('modalBaja');

    if (modalBaja) {
        modalBaja.addEventListener('show.bs.modal', function (evento) {
            var boton = evento.relatedTarget;
            var accion = boton.getAttribute('data-accion');
            var nombre = boton.getAttribute('data-nombre');

            document.getElementById('baja_id').value = boton.getAttribute('data-id');
            document.getElementById('baja_accion').value = accion;

            var titulo = document.getElementById('baja_titulo');
            var texto = document.getElementById('baja_texto');
            var confirmar = document.getElementById('baja_boton');

            if (accion === 'desactivar') {
                titulo.textContent = '¿Desactivar proveedor?';
                texto.textContent = '"' + nombre + '" dejará de aparecer en nuevas órdenes de compra y facturas. Su historial se conserva y podés reactivarlo cuando quieras.';
                confirmar.textContent = 'Desactivar';
                confirmar.className = 'btn btn-danger';
            } else {
                titulo.textContent = '¿Reactivar proveedor?';
                texto.textContent = '"' + nombre + '" volverá a aparecer en los combos de compras y conserva todo su historial.';
                confirmar.textContent = 'Reactivar';
                confirmar.className = 'btn btn-verde';
            }
        });
    }

    // ------------------------------------------------------
    // Modal de bloqueo (se abre solo si el servidor lo pidio)
    // ------------------------------------------------------
    var modalBloqueo = document.getElementById('modalBloqueo');

    if (modalBloqueo && modalBloqueo.getAttribute('data-mostrar') === '1') {
        new bootstrap.Modal(modalBloqueo).show();
    }

    // ------------------------------------------------------
    // Validacion del formulario (nuevo / editar)
    // ------------------------------------------------------
    var formulario = document.getElementById('formProveedor');

    if (!formulario) {
        return;
    }

    var botonGuardar = document.getElementById('botonGuardar');
    var campoCuit = document.getElementById('cuit');
    var cuitEditable = campoCuit && !campoCuit.readOnly;

    // devuelve el mensaje de error de un campo ('' si esta bien)
    function mensajeDe(nombre) {
        var valor = document.getElementById(nombre).value.trim();

        if (nombre === 'razon_social') {
            if (valor === '') {
                return 'Campo obligatorio';
            }
        }

        if (nombre === 'cuit' && cuitEditable) {
            if (valor === '') {
                return 'Campo obligatorio';
            }
            if (!cuitValido(valor)) {
                return 'CUIT inválido';
            }
        }

        if (nombre === 'telefono' && valor !== '') {
            if (!/^[0-9+\-\s()]+$/.test(valor)) {
                return 'El teléfono solo admite números, espacios y guiones';
            }
        }

        if (nombre === 'mail' && valor !== '') {
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor)) {
                return 'Correo electrónico inválido';
            }
        }

        return '';
    }

    // muestra u oculta el error debajo del campo
    function mostrarError(nombre, mensaje) {
        var campo = document.getElementById(nombre);
        var aviso = document.getElementById('err_' + nombre);

        if (!campo || !aviso) {
            return;
        }

        aviso.textContent = mensaje;

        if (mensaje === '') {
            campo.classList.remove('campo-error');
        } else {
            campo.classList.add('campo-error');
        }
    }

    var campos = ['razon_social', 'cuit', 'telefono', 'mail'];

    // true si algun campo tiene error (sin mostrar nada)
    function hayErrores() {
        for (var i = 0; i < campos.length; i++) {
            if (document.getElementById(campos[i]) && mensajeDe(campos[i]) !== '') {
                return true;
            }
        }
        return false;
    }

    // si la pagina vino con errores del servidor, el boton queda deshabilitado hasta que se edite algo
    if (formulario.getAttribute('data-errores') === '1') {
        botonGuardar.disabled = true;
    }

    for (var i = 0; i < campos.length; i++) {
        (function (nombre) {
            var campo = document.getElementById(nombre);

            if (!campo || campo.readOnly) {
                return;
            }

            campo.addEventListener('input', function () {
                // al escribir solo se limpia el error; se vuelve a evaluar al salir del campo
                mostrarError(nombre, '');
                botonGuardar.disabled = hayErrores();
            });

            campo.addEventListener('blur', function () {
                if (nombre === 'cuit') {
                    campo.value = formatearCuit(campo.value.trim());
                }
                mostrarError(nombre, mensajeDe(nombre));
                botonGuardar.disabled = hayErrores();
            });
        })(campos[i]);
    }

    formulario.addEventListener('submit', function (evento) {
        var conError = false;

        for (var i = 0; i < campos.length; i++) {
            if (document.getElementById(campos[i])) {
                var mensaje = mensajeDe(campos[i]);
                mostrarError(campos[i], mensaje);

                if (mensaje !== '') {
                    conError = true;
                }
            }
        }

        if (conError) {
            evento.preventDefault();
            botonGuardar.disabled = true;
        }
    });
});
