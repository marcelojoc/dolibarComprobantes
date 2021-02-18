<?php
require_once '../../main.inc.php';
include_once ( '../class/getComprobantes.class.php');
include_once ( '../class/genComprobantePdf.class.php');


if($user->admin == 0){

    if(is_null($user->rights->Comprobantes->comprobante) ){
        accessforbidden();
    }

}

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);

$comprobante = (GETPOST('comp', 'int') ? GETPOST('comp', 'int') : GETPOST('comp', 'int')); // For backward compatibility

//var_dump($comprobante);

$protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';

$domain = $_SERVER["HTTP_HOST"];
$url = $_SERVER['REQUEST_URI'];
$data = explode("scripts", $url);
$urlSinParams = $data[0];

$config = array( 

    'empresa'=>$conf->global->MAIN_INFO_SOCIETE_NOM,
    'direccion'=>$conf->global->MAIN_INFO_SOCIETE_ADDRESS,
    'cpos'=>$conf->global->MAIN_INFO_SOCIETE_ZIP,
    'ciudad'=>'Mendoza',
    'dep'=>$conf->global->MAIN_INFO_SOCIETE_TOWN,
    'tel'=>$conf->global->MAIN_INFO_SOCIETE_TEL,
    'web'=>$conf->global->MAIN_INFO_SOCIETE_WEB,
    'email'=>$conf->global->MAIN_INFO_SOCIETE_MAIL,
    'logo'=>$conf->global->MAIN_INFO_SOCIETE_LOGO,
    'iibb'=>$conf->global->MAIN_INFO_SIRET,
    'cuit'=>$conf->global->MAIN_INFO_SIREN,
    'url'=>$protocol .$domain. $urlSinParams.'img/',
    'download'=>'D',


);

 $test = new genComprobantePdf($db,$langs, $config  );

 $test->dibujar($comprobante);