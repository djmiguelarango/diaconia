<?php

require 'sibas-db.class.php';

session_start();

$arrUSR = array(0 => 0, 1 => 'R', 2 => 'La Contraseña no puede ser cambiada');

if (isset($_GET['user']) && isset($_GET['cp_new_password']) && isset($_GET['url'])) {
    $link = new SibasDB();
    
    $idUser     = $link->real_escape_string(trim(base64_decode($_GET['user'])));
    $password   = $link->real_escape_string(trim($_GET['cp_new_password']));
    $url        = $link->real_escape_string(trim(base64_decode($_GET['url'])));
    $email      = $link->real_escape_string(trim($_GET['cp_email']));
    $idef       = $_SESSION['idEF'];

    $password   = crypt_blowfish_bycarluys($password);
    
    $sql = "update 
        s_usuario as su
            inner join 
        s_ef_usuario as seu ON (seu.id_usuario = su.id_usuario)
            inner join 
        s_entidad_financiera as sef ON (sef.id_ef = seu.id_ef)
    set 
        su.password = '" . $password . "', 
        su.email = '" . $email . "', 
        su.cambio_password = true,
        su.history_password = '',
        su.date_password = '" . date('Y-m-d H:i:s') . "'
    where
        su.id_usuario = '" . $idUser . "'
            and sef.id_ef = '" . base64_decode($idef) . "'
    ;";

    if ($link->query($sql)) {
        $arrUSR[0] = 1;
        $arrUSR[1] = $url;
        $arrUSR[2] = 'La contraseña se actualizó correctamente <br> Por favor espere..';
    } else {
        $arrUSR[2] = 'No se pudo actualizar la contraseña';
    }
}

echo json_encode($arrUSR);

function crypt_blowfish_bycarluys($password, $digito = 7) {
    //  El salt para Blowfish debe ser escrito de la siguiente manera: 
    //  $2a$, $2x$ o $2y$ + 2 números de iteración entre 04 y 31 + 22 caracteres
    $set_salt = './1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $salt = sprintf('$2a$%02d$', $digito);
    
    for($i = 0; $i < 22; $i++){
        $salt .= $set_salt[mt_rand(0, 63)];
    }
    
    return crypt($password, $salt);
}
?>