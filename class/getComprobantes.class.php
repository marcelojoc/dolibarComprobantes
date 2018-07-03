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

/**
 * 	\file		class/myclass.class.php
 * 	\ingroup	mymodule
 * 	\brief		This file is an example CRUD class file (Create/Read/Update/Delete)
 * 				Put some comments here
 */
// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT."/compta/facture/class/facture.class.php";
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
//require_once DOL_DOCUMENT_ROOT."/societe/class/societe.class.php";
//require_once DOL_DOCUMENT_ROOT."/product/class/product.class.php";

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
    public $fecha;
    public $idCliente;
    public $fechaFactura;  // fecha de la factura realizada
    public $total;  /// total de la factura  total_ttc
    public $pagada;  // si esta pagada esta en 1  si no el valor paie  en 0
    // public $total;  /// total de la factura  total_ttc
    // public $total;  /// total de la factura  total_ttc





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

                
                $this->id = intval($obj->fk_facture);

            }
            $this->db->free($resql);

            return 1;

        } else {
            $this->error = "Error " . $this->db->lasterror();
            dol_syslog(get_class($this) . "::fetch " . $this->error, LOG_ERR);

            return -1;
        }


    }

    public function dataFactura(){


        $factura = new facture($this->db); // instancio la clase factura

        $factura->fetch($this->id); // cargo los datos  para el id de la factura asociada

        $this->referenciaFactura= $factura->ref;
        $this->fecha= $factura->date;
        $this->idCliente= $factura->socid;
        $this->fechaFactura= $factura->date_creation;
        $this->total= $factura->total_ttc;



        $this->referenciaFactura= $factura->paye;
        $this->referenciaFactura= $factura->paye;
        var_dump($factura->paye);
        var_dump($factura->fk_soc);
        var_dump($factura->total_ttc);
        var_dump($factura->socid);
        var_dump($factura->ref);

var_dump($factura);

    }



    // public function getClient($this->idCliente){



    // }























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
