<?php
require_once '../../main.inc.php';
include_once ( '../class/getComprobantes.class.php');
include_once ( '../class/genComprobantePdf.class.php');




$comprobante = (GETPOST('comp', 'int') ? GETPOST('comp', 'int') : GETPOST('comp', 'int')); // For backward compatibility

// var_dump($comprobante);

// var_dump($conf->global->MAIN_INFO_SOCIETE_NOM);
// var_dump($conf->global->MAIN_INFO_SOCIETE_ADDRESS);
// var_dump($conf->global->MAIN_INFO_SOCIETE_ZIP);
// var_dump($conf->global->MAIN_INFO_SOCIETE_TOWN);
// var_dump($conf->global->MAIN_INFO_SOCIETE_LOGO_SMALL);
// var_dump($conf->global->MAIN_INFO_SOCIETE_LOGO_MINI);
// var_dump($conf->global->MAIN_INFO_SOCIETE_LOGO);
// var_dump($conf->global->MAIN_INFO_SOCIETE_WEB);
// var_dump($conf->global->MAIN_INFO_SOCIETE_MAIL);
// var_dump($conf->global->MAIN_INFO_SOCIETE_TEL);
// var_dump($conf->global->MAIN_INFO_SIRET);
// var_dump($conf->global->MAIN_INFO_SIREN);


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
    'download'=>'D',


);

 $test = new genComprobantePdf($db,$langs, $config  );

 $test->dibujar($comprobante);