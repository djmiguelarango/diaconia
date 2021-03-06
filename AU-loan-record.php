<?php
require('sibas-db.class.php');
require('session.class.php');
$session = new Session();
$token = $session->check_session();
$arrAU = array(0 => 0, 1 => 'R', 2 => 'Error: No se pudo procesar la Cotización');

$link = new SibasDB();

if($token === FALSE){
	if(($_ROOT = $link->get_id_root()) !== FALSE) {
		$_SESSION['idUser'] = base64_encode($_ROOT['idUser']);
	} else {
		$arrAU[0] = 1;
		$arrAU[1] = 'logout.php';
		$arrAU[2] = 'La Cotización no puede ser registrada, intentelo mas tarde';
	}
}

if(isset($_POST['ms']) && isset($_POST['page']) && isset($_POST['pr'])){
	$pr = $link->real_escape_string(trim(base64_decode($_POST['pr'])));
	
	if($pr === 'AU|01'){
		$ms = $link->real_escape_string(trim($_POST['ms']));
		$page = $link->real_escape_string(trim($_POST['page']));
		
		$cp = 0;
		if (isset($_POST['fq-cp'])) {
			$cp = $link->real_escape_string(trim($_POST['fq-cp']));
			if($cp === md5(1)) {
				$cp = 1;
			} elseif($cp === md5(0)) {
				$cp = 0;
			}
		}
		
		$idc = uniqid('@S#2$2013',true);
		$record = $link->getRegistrationNumber($_SESSION['idEF'], 'AU', 0);
		
		$sql = 'insert into s_au_cot_cabecera 
		(`id_cotizacion`, `no_cotizacion`, `id_ef`, `certificado_provisional`, 
		`fecha_creacion`, `id_usuario`)
		values
		("'.$idc.'", '.$record.', "'.base64_decode($_SESSION['idEF']).'", 
		'.$cp.', curdate(), "'.base64_decode($_SESSION['idUser']).'") ;';
		
		if($link->query($sql) === TRUE){
			$arrAU[0] = 1;
			$arrAU[1] = 'au-quote.php?ms='.$ms.'&page='.$page.'&pr='.base64_encode('AU|01').'&idc='.base64_encode($idc);
			$arrAU[2] = 'La Cotización fue registrada con exito';
		}else{
			
		}
		$link->close();
	}else{
		$arrAU[2] = 'La Cotización no puede ser registrada';
	}
	echo json_encode($arrAU);
}else{
	echo json_encode($arrAU);
}
?>