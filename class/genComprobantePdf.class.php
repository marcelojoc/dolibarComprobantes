<?php
// require_once '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once TCPDF_PATH.'tcpdf.php';
require_once DOL_DOCUMENT_ROOT."/comprobantes/class/getComprobantes.class.php";
require_once DOL_DOCUMENT_ROOT."/comprobantes/lib/PHPMailer/PHPMailer.php";
require_once DOL_DOCUMENT_ROOT."/comprobantes/lib/PHPMailer/SMTP.php";
require_once DOL_DOCUMENT_ROOT."/comprobantes/lib/PHPMailer/Exception.php";

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
		$this->pdf->SetCreator(PDF_CREATOR);
		$this->pdf->SetAuthor('Nicola Asuni');
		$this->pdf->SetTitle('PDF COMPROBANTES');
		$this->pdf->SetSubject('TCPDF COMPROBANTES');
		$this->langs = $langs;
		$this->conf = $conf;


	}



    public function dibujar($comp){

		
		// var_dump(DOL_DOCUMENT_ROOT);

		// exit;
		$comprobante = new getComprobantes($this->db);
		
		$valorComprobante= $comprobante->setIdComprobante($comp);
		
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
			
			if($comprobante->objAfip != false){

				$datoAfip= $comprobante->objAfip->ptovta.'-'.str_pad($comprobante->objAfip->nComprobanteAfip, 8, "0", STR_PAD_LEFT);
				$txt.= ' - Afip '.$datoAfip;
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
			  <td><strong>Nota</strong></td>
			  <td><strong>Importe</strong></td>
			</tr>
	
			<tr>
			  <td>'.$comprobante->referenciaFactura.' '.$datoAfip.'</td>
			  <td>'.$comprobante->fechaFactura.'</td>
			  <td> $'.$comprobante->total.'</td>
			  <td>'.$comprobante->medioDePago.' '.$comprobante->numeroDePago.'</td>
			  <td>'.$comprobante->banco.'</td>
			  <td>'.$comprobante->nota.'</td>
			  <td> $'.$comprobante->monto.'</td>
			</tr>
	
	
	
	
	
		  </table>
			';
			
			 $this->pdf->writeHTML($tblDatos, true, false, false, false, '');
	
			$htmlTotal = '
			<H3 align="right"> Total : $ '.$comprobante->monto.' </H3>
			<H3 align="right" color="red"> Pendiente :  $'.(intval($comprobante->total - $comprobante->montoTotalPagado)).'</H3>
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


	// Este metodo realiza el envio de correos desde SMTP a la casilla de correo del cliente
	public function sendMailComprobante()
	{

		$mail = new PHPMailer\PHPMailer\PHPMailer();

		//Luego tenemos que iniciar la validación por SMTP:
		$mail->IsSMTP();
		$mail->SMTPAuth = true;
		$mail->Host = "mail.tmsgroup.com.ar"; // A RELLENAR. Aquí pondremos el SMTP a utilizar. Por ej. mail.midominio.com
		$mail->Username = "marcelo.contreras@tmsgroup.com.ar"; // A RELLENAR. Email de la cuenta de correo. ej.info@midominio.com La cuenta de correo debe ser creada previamente. 
		$mail->Password = "Marcelo.2017"; // A RELLENAR. Aqui pondremos la contraseña de la cuenta de correo
		$mail->Port = 587; // Puerto de conexión al servidor de envio. 
		$mail->From = "facturacion@tmsgroup.com.ar"; // A RELLENARDesde donde enviamos (Para mostrar). Puede ser el mismo que el email creado previamente.
		$mail->FromName = "marcelo.contreras@tmsgroup.com.ar"; //A RELLENAR Nombre a mostrar del remitente. 
		$mail->AddAddress("marcelo.contreras@tmsgroup.com.ar"); // Esta es la dirección a donde enviamos 
		$mail->IsHTML(true); // El correo se envía como HTML 
		$mail->Subject = '“Titulo”'; // Este es el titulo del email. 
		$body = '“Hola mundo. Esta es la primer línea ”'; 
		$body .= '“Aquí continuamos el mensaje”'; 
		
		$mail->Body = $body; // Mensaje a enviar. $exito = $mail->Send(); // Envía el correo.

		if (!$mail->send()) {
			echo "Mailer Error: ‘Hubo un problema. Contacta a un administrador.’ " . $mail->ErrorInfo;
		} else {
			echo "‘El correo fue enviado correctamente";
		}

	}





}

