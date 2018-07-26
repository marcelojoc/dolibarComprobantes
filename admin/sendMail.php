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
$comp=GETPOST('id','alpha');

if (! $user->admin) accessforbidden();


	// var_dump($comp);

	if($comp != ''){    //  si envio datos de id de comprobante

		$comprobante = new getComprobantes($db);
				
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


				$wikihelp='EN:Setup_EMails|FR:Paramétrage_EMails|ES:Formulario envio de comprobantes';
				llxHeader('','Formulario envio de comprobantes',$wikihelp);
				$head = email_admin_prepare_head();

				print load_fiche_titre('Formulario de envio de comprobantes','','');

				// Cree l'objet formulaire mail
				include_once DOL_DOCUMENT_ROOT.'/core/class/html.formmail.class.php';
				$formmail = new FormMail($db);
				$formmail->trackid= $trackid;
				$formmail->fromname = $conf->global->MAIN_MAIL_EMAIL_FROM;
				$formmail->frommail = $conf->global->MAIN_MAIL_EMAIL_FROM;
				$formmail->fromid= $user->id;
				$formmail->fromalsorobot=1;
				$formmail->fromtype=(GETPOST('fromtype')?GETPOST('fromtype'):(!empty($conf->global->MAIN_MAIL_DEFAULT_FROMTYPE)?$conf->global->MAIN_MAIL_DEFAULT_FROMTYPE:'user'));

				$formmail->withfromreadonly=1;
				$formmail->withsubstit=0;
				$formmail->withfrom=1;
				// $formmail->witherrorsto=1;
				$formmail->withto= $comprobante->emailCliente;     // ! empty to keep field if empty
				$formmail->withtoccc=(! empty($_POST['sendtoccc'])?$_POST['sendtoccc']:0);    // ! empty to keep field if empty
				$formmail->withtopic=(isset($_POST['subject'])?$_POST['subject']:'comprobante 55555555');
				$formmail->withtopicreadonly=0;
				$formmail->withfile=2;
		$formmail->withbody='__(Hello)__,<br><br>

		Estimado __MEMBER_FIRSTNAME__ <br><br>
		
			Envio comprobante de pago para la factura<br><br>
			
		__(Sincerely)__<br><br>'.$conf->global->MAIN_INFO_SOCIETE_NOM;



		$formmail->withbodyreadonly=0;
		$formmail->withcancel=1;
		$formmail->withdeliveryreceipt=1;
		$formmail->ckeditortoolbar='dolibarr_mailings';
		// Tableau des substitutions
		
		// Tableau des parametres complementaires du post
		$formmail->param["action"]="send";
		$formmail->param["models"]="body";
		$formmail->param["mailid"]=0;
		$formmail->param["returnurl"]=$_SERVER["PHP_SELF"];
		$formmail->param['fileinit'] = 'C:/wamp/www/dolibar_local/documents/facture/FA1807-13811/FA1807-13811.pdf';



		$formmail->param['action'] = 'send';
		// $formmail->param['models'] = 'facture_send';
		$formmail->param['models_id']=GETPOST('modelmailselected','int');
		$formmail->param['id'] = '14150';
		$formmail->param['returnurl'] = DOL_MAIN_URL_ROOT . '/compta/paiement/card.php?id=' . '14150';
		$file= 'C:\wamp\www\dolibar_local\documents\comprobantes\PAY1807-14073';
		$formmail->param['fileinit'] = array($file,);

        
		// Init list of files
		if (GETPOST("mode")=='init')
		{
			$formmail->clear_attached_files();
		}

		$formmail->clear_attached_files();


		$formmail->add_attached_files($file, basename($file), 'application/pdf');
		print $formmail->get_form();

		
		dol_fiche_end();


		}else{


			// el numero de id del comprobante no existe 


		}




	}else{  // Si no mando nada



		


	}









	
	
		

llxFooter();

$db->close();
