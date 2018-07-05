<?php
require_once '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';

require_once TCPDF_PATH.'tcpdf.php';


class genComprobantePdf 
{


	function __construct($db)
	{


		$this->db = $db;
		// $this->name = "crabe";
		// $this->description = $langs->trans('PDFCrabeDescription');
		// $this->update_main_doc_field = 1;		// Save the name of generated file as the main doc when generating a doc with this template

		// Dimensiont page
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:10;

		$this->option_logo = 1;                    // Affiche logo
		$this->option_tva = 1;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 1;                 // Affiche mode reglement
		$this->option_condreg = 1;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 1;      // Affiche code produit-service
		$this->option_multilang = 1;               // Dispo en plusieurs langues
		$this->option_escompte = 1;                // Affiche si il y a eu escompte
		$this->option_credit_note = 1;             // Support credit notes
		$this->option_freetext = 1;				   // Support add of a personalised text
		$this->option_draft_watermark = 1;		   // Support add of a watermark on drafts

		$this->franchise=!$mysoc->tva_assuj;

		// // Get source company
		// $this->emetteur=$mysoc;
		// if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if was not defined

		// // Define position of columns
		// $this->posxdesc=$this->marge_gauche+1;
		// if($conf->global->PRODUCT_USE_UNITS)
		// {
		// 	$this->posxtva=101;
		// 	$this->posxup=118;
		// 	$this->posxqty=135;
		// 	$this->posxunit=151;
		// }
		// else
		// {
		// 	$this->posxtva=110;
		// 	$this->posxup=126;
		// 	$this->posxqty=145;
		// }
		// $this->posxdiscount=162;
		// $this->posxprogress=126; // Only displayed for situation invoices
		// $this->postotalht=174;
		// if (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT) || ! empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT_COLUMN)) $this->posxtva=$this->posxup;
		// $this->posxpicture=$this->posxtva - (empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH)?20:$conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH);	// width of images


	}





    public function dibujar(){


		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	
		// set font
		$pdf->SetFont('times', '', 10);
		
		// add a page
		$pdf->AddPage();
		



		$pdf->Ln();
		$tbl = '
		<table cellspacing="0" cellpadding="5" border="1">
		<tr>
		  <th  colspan="3">Detalle de factura pagada</th>
		  <th  colspan="4"> Detalle de valores recibidos</th>
		</tr>
		<tr>
		  <td><strong>Factura Nº</strong></td>
		  <td>Fecha</td>
		  <td>Importe</td>
		  <td>Medio de Pago</td>
		  <td>Banco</td>
		  <td>Fecha Venc</td>
		  <td>Importe</td>
		</tr>

		<tr>
		  <td>Fac-35898</td>
		  <td>22/8/2018</td>
		  <td>$5200</td>
		  <td>EFECTIVO</td>
		  <td></td>
		  <td>25/8/2018</td>
		  <td>$5200</td>
		</tr>





	  </table>
		';
		
		$pdf->writeHTML($tbl, true, false, false, false, '');

		$txt = '<b>Lorem ipsum </b> <br>dolor sit amet, consectetur adipisicing </b> elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';

		$pdf->writeHTML($txt, true, false, false, false, '');
		$pdf->Ln();
		$tbl = '
		<table cellspacing="0" cellpadding="5" border="1">
		<tr>
		  <th  colspan="3">Detalle de factura pagada</th>
		  <th  colspan="4"> Detalle de valores recibidos</th>
		</tr>
		<tr>
		  <td><strong>Factura Nº</strong></td>
		  <td>Fecha</td>
		  <td>Importe</td>
		  <td>Medio de Pago</td>
		  <td>Banco</td>
		  <td>Fecha Venc</td>
		  <td>Importe</td>
		</tr>

		<tr>
		  <td>Fac-35898</td>
		  <td>22/8/2018</td>
		  <td>$5200</td>
		  <td>EFECTIVO</td>
		  <td></td>
		  <td>25/8/2018</td>
		  <td>$5200</td>
		</tr>





	  </table>
		';
		
		$pdf->writeHTML($tbl, true, false, false, false, '');

// <img src="http://placehold.it/32x32" border="0" height="32" width="32" />
		$htmlTotal = '
		
		
		<H3 align="right"> Total : $5200 </H3>
		<H3 align="right"> Pendiente : $0 </H3>

		<hr>
		';

		$pdf->writeHTML($htmlTotal, true, false, false, false, '');






		$pdf->lastPage();
		
		// ---------------------------------------------------------
		
		//Close and output PDF document
		$pdf->Output('example_005.pdf', 'I');





    }






}

