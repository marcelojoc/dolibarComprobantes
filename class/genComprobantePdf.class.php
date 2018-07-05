<?php
require_once '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once TCPDF_PATH.'tcpdf.php';
require_once DOL_DOCUMENT_ROOT."/comprobantes/class/getComprobantes.class.php";

// $conf->mycompany->dir_output.'/logos/';
// var_dump($conf->mycompany->dir_output.'/logos/');
// var_dump(DOL_DATA_ROOT);
class genComprobantePdf 
{



	function __construct($db)
	{

		
		$this->db = $db;
		$this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		

		// set document information
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor('Nicola Asuni');
		$this->pdf->SetTitle('TCPDF Example 004');
		$this->pdf->SetSubject('TCPDF Tutorial');
		$this->pdf->SetKeywords('TCPDF, PDF, example, test, guide');

	}





    public function dibujar(){

// var_dump($conf);

// exit;
		$comprobante = new getComprobantes($this->db);
		
		$valorComprobante= $comprobante->setIdComprobante(14083);
		
		$comprobante->dataFactura();
		// var_dump($comprobante);
		
		// exit;
		if($valorComprobante['response']){


			$this->pdf->SetFont('helvetica', '', 9);
		
			// add a page
			$this->pdf->AddPage();
		
	
			$this->pdf->Ln();
			$tbl = '
			<table cellspacing="0" cellpadding="5" border="0">
			

			<tr>
			  <td WIDTH="40%">  <img src="'.DOL_DATA_ROOT.'/mycompany/logos/images.png'.'"  height="80" width="80" />   </td>
			  <td WIDTH="20%" align="center" style="font-size: 25;margin-top: 0"> <h1><b>X</b></h1> </td>
			  <td WIDTH="40%"> <h1><b>RECIBO</b></h1> <br> N°'.$comprobante->referenciaComprobante;



			  $tbl .=  ' <h3>Fecha:  '.$comprobante->fecha.'</h3>';
			  $tbl .=  '  </td></tr></table> <HR>';
			
			$this->pdf->writeHTML($tbl, true, false, false, false, '');
	
				$txt = '<p><b>Recibi de: </b>'.$comprobante->nombreCliente.' - '.$comprobante->direccionCliente.'</p><br>
				
				<p><b>Cantidad de Pesos: $</b>'.$comprobante->monto.'</p><br>
				
				<p><b>Por los siguientes conceptos: </b>'.$comprobante->referenciaFactura ;
			
			if($comprobante->objAfip != false){

				$txt.= ' - Afip '.$comprobante->objAfip->ptovta.'-'.str_pad($comprobante->objAfip->nComprobanteAfip, 8, "0", STR_PAD_LEFT);
			}
			
				$txt.= '</p><br>';
	
			$this->pdf->writeHTML($txt, true, false, false, false, '');
			$this->pdf->Ln();

			$tblDatos = '
			<table cellspacing="0" cellpadding="5" border="1">
			<tr>
				<th  colspan="3" align="center">Detalle de factura pagada</th>
				<th  colspan="4" align="center"> Detalle de valores recibidos</th>
			</tr>
			<tr>
			  <td><strong>Comp Nº</strong></td>
			  <td><strong>Fecha</strong></td>
			  <td><strong>Importe</strong></td>
			  <td><strong>Medio de Pago</strong></td>
			  <td><strong>Banco</strong></td>
			  <td><strong>Fecha Venc</strong></td>
			  <td><strong>Importe</strong></td>
			</tr>
	
			<tr>
			  <td>'.$comprobante->referenciaFactura.'</td>
			  <td>'.$comprobante->fechaFactura.'</td>
			  <td> $'.$comprobante->total.'</td>
			  <td>'.$comprobante->medioDePago.'</td>
			  <td>'.$comprobante->banco.'</td>
			  <td>'.$comprobante->fechaVencimiento.'</td>
			  <td> $'.$comprobante->monto.'</td>
			</tr>
	
	
	
	
	
		  </table>
			';
			
			 $this->pdf->writeHTML($tblDatos, true, false, false, false, '');
	
	// <img src="http://placehold.it/32x32" border="0" height="32" width="32" />
			$htmlTotal = '
			
			
			<H3 align="right"> Total : $ '.$comprobante->monto.' </H3>
			<H3 align="right" color="red"> Pendiente :  $'.($comprobante->total - $comprobante->montoTotalPagado).'</H3>
	
			<hr>';
	
			$this->pdf->writeHTML($htmlTotal, true, false, false, false, '');
	
	
	
	
	
	
			$this->pdf->lastPage();
			
			// ---------------------------------------------------------
			
			//Close and output PDF document
			$this->pdf->Output($comprobante->referenciaComprobante.'.pdf', 'D');



















		}else{  //si hay algun error imprime pdf de error

			$this->pdf->SetFont('helvetica', '', 10);
		
			// add a page
			$this->pdf->AddPage();

			$txt = '<p><b>'.$valorComprobante['msg'].'</b></p><br>';
	
			$this->pdf->writeHTML($txt, true, false, false, false, '');
			$this->pdf->lastPage();

			$this->pdf->Output('example_005.pdf', 'I');

		}


		





    }






}

