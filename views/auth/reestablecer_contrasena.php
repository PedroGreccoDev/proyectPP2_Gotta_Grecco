<?php
session_start();

require_once '../../config/database.php';
require_once '../../helpers/funciones.php';

$MiConexion = ConexionBD();
$Mensaje = '';
$TokenValido = false;
$Token = '';

if (!empty($_GET['token'])) {
    $Token = strip_tags($_GET['token']);
    if (ValidarToken($Token) !== false) {
        $TokenValido = true;
    } else {
        $Mensaje = 'El enlace venció o no es válido, solicitá uno nuevo';
    }
} else {
    $Mensaje = 'Enlace inválido';
}

if (!empty($_POST['BotonRestablecer'])) {

    $Token = $_POST['token'];
    $IdUsuario = ValidarToken($Token);

    if ($IdUsuario !== false) {

        $Contraseña = $_POST['password'];

        if (strlen($Contraseña) < 8 || !preg_match('/[A-Z]/', $Contraseña) || !preg_match('/[0-9]/', $Contraseña)) {
            $Mensaje = 'La contraseña debe tener al menos 8 caracteres y una mayúscula';
            $TokenValido = true;
        } else {
            if (Actualizar_Contraseña($IdUsuario, $MiConexion)) {
                header('Location: /sdegestionpp2/views/auth/login.php');
                exit;
            } else {
                $Mensaje = 'Ocurrió un error al actualizar la contraseña';
                $TokenValido = true;
            }
        }

    } else {
        $Mensaje = 'El enlace venció o no es válido, solicitá uno nuevo';
        $TokenValido = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../public/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="auth-container">
        <div class="auth-brand">
            <div style="background-color: #ffffff; padding: 20px 30px; border-radius: 16px; margin-bottom: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center;">
                <img src="../../public/assets/img/Logo2G&G.png" alt="G&G Manager" style="width: 100%; max-width: 250px; height: auto;">
            </div>
            
            <div class="brand-text">
                <h3>Automatizá tu comercio.</h3>
                <p>Controlá ventas, compras, stock y administración desde un mismo panel.</p>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="auth-card">
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="../../public/assets/img/LogoG&G.png" alt="G&G Logo" style="width: 90px; height: 90px; border-radius: 22px; object-fit: cover; box-shadow: 0 8px 20px rgba(10, 54, 34, 0.15);">
                </div>
                <h2>Restablecer contraseña</h2>

                <?php if ($Mensaje != '') { ?>
                    <div class="alert alert-warning py-2"><?php echo $Mensaje; ?></div>
                <?php } ?>

                <?php if ($TokenValido) { ?>
                    <form method="POST">
                        <input type="hidden" name="token" value="<?php echo $Token; ?>">

                        <div class="mb-3">
                            <label class="form-label">Nueva contraseña</label>
                            <input type="password" name="password" class="form-control" required>
                            <div class="form-text">Mínimo 8 caracteres, al menos una mayúscula y al menos un número</div>
                        </div>

                        <button type="submit" name="BotonRestablecer" value="1" class="btn btn-auth">Guardar contraseña</button>
                    </form>
                <?php } else { ?>
                    <div class="auth-links">
                        <a href="recuperar_contrasena.php">Solicitar un nuevo enlace</a>
                    </div>
                <?php } ?>

            </div>
        </div>
    </div>

</body>
</html>