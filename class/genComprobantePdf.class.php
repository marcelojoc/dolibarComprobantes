<?php
// require_once '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once TCPDF_PATH.'tcpdf.php';
require_once DOL_DOCUMENT_ROOT."/comprobantes/class/getComprobantes.class.php";


// $conf->mycompany->dir_output.'/logos/';
// var_dump($conf->mycompany->dir_output.'/logos/');
// var_dump(DOL_URL_ROOT);
class genComprobantePdf 
{



	function __construct($db, $langs ,$conf)
	{

		
		$this->db = $db;
		$this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		
		// set document information
		$this->pdf->SetCreator($conf['empresa'].' - ' .$conf['web']);
		$this->pdf->SetAuthor($conf['empresa']);
		$this->pdf->SetTitle('COMPROBANTES');
		$this->pdf->SetSubject('PDF COMPROBANTES');
		$this->langs = $langs;
		$this->conf = $conf;


	}



    public function dibujar($comp){

		
		// var_dump(DOL_DOCUMENT_ROOT);

		// exit;
		$comprobante = new getComprobantes($this->db);
		
		$valorComprobante= $comprobante->setIdComprobante($comp);
		
		// $comprobante->dataFactura();
		// var_dump($comprobante);
		
		// exit;
		if($valorComprobante['response']){




			// set certificate file 
$certificate = DOL_DOCUMENT_ROOT."/comprobantes/crt/certificado.crt";

	// set additional information 
	$info = array(
		'Name' => 'TCPDF', 
		'Location' => 'Office', 
		'Reason' => 'Testing TCPDF', 
		'ContactInfo' => 'http://www.tcpdf.org', 
		); 
	
	// set document signature 
	$this->pdf->setSignature($certificate, $certificate, 'tcpdfdemo', '', 2, $info); 


			$this->pdf->SetFont('helvetica', '', 9);
		
			// add a page
			$this->pdf->AddPage();
		
	
			$this->pdf->Ln();
			$tbl = '
			<table cellspacing="0" cellpadding="5" border="0">
			
			<tr>
			  <td WIDTH="45%" align="center">  <img  src="'.DOL_DATA_ROOT.'/mycompany/logos/'.$this->conf['logo'].'"  height="110"  />
			  
			  <br><small>'.$this->conf['empresa']. ' - '.$this->conf['direccion'].  ' - '.$this->conf['ciudad']
			  . ' - Tel:'.$this->conf['tel']
			  . ' - '.$this->conf['email']
			  . ' - <b>'.$this->conf['web']
			  .'</b></small>
			  </td>';

				$tbl .='  <td WIDTH="10%" align="center" style="font-size: 25; align: top;"> <h1><b>X</b></h1> </td>';
				$tbl .='  <td WIDTH="45%"> <small>DOCUMENTO NO VALIDO COMO FACTURA</small><h1><b>RECIBO</b></h1> N° '.$comprobante->referenciaComprobante;

				$tbl .=  ' <h3>Fecha:  '.$comprobante->fecha.'</h3>';
				$tbl .=  ' <small>CUIT:  '.$this->conf['cuit']. ' -  IIBB: '.$this->conf['iibb'].' <br><b> IVA RESPONSABLE INSCRIPTO</b></small>';

			  	$tbl .=  '  </td></tr></table> <HR>';
			
				$this->pdf->writeHTML($tbl, true, false, false, false, '');

				// separo el monto por que no lo escribe correctamente el modulo de dolibarr

				$entero= strval($comprobante->monto);
				$porciones = explode(".", $entero);
				// var_dump($porciones[0]);
				// var_dump($porciones[1]);
				// var_dump($comprobante->monto);
				
				$textoMonto= 'PESOS ';
				$textoMonto.= strtoupper($this->langs->getLabelFromNumber($porciones[0] ,0|0));
				$textoMonto.= ' CON ';
				$textoMonto.= strtoupper($this->langs->getLabelFromNumber($porciones[1] ,0|0));
				$textoMonto.= ' CENT ';
				// $str = $langs->getLabelFromNumber($comprobante->monto,0|1);
				// $str = strtoupper($str);

				//  var_dump($textoMonto);
				// exit;
				$txt = '<p><b>Recibi de: </b>'.$comprobante->nombreCliente.' - '.$comprobante->direccionCliente.'</p><br>
				
				<p><b>Cantidad : </b>'.$textoMonto.'</p><br>
				
				<p><b>Por los siguientes conceptos: </b>'.$comprobante->referenciaFactura ;
			
			// if($comprobante->objAfip != false){

			// 	$datoAfip= $comprobante->objAfip->ptovta.'-'.str_pad($comprobante->objAfip->nComprobanteAfip, 8, "0", STR_PAD_LEFT);
			// 	$txt.= ' - Afip '.$datoAfip;
			// }
			
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
			  <td><strong>Nota</strong></td>
			  <td><strong>Importe</strong></td>
			</tr>';
	
			$totalesDeFacturas= 0;

			foreach($comprobante->facturas as $facturas){

				$tblDatos .='
				
				<tr>
					<td>'.$facturas['referenciaFactura'].' '.$datoAfip.'</td>
					<td>'.$facturas['fechaFactura'].'</td>
					<td> $'.$facturas['total'].'</td>
					<td>'.$comprobante->medioDePago.' '.$comprobante->numeroDePago.'</td>
					<td>'.$comprobante->banco.'</td>
					<td>'.$comprobante->nota.'</td>
					<td> $'.$facturas['total'].'</td>
				</tr>';


				$totalesDeFacturas +=  intval($facturas['total']);
			}

	

	
		  $tblDatos .= '</table>';
			
			 $this->pdf->writeHTML($tblDatos, true, false, false, false, '');
	
			$htmlTotal = '
			<H3 align="right"> Total : $ '.$comprobante->monto.' </H3>
			<H3 align="right" color="red"> Pendiente :  $'.(intval(	$totalesDeFacturas - $comprobante->montoTotalPagado)).'</H3>
			<hr>';
	
			$this->pdf->writeHTML($htmlTotal, true, false, false, false, '');

			$this->pdf->lastPage();
			
			if($this->conf['download']== 'D' ) {  // esta opcion es para que lo descargue
				
				//Close and output PDF document
				$this->pdf->Output($comprobante->referenciaComprobante.'.pdf', $this->conf['download']);

			}else{

				if($this->conf['download']== 'I' ) {    // esta es para que solo lo dibuje y lo muestre en el navegador

					$this->pdf->Output($comprobante->referenciaComprobante.'.pdf', $this->conf['download']);

				}else{  // esta opcion es si quiero que o guarde en el server

					// verifico si existe la carpeta
					$carpeta = DOL_DATA_ROOT.'/comprobantes/'.$comprobante->referenciaComprobante;
					if (!file_exists($carpeta)) {
						mkdir($carpeta, 0777, true);
					}
					
					//Close and output PDF document
					$this->pdf->Output(DOL_DATA_ROOT.'/comprobantes/'.$comprobante->referenciaComprobante.'/'.$comprobante->referenciaComprobante.'.pdf', $this->conf['download']);

				}	



			}
			// ---------------------------------------------------------


		}else{  //si hay algun error imprime pdf de error

			$this->pdf->SetFont('helvetica', '', 10);
		
			// add a page
			$this->pdf->AddPage();

			$txt = '<p><b>'.$valorComprobante['msg'].'</b></p><br>';
	
			$this->pdf->writeHTML($txt, true, false, false, false, '');
			$this->pdf->lastPage();

			$this->pdf->Output('ERROR.pdf', 'I');

		}


    }


}

