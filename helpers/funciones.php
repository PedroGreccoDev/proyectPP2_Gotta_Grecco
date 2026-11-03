<?php 


function DatosLogin_Hash($vUsuario, $vClave, $vConexion){
    $Usuario = array();

    $SQL = "SELECT id_usuario, nombre, apellido, mail, contraseña, id_rol
            FROM usuarios
            WHERE mail = '$vUsuario'";

    $rs = mysqli_query($vConexion,$SQL);
    $data = mysqli_fetch_array($rs);

    if(!empty($data) && password_verify($vClave,$data['contraseña'])){
        $Usuario['ID']       = $data['id_usuario'];
        $Usuario['NOMBRE']   = $data['nombre'];
        $Usuario['APELLIDO'] = $data['apellido'];
        $Usuario['MAIL']     = $data['mail'];
        $Usuario['ROL']      = $data['id_rol'];
    }

    return $Usuario;
}

//RECUPERAR CONTRASEÑA

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function EnviarMailRecuperacion($vMail, $vEnlace) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'felismurf07@gmail.com';       // tu cuenta de Gmail
        $mail->Password   = 'izpwihnhqxgjqqhi';   // NO tu contraseña normal, ver paso 2
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('felismurf07@gmail.com', 'Sistema de Gestion');
        $mail->addAddress($vMail);

        $mail->isHTML(true);
        $mail->Subject = 'Recuperacion de contraseña';
        $mail->Body    = 'Hacé clic en el siguiente enlace para restablecer tu contraseña: <a href="' . $vEnlace . '">' . $vEnlace . '</a>';
        $mail->AltBody = 'Copiá y pegá este enlace en tu navegador: ' . $vEnlace;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function GenerarToken($vIdUsuario) {
    $vExpira = time() + (5 * 60);
    $vPayload = $vIdUsuario . '|' . $vExpira;
    $vFirma = hash_hmac('sha256', $vPayload, 'clave_secreta_pp2');
    $vToken = base64_encode($vPayload . '|' . $vFirma);
    return $vToken;
}

function ValidarToken($vToken) {
    $vDecodificado = base64_decode($vToken);
    $vPartes = explode('|', $vDecodificado);

    if (count($vPartes) != 3) {
        return false;
    }

    $vIdUsuario = $vPartes[0];
    $vExpira = $vPartes[1];
    $vFirma = $vPartes[2];

    $vPayload = $vIdUsuario . '|' . $vExpira;
    $vFirmaEsperada = hash_hmac('sha256', $vPayload, 'clave_secreta_pp2');

    if (!hash_equals($vFirmaEsperada, $vFirma)) {
        return false;
    }

    if (time() > $vExpira) {
        return false;
    }

    return $vIdUsuario;
}

function Obtener_Usuario_Por_Mail($vMail, $vConexion) {
    $Usuario = array();

    $SQL = "SELECT id_usuario, nombre, mail
            FROM usuarios
            WHERE mail = '$vMail'";

    $rs = mysqli_query($vConexion, $SQL);
    $data = mysqli_fetch_array($rs);

    if (!empty($data)) {
        $Usuario['ID'] = $data['id_usuario'];
        $Usuario['NOMBRE'] = $data['nombre'];
        $Usuario['MAIL'] = $data['mail'];
    }

    return $Usuario;
}

function Actualizar_Contraseña($vIdUsuario, $vConexion) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $SQL = "UPDATE usuarios SET contraseña = '$password' WHERE id_usuario = '$vIdUsuario'";

    if (mysqli_query($vConexion, $SQL)) {
        return true;
    } else {
        return false;
    }
}

//USUARIOS

function Insertar_Usuario($vConexion){
    $nombre = strip_tags($_POST['nombre']);
    $apellido = strip_tags($_POST['apellido']);
    $mail = strip_tags($_POST['mail']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $id_rol = strip_tags($_POST['id_rol']);

    $SQL = "INSERT INTO usuarios (nombre, apellido, mail, contraseña, id_rol)
            VALUES ('$nombre', '$apellido', '$mail', '$password', '$id_rol')";

    if(mysqli_query($vConexion,$SQL)){
        return true;
    } else {
        return false;
    }
}