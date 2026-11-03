<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once '../../config/database.php';
require_once '../../helpers/funciones.php';

$MiConexion = ConexionBD();
$Mensaje = '';

if (!empty($_POST['BotonRecuperar'])) {

    $UsuarioEncontrado = Obtener_Usuario_Por_Mail(strip_tags($_POST['mail']), $MiConexion);

    if (!empty($UsuarioEncontrado)) {
        $Token = GenerarToken($UsuarioEncontrado['ID']);
        $Enlace = 'http://localhost/sdegestionpp2/views/auth/reestablecer_contrasena.php?token=' . $Token;
        EnviarMailRecuperacion($UsuarioEncontrado['MAIL'], $Enlace);
    }

    $Mensaje = 'Si el correo está registrado, te enviamos un enlace de recuperación';

}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar contraseña</title>
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
                <h2>Olvidé mi contraseña</h2>

                <?php if ($Mensaje != '') { ?>
                    <div class="alert alert-success py-2"><?php echo $Mensaje; ?></div>
                <?php } ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Correo registrado</label>
                        <input type="email" name="mail" class="form-control" required>
                    </div>

                    <button type="submit" name="BotonRecuperar" value="1" class="btn btn-auth">Enviar enlace</button>
                </form>

                <div class="auth-links">
                    <a href="login.php">Volver a iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>