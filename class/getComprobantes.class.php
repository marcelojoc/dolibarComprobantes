<?php


/**
 * 	\file		class/myclass.class.php
 * 	\ingroup	mymodule
 * 	\brief		This file is an example CRUD class file (Create/Read/Update/Delete)
 * 				Put some comments here
 */
// Put here all includes required by your class file

require_once DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php";
// require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
require_once DOL_DOCUMENT_ROOT."/societe/class/societe.class.php";
//require_once DOL_DOCUMENT_ROOT."/product/class/product.class.php";

/**
 * Esta es una clase dependencia con el modulo de facturacion E de afip
 * Si esta activo  lo va a requerir , de otro modo  puede funcionar sin problemas
 */
// if (! empty($conf->facturaelectronica->enabled)) {
	require_once DOL_DOCUMENT_ROOT . '/facturaElectronica/class/consultaFactura.class.php';  // incluir la clase de facturacion electronica
// }

/**
 * Put your class' description here
 */
class getComprobantes // extends CommonObject
{

    private $db; //!< To store db handler
    public $error; //!< To return error code (or message)
    public $id;     // el id de la factura
    public $comprobante;  // este es el numero de comprobante
    public $referenciaFactura;  // el nombre de referencia de la factura ejemplo FA1807-13808
    public $fecha; // fecha actual
    public $fechaVencimiento;  // venc  de la factura generada
    public $fechaFactura;  // fecha de la factura realizada
    public $idCliente;  // id del cleinte 
    public $nombreCliente;  // id del cleinte 
    public $direccionCliente;  // direccion Cliente
    public $total;  /// total de la factura  total_ttc
    public $monto;  // este valor es el monto pagado puede ser inferior al valor total  en ese caso quedaria adeudando
    public $montoTotalPagado;  // este valor es la sumatoria de los pagos para una factura
    public $pagada;  // si esta pagada esta en 1  si no el valor paie  en 0
    public $medioDePago;  // si es cheque o efectivo
    public $numeroDePago;  // numero del cheque
    public $banco;  // si es cheque o efectivo
    public $referenciaComprobante;  // refrerencia del comprobante PAY1807-14074
    public $objAfip= false;  // objeto con todo los datos de la factura Electronica
    public $nota;  // nota del pago realizado


    
    /**
     * Constructor
     *
     * 	@param	DoliDb		$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
        return 1;
    }

    /**
     * Este metodo me setea el numero de comprobante al cual estoy ingresando
     */
    public function setIdComprobante($idComp= null){

        if(!is_null($idComp)){ // si envian algo hay que ver que sea un numero

            $comprobante = intval($idComp);

            if($comprobante > 0){  // si es un numero realiza el proceso de seteo

                $this->comprobante = $comprobante; // seteo el numero de id comprobante

                if($this->getIdFactura() == 1){

                    $resultado=['response'=>true, 'msg'=>'Valor comprobante seteado correctamente'];

                }else{

                    $resultado=['response'=>false, 'msg'=>'El numero de comprobante no tiene una factura asociada'];

                }    

            }else{ // si no es un numero devuelve error

                $resultado=['response'=>false, 'msg'=>'No es un numero Valido'];
            }
            
        }else{ // si no envian nada que salga error

            $resultado=['response'=>false, 'msg'=>'No se puede verificar el numero de identificacion de comprobante'];

        }
        
        
        return $resultado;

    }


    /**
     * Este metodo  toma el id del comprobante  y lo asocia con el id de factura correspondiente
     */
    private function getIdFactura(){

        $sql = "SELECT";
        $sql.= " *";
        $sql.= " FROM " . MAIN_DB_PREFIX . "paiement_facture as p";
        $sql.= " WHERE p.fk_paiement = " . $this->comprobante;
        
        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
   
        if ($resql->num_rows > 0) {

            if ($this->db->num_rows($resql)) {

                $obj = $this->db->fetch_object($resql);

                $this->id = intval($obj->fk_facture);  // coloca el id de la factura asociada

            }
            $this->db->free($resql);

            return 1;

        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);

            return -1;
        }


    }

    /**
     * Este metodo asigna cada valor de la factura a los atributos de la clase
     * 
     */
    public function dataFactura(){

        $factura = new facture($this->db); // instancio la clase factura
        $factura->fetch($this->id); // cargo los datos  para el id de la factura asociada

        $this->referenciaFactura= $factura->ref;
        // $this->fecha=  date('d/m/Y', $factura->date);
        $this->fechaVencimiento=  date('d/m/Y', $factura->date_lim_reglement);
        $this->fechaFactura=  date('d/m/Y', $factura->date_creation); 
        $this->idCliente= $factura->socid;

        $this->total= floatval($factura->total_ttc);
        $this->pagada= $factura->paye;


        $this->getClient();  // asigno los datos del cliente
        $this->getPaiement();   // asigno los datos del PAGO
        $this->montoTotalPagado = $this->getTotalAmount(); // sumatoria de los pagos realizados para esta factura
        $this->getAfip();  // si esta activo el modulo trae el valor de datos electronicos, si no esta trae falso y si no esta activo el paramentro afip queda NULL
    }


    /**
     * En este metodo se asignan los datos del cliente 
     */
    private function getClient(){

        $societe = new Societe($this->db);
        $societe->fetch($this->idCliente);
        $this->nombreCliente = $societe->nom;  
        $this->direccionCliente= $societe->address;  


    }


    /**
     * Este metodo va a agrupar los montos pagados para una misma factura
     * y los trae como un solo monto...  
     * esto se aplica para pagos parciales de facturas o usando diferentes medios de pago
     */
    private function getTotalAmount (){

        $total=0;

        $sql = "SELECT";
        $sql.= " *";
        $sql.= " FROM " . MAIN_DB_PREFIX . "paiement_facture as p";
        $sql.= " WHERE p.fk_facture = " . $this->id;
        
        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
   
        if ($resql->num_rows > 0) {

            while ($obj = $this->db->fetch_object($resql)) {
               
                $total+= floatval($obj->amount);
            }
            
            $this->db->free($resql);

            return $total;

        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);

            return false;
        }

    }



// este metodo  trae todos los valores de la tabla paiement 
// 
    private function getPaiement(){

        // SELECT * FROM llx_paiement AS p , llx_bank AS b WHERE  p.fk_bank = b.rowid AND  p.rowid = 14083
        $sql = "SELECT";
        $sql.= " p.rowid,p.ref, p.datep, p.amount, p.fk_paiement, p.note, p.num_paiement , ";
        $sql.= " b.banque ";
        $sql.= " FROM " . MAIN_DB_PREFIX . "paiement as p ,";
        $sql.=   MAIN_DB_PREFIX . "bank as b ";
        $sql.= " WHERE p.fk_bank = b.rowid ";
        $sql.= " AND p.rowid = " . $this->comprobante;
        

        // echo $sql;
        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
   

        if ($resql->num_rows > 0) {

            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->monto= floatval($obj->amount);
                $medio = $obj->fk_paiement;
                $this->numeroDePago = $obj->num_paiement;
                $this->banco = $obj->banque;
                $this->referenciaComprobante = $obj->ref;
                $this->fecha=  date('d/m/Y', $obj->datep);
                $this->nota= $obj->note;
                
            }

            switch ($medio) {
                case '4':
                    $this->medioDePago ='EFECTIVO';
                    break;
                case '7':
                    $this->medioDePago ='CHEQUE';
                    break;
                case '6':
                    $this->medioDePago ='C.BANCARIA';
                    break;
                case '54':
                    $this->medioDePago ='RETENCION';
                    break;
                case '55':
                    $this->medioDePago ='TRANS.BANCARIA';
                    break;
                default:
                $this->medioDePago ='SIN DEFINIR';
            }
    
            $this->db->free($resql);

            return $total;

        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);

            return false;
        }






    }










    //region Afip


    /**
     * Este metodo asigna los valores de acuerdo a si esta validada en Afip o no
     */
    public function getAfip(){

        
        // if (! empty($conf->facturaelectronica->enabled)) {

            // instancio la clase de consulta con la instancia de base de datos y el id de factura
            $afip = new consultaFactura($this->db, $this->id );
            $this->objAfip = $afip->checkValidation();
            return $afip->checkValidation();

        // }
    }


    

    //endregion Afip



















    public function prueba(){

        $factura= new facture($this->db);
        
        var_dump($factura->fetch(14146));
        var_dump($factura->paye);
        var_dump($factura->fk_soc);
        var_dump($factura->total_ttc);
        var_dump($factura->socid);
        var_dump($factura->ref);

        var_dump($factura->ref);
        var_dump($factura->ref);



    }
    /**
     * Create object into database
     *
     * 	@param		User	$user		User that create
     * 	@param		int		$notrigger	0=launch triggers after, 1=disable triggers
     * 	@return		int					<0 if KO, Id of created object if OK
     */
    public function create($user, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        // Clean parameters
        if (isset($this->prop1)) {
            $this->prop1 = trim($this->prop1);
        }
        if (isset($this->prop2)) {
            $this->prop2 = trim($this->prop2);
        }

        // Check parameters
        // Put here code to add control on parameters values
        // Insert request
        $sql = "INSERT INTO " . MAIN_DB_PREFIX . "mytable(";
        $sql.= " field1,";
        $sql.= " field2";

        $sql.= ") VALUES (";
        $sql.= " '" . $this->prop1 . "',";
        $sql.= " '" . $this->prop2 . "'";

        $sql.= ")";

        $this->db->begin();

        dol_syslog(get_class($this) . "::create sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (! $resql) {
            $error ++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (! $error) {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX . "mytable");

            if (! $notrigger) {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action call a trigger.
                //// Call triggers
                //include_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
                //$interface=new Interfaces($this->db);
                //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
                //if ($result < 0) { $error++; $this->errors=$interface->errors; }
                //// End call triggers
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(get_class($this) . "::create " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();

            return -1 * $error;
        } else {
            $this->db->commit();

            return $this->id;
        }
    }

    /**
     * Load object in memory from database
     *
     * 	@param		int		$id	Id object
     * 	@return		int			<0 if KO, >0 if OK
     */
    public function fetch($id)
    {
        global $langs;
        $sql = "SELECT";
        $sql.= " t.rowid,";
        $sql.= " t.field1,";
        $sql.= " t.field2";
        //...
        $sql.= " FROM " . MAIN_DB_PREFIX . "mytable as t";
        $sql.= " WHERE t.rowid = " . $id;

        dol_syslog(get_class($this) . "::fetch sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if ($resql) {
            if ($this->db->num_rows($resql)) {
                $obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;
                $this->prop1 = $obj->field1;
                $this->prop2 = $obj->field2;
                //...
            }
            $this->db->free($resql);

            return 1;
        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);

            return -1;
        }
    }

    /**
     * Update object into database
     *
     * 	@param		User	$user		User that modify
     * 	@param		int		$notrigger	0=launch triggers after, 1=disable triggers
     * 	@return		int					<0 if KO, >0 if OK
     */
    public function update($user = 0, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        // Clean parameters
        if (isset($this->prop1)) {
            $this->prop1 = trim($this->prop1);
        }
        if (isset($this->prop2)) {
            $this->prop2 = trim($this->prop2);
        }

        // Check parameters
        // Put here code to add control on parameters values
        // Update request
        $sql = "UPDATE " . MAIN_DB_PREFIX . "mytable SET";
        $sql.= " field1=" . (isset($this->field1) ? "'" . $this->db->escape($this->field1) . "'" : "null") . ",";
        $sql.= " field2=" . (isset($this->field2) ? "'" . $this->db->escape($this->field2) . "'" : "null") . "";

        $sql.= " WHERE rowid=" . $this->id;

        $this->db->begin();

        dol_syslog(get_class($this) . "::update sql=" . $sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
        if (! $resql) {
            $error ++;
            $this->errors[] = "Error " . $this->db->lasterror();
        }

        if (! $error) {
            if (! $notrigger) {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action call a trigger.
                //// Call triggers
                //include_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
                //$interface=new Interfaces($this->db);
                //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
                //if ($result < 0) { $error++; $this->errors=$interface->errors; }
                //// End call triggers
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(get_class($this) . "::update " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();

            return -1 * $error;
        } else {
            $this->db->commit();

            return 1;
        }
    }

    /**
     * Delete object in database
     *
     * 	@param		User	$user		User that delete
     * 	@param		int		$notrigger	0=launch triggers after, 1=disable triggers
     * 	@return		int					<0 if KO, >0 if OK
     */
    public function delete($user, $notrigger = 0)
    {
        global $conf, $langs;
        $error = 0;

        $this->db->begin();

        if (! $error) {
            if (! $notrigger) {
                // Uncomment this and change MYOBJECT to your own tag if you
                // want this action call a trigger.
                //// Call triggers
                //include_once DOL_DOCUMENT_ROOT . "/core/class/interfaces.class.php";
                //$interface=new Interfaces($this->db);
                //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
                //if ($result < 0) { $error++; $this->errors=$interface->errors; }
                //// End call triggers
            }
        }

        if (! $error) {
            $sql = "DELETE FROM " . MAIN_DB_PREFIX . "mytable";
            $sql.= " WHERE rowid=" . $this->id;

            dol_syslog(get_class($this) . "::delete sql=" . $sql);
            $resql = $this->db->query($sql);
            if (! $resql) {
                $error ++;
                $this->errors[] = "Error " . $this->db->lasterror();
            }
        }

        // Commit or rollback
        if ($error) {
            foreach ($this->errors as $errmsg) {
                dol_syslog(get_class($this) . "::delete " . $errmsg, LOG_ERR);
                $this->error.=($this->error ? ', ' . $errmsg : $errmsg);
            }
            $this->db->rollback();

            return -1 * $error;
        } else {
            $this->db->commit();

            return 1;
        }
    }

    /**
     * Load an object from its id and create a new one in database
     *
     * 	@param		int		$fromid		Id of object to clone
     * 	@return		int					New id of clone
     */
    public function createFromClone($fromid)
    {
        global $user, $langs;

        $error = 0;

        $object = new SkeletonClass($this->db);

        $this->db->begin();

        // Load source object
        $object->fetch($fromid);
        $object->id = 0;
        $object->statut = 0;

        // Clear fields
        // ...
        // Create clone
        $result = $object->create($user);

        // Other options
        if ($result < 0) {
            $this->error = $object->error;
            $error ++;
        }

        if (! $error) {
            // Do something
        }

        // End
        if (! $error) {
            $this->db->commit();

            return $object->id;
        } else {
            $this->db->rollback();

            return -1;
        }
    }

    /**
     * Initialise object with example values
     * Id must be 0 if object instance is a specimen
     *
     * 	@return		void
     */
    public function initAsSpecimen()
    {
        $this->id = 0;
        $this->prop1 = 'prop1';
        $this->prop2 = 'prop2';
    }
}
