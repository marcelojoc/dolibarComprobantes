<?php
require_once '../../main.inc.php';
include_once ( '../class/getComprobantes.class.php');
include_once ( '../class/genComprobantePdf.class.php');




$comprobante = (GETPOST('comp', 'int') ? GETPOST('comp', 'int') : GETPOST('comp', 'int')); // For backward compatibility

// var_dump($comprobante);

 $test = new genComprobantePdf($db);

 $test->dibujar();