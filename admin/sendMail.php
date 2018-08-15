<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2009-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2013	   Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2016      Jonathan TISSEAU     <jonathan.tisseau@86dev.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *       \file       htdocs/admin/mails.php
 *       \brief      Page to setup emails sending
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT."/comprobantes/class/getComprobantes.class.php";

$langs->load("mails");
$langs->load("other");
$langs->load("errors");

$action=GETPOST('action','alpha');
$comp=GETPOST('comp','alpha');

if (! $user->admin) accessforbidden();


 var_dump($_POST);

	if($comp != ''){    //  si envio datos de id de comprobante

		$comprobante = new getComprobantes($db);

		//modelmailselected  es el parametro de la plantilla
		/**
		 * hay que hacer un metodo que busque en la tabla 
		 * llx_c_email_templates con el id de este numero
		 * traer la plantilla y el asunto 
		 * Luego un metodo para reemplazar cada dato con el del comprobante
		 * 
		 * en la pagina de prueb de correos hay un metodo que se puede modificar para este proposito
		 * 
		 */
				
		$valorComprobante= $comprobante->setIdComprobante($comp);


		if($valorComprobante['response'] ){    //  si el resultado de setear el comprobante es favorable puedo desplegar el formulario

			$comprobante->dataFactura(); // busco todos los datos del comprobante;

			$id=0;
			$actiontypecode='';     // Not an event for agenda
			$trigger_name='$trackid';       // Disable triggers
			$paramname='id';
			$mode='emailfortest';
			$trackid='comprobante';
			include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
			/*
			* View
			*/
			$wikihelp='EN:Setup_EMails|ES:Formulario envio de comprobantes';
			llxHeader('','Formulario envio de comprobantes',$wikihelp);
			$head = email_admin_prepare_head();

			print load_fiche_titre('Formulario de envio de comprobantes','','');

			// Cree l'objet formulaire mail
			include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
			$formmail = new FormMail($db);
			$formmail->trackid= $trackid;
			$formmail->fromname = (isset($_POST['fromname'])?$_POST['fromname']:$conf->global->MAIN_MAIL_EMAIL_FROM);
			$formmail->frommail = (isset($_POST['frommail'])?$_POST['frommail']:$conf->global->MAIN_MAIL_EMAIL_FROM);
			$formmail->fromid= $user->id;
			$formmail->fromalsorobot=1;
			$formmail->fromtype= (GETPOST('fromtype')?GETPOST('fromtype'):(!empty($conf->global->MAIN_MAIL_DEFAULT_FROMTYPE)?$conf->global->MAIN_MAIL_DEFAULT_FROMTYPE:'user'));

			$formmail->withfromreadonly=1;
			$formmail->withsubstit=0;
			$formmail->withfrom=1;
			// $formmail->witherrorsto=1;
			$formmail->withto=(! empty($_POST['sendto'])?$_POST['sendto']:($comprobante->emailCliente));
			$formmail->withtocc=(! empty($_POST['sendtocc'])?$_POST['sendtocc']:1);       // ! empty to keep field if empty
			$formmail->withtoccc=(! empty($_POST['sendtoccc'])?$_POST['sendtoccc']:1);    // ! empty to keep field if empty
			$formmail->withtopic=(isset($_POST['subject'])?$_POST['subject']:'Comprobante de pago '.$comprobante->referenciaComprobante);

			$formmail->withtopicreadonly=0;
			$formmail->withfile=2;
			// este esw el cuerpo del mensaje

			$formmail->withbody='__(Hello)__,<br><br>

			Estimado '.$comprobante->nombreCliente.' <br><br>
			
				Envio comprobante de pago para la factura '.$comprobante->referenciaFactura.' <br><br>
				
			__(Sincerely)__<br><br>'.$conf->global->MAIN_INFO_SOCIETE_NOM;

			$formmail->withbodyreadonly=0;
			$formmail->withcancel=1;
			$formmail->withdeliveryreceipt=1;
			$formmail->ckeditortoolbar='dolibarr_mailings';
			// Tableau des substitution
			// Tableau des parametres complementaires du post
			$formmail->param["action"]="send";
			$formmail->param["models"]="body";
			$formmail->param["mailid"]=0;
			$formmail->param["returnurl"]=$_SERVER["PHP_SELF"].'?comp='. $comp;
			$file= DOL_DATA_ROOT.'/comprobantes/'.$comprobante->referenciaComprobante.'/'.$comprobante->referenciaComprobante.'.pdf';
			$formmail->param['fileinit'] = array($file);		
			$formmail->param['id'] = $comp;
			
			$formmail->clear_attached_files();


			$formmail->add_attached_files($file, basename($file), 'application/pdf');
			print $formmail->get_form();

			
			dol_fiche_end();


		}else{


			// el numero de id del comprobante no existe 
			$wikihelp='EN:Setup_EMails|FR:Paramétrage_EMails|ES:Formulario envio de comprobantes';
			llxHeader('','Formulario envio de comprobantes',$wikihelp);
			$head = email_admin_prepare_head();

			print load_fiche_titre('Formulario de envio de comprobantes','','');

			print '<b>EL NUMERO DE COMPROBANTE NO EXISTE </b><br>';
			print '<b><a href="'.DOL_URL_ROOT.'/compta/paiement/card.php?id='.$comp.'">Volver</a></b>';


		}




	}else{  // Si no mando nada o  ya envio el mensaje


		if($_POST['action']== 'send'){  // si ya envio el mensaje

			$wikihelp='EN:Setup_EMails|FR:Paramétrage_EMails|ES:Formulario envio de comprobantes';
			llxHeader('','Formulario envio de comprobantes',$wikihelp);
			$head = email_admin_prepare_head();
	
			print load_fiche_titre('Formulario de envio de comprobantes','','');
	
			print '<b>COMPROBANTE  ENVIADO</b>';
			
			// var_dump($conf->global->MAIN_MAIL_EMAIL_FROM);

		}else{

			$wikihelp='EN:Setup_EMails|FR:Paramétrage_EMails|ES:Formulario envio de comprobantes';
			llxHeader('','Formulario envio de comprobantes',$wikihelp);
			$head = email_admin_prepare_head();
	
			print load_fiche_titre('Formulario de envio de comprobantes','','');
	
			print '<b>NO HAY DATOS ENVIADOS PARA PROCESAR</b>';
			
			// var_dump($conf->global->MAIN_MAIL_EMAIL_FROM);

		}

	}



	// var_dump($_POST);
	// var_dump($_SERVER["PHP_SELF"]);
	

	// $comprobante = new getComprobantes($db);
				
	// $valorComprobante= $comprobante->setIdComprobante($comp);


	    //  si el resultado de setear el comprobante es favorable puedo desplegar el formulario

		

	
	
	
		

llxFooter();

$db->close();
