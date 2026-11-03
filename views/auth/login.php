<?php 
session_start();

require_once '../../config/database.php';
require_once '../../helpers/funciones.php';

$MiConexion = ConexionBD();
$Mensaje = '';

if (!empty($_POST['BotonLogin'])){
    
    $UsuarioLogueado = DatosLogin_Hash(strip_tags($_POST['mail']), strip_tags($_POST['password']), $MiConexion);

    if(!empty($UsuarioLogueado)){
        $_SESSION['Usuario_ID'] = $UsuarioLogueado['ID'];
        $_SESSION['Usuario_Nombre'] = $UsuarioLogueado['NOMBRE'];
        $_SESSION['Usuario_Apellido'] = $UsuarioLogueado['APELLIDO'];
        $_SESSION['Usuario_Rol'] = $UsuarioLogueado['ROL'];
        header('Location: /sdegestionpp2/views/dashboard/index.php');
        exit;
    } else {
        $_SESSION['Mensaje'] = 'Email o contraseña incorrectos';
        header('Location: login.php');
        exit;
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
    <title>Iniciar sesión</title>
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
                <h3>Automatizá tu comercio.</h3>
                <p>Controlá ventas, compras, stock y administración desde un mismo panel.</p>
            </div>
        </div>

        <!-- LADO DERECHO: Formulario -->
        <div class="auth-form-side">
            <div class="auth-card">
                
                <!-- Logo Cuadrado con estilos forzados para que no explote de tamaño -->
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="../../public/assets/img/LogoG&G.png" alt="G&G Logo" style="width: 90px; height: 90px; border-radius: 22px; object-fit: cover; box-shadow: 0 8px 20px rgba(10, 54, 34, 0.15);">
                </div>

                <h2>Iniciar sesión</h2>

                <?php if ($Mensaje != '') { ?>
                    <div class="alert alert-danger py-2"><?php echo $Mensaje; ?></div>
                <?php } ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Correo electrónico</label>
                        <input type="email" name="mail" class="form-control custom-input" required placeholder="ej. usuario@ggmanager.com">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" class="form-control custom-input" required placeholder="••••••••">
                    </div>

                    <button type="submit" name="BotonLogin" value="1" class="btn btn-auth">Iniciar sesión</button>
                </form>

                <div class="auth-links">
                    <a href="recuperar_contrasena.php" class="link-muted">¿Olvidaste tu contraseña?</a><br>
                    <a href="registro.php" class="link-accent">Registrate</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>