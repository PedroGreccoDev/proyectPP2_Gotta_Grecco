<?php 

//CONEXION

function ConexionBD($Host = 'localhost', $User = 'root', $Password = '', $BaseDeDatos = 'sdegestionpp2'){
    mysqli_report(MYSQLI_REPORT_OFF);
    $linkConexion = mysqli_connect($Host, $User, $Password, $BaseDeDatos);
    if($linkConexion != false){
        return $linkConexion;
    } else {
        die('No se pudo establecer la conexion con la base');
    }
}