<?php 
session_start();

require_once '../../config/database.php';
require_once '../../helpers/funciones.php';

$MiConexion = ConexionBD();
$Mensaje = '';

if (!empty($_POST['BotonRegistro'])){

    $Contraseña = $_POST['password'];

    if (strlen($Contraseña) < 8 || !preg_match('/[A-Z]/', $Contraseña) || !preg_match('/[0-9]/', $Contraseña)) {
        $_SESSION['Mensaje'] = 'La contraseña debe tener al menos 8 caracteres, una mayúscula y un número.';
        header('Location: registro.php');
        exit;
    } else {

        if(Insertar_Usuario($MiConexion)){
            header('Location: /sdegestionpp2/views/auth/login.php');
            exit;
        } else {
            
            $_SESSION['Mensaje'] = 'Ese correo ya está registrado';
            header('Location: registro.php');
            exit;
        }

    }
}
//VALIDACION PARA QUE CUANDO DE F5 SE BORRE SI HUBO UN ERROR
if (!empty($_SESSION['Mensaje'])) {
    $Mensaje = $_SESSION['Mensaje'];
    unset($_SESSION['Mensaje']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../public/assets/css/style.css" rel="stylesheet">
</head>
<body>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../../public/assets/css/style.css" rel="stylesheet">
</head>
<body>

    <div class="auth-container">
        <!-- LADO IZQUIERDO: Branding -->
        <div class="auth-brand">
            
            <div style="background-color: #ffffff; padding: 20px 30px; border-radius: 16px; margin-bottom: 40px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); text-align: center;">
                <img src="../../public/assets/img/Logo2G&G.png" alt="G&G Manager" style="width: 100%; max-width: 250px; height: auto;">
            </div>
            
            <div class="brand-text">
                <h3>Unite a G&G Manager.</h3>
                <p>Comenzá a gestionar tu negocio de forma inteligente, rápida y segura hoy mismo.</p>
            </div>
        </div>

        <!-- LADO DERECHO: Formulario de Registro -->
        <div class="auth-form-side">
            <div class="auth-card">
                
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="../../public/assets/img/LogoG&G.png" alt="G&G Logo" style="width: 90px; height: 90px; border-radius: 22px; object-fit: cover; box-shadow: 0 8px 20px rgba(10, 54, 34, 0.15);">
                </div>

                <h2>Crear cuenta</h2>

                <!-- PHP Intacto -->
                <?php if ($Mensaje != '') { ?>
                    <div class="alert alert-danger py-2"><?php echo $Mensaje; ?></div>
                <?php } ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Nombre (*)</label>
                        <input type="text" name="nombre" class="form-control custom-input" required placeholder="Ej: Juan">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Apellido(*)</label>
                        <input type="text" name="apellido" class="form-control custom-input" required placeholder="Ej: Pérez">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Correo electrónico(*)</label>
                        <input type="email" name="mail" class="form-control custom-input" required placeholder="Ej: usuario@ggmanager.com">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña(*)</label>
                        <input type="password" name="password" class="form-control custom-input" required placeholder="••••••••">
                        <div class="form-text" style="font-size: 12px; font-weight: 500;">-Mínimo 8 caracteres</div>
                        <div class="form-text" style="font-size: 12px; font-weight: 500;">-Al menos una mayúscula</div>
                        <div class="form-text" style="font-size: 12px; font-weight: 500;">-Al menos un número</div>
                    </div>

                    <!-- Input Oculto Intacto -->
                    <input type="hidden" name="id_rol" value="1">

                    <button type="submit" name="BotonRegistro" value="1" class="btn btn-auth w-100">Registrarme</button>
                </form>

                <div class="auth-links">
                    <span class="text-muted">¿Ya tenés cuenta?</span> <a href="login.php" class="link-accent">Iniciá sesión</a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
</body>
</html>