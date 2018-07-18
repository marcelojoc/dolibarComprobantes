<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once '../../main.inc.php';
include_once ( '../class/getComprobantes.class.php');
include_once ( '../class/genComprobantePdf.class.php');

//  var_dump($conf->global->MAIN_INFO_SOCIETE_NOM);
//  var_dump($conf->global->MAIN_INFO_SOCIETE_ADDRESS);
//  var_dump($conf->global->MAIN_INFO_SOCIETE_ZIP);
//  var_dump($conf->global->MAIN_INFO_SOCIETE_TOWN);
 var_dump(DOL_DATA_ROOT);

 var_dump($conf->global->MAIN_MONNAIE);
 var_dump($conf->global->MAIN_INFO_SOCIETE_LOGO_MINI);
//  var_dump($conf->global->MAIN_INFO_SOCIETE_LOGO);
//  var_dump($conf->global->MAIN_INFO_SOCIETE_WEB);
//  var_dump($conf->global->MAIN_INFO_SOCIETE_MAIL);
//  var_dump($conf->global->MAIN_INFO_SOCIETE_TEL);
 var_dump($conf->global->MAIN_INFO_SIRET);
 var_dump($conf->global->MAIN_INFO_SIREN);



//  var_dump(strtoupper($langs->getLabelFromNumber(72956.43 ,0|1)));
//  var_dump(strtoupper($langs->getLabelFromNumber('85' ,0|0)));


 echo json_encode($conf->global);

?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    
<table cellspacing="0" cellpadding="5" border="1">


    <tr>
    <td WIDTH="40%">  <img  src="http://localhost/dolibar_local/documents/mycompany/logos/images.png"  height="60" width="100" />   </td>
    <td WIDTH="20%" align="center" style="font-size: 25;margin-top: 0;  vertical-align: top;"> <h1><b>X</b></h1> </td>
    <td WIDTH="40%"> <h1><b>RECIBO</b></h1> <br> N° zzzzzzzzzzzzzzzzz


    <h3>Fecha: 55555555</h3>
    </td></tr>



</table> <HR>



</body>
</html>