<?php

/**
	Axon ORM for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2010 F3 Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package Auth
		@version 1.3.21
**/

//! Axon Object Relational Mapper
class Axon {

	//! Minimum framework version required to run
	const F3_Minimum='1.3.21';

	//@{
	//! Locale-specific error/exception messages
	const
		TEXT_AxonTable='Unable to map table {@CONTEXT} to Axon',
		TEXT_AxonEmpty='Axon is empty',
		TEXT_AxonNotMapped='The field {@CONTEXT} does not exist',
		TEXT_AxonCantUndef='Cannot undefine an Axon-mapped field',
		TEXT_AxonCantUnset='Cannot unset an Axon-mapped field',
		TEXT_AxonConflict='Name conflict with Axon-mapped field',
		TEXT_AxonInvalid='Invalid virtual field expression',
		TEXT_AxonReadOnly='Virtual fields are read-only',
		TEXT_AxonEngine='Database engine is not supported';
	//@}

	//@{
	//! Axon properties
	private $db=NULL;
	private $table=NULL;
	private $keys=array();
	private $criteria=NULL;
	private $order=NULL;
	private $offset=NULL;
	private $fields=array();
	private $virtual=array();
	private $empty=TRUE;
	//@}

	/**
		Similar to Axon->find method but provides more fine-grained control
		over specific fields and grouping of results
			@param $_fields string
			@param $_criteria mixed
			@param $_grouping mixed
			@param $_order mixed
			@param $_limit mixed
			@param $_ttl integer
			@public
	**/
	public function lookup(
		$_fields,
		$_criteria=NULL,
		$_grouping=NULL,
		$_order=NULL,
		$_limit=NULL,
		$_ttl=0) {
			return F3::sql(
				'SELECT '.$_fields.' FROM '.$this->table.
					(is_null($_criteria)?'':(' WHERE '.$_criteria)).
					(is_null($_grouping)?'':(' GROUP BY '.$_grouping)).
					(is_null($_order)?'':(' ORDER BY '.$_order)).
					(is_null($_limit)?'':(' LIMIT '.$_limit)).';',
				$this->db,
				$_ttl
			);
	}

	/**
		Alias of the lookup method
			@public
	**/
	public function select() {
		// PHP doesn't allow direct use as function argument
		$_args=func_get_args();
		return call_user_func_array(array($this,'lookup'),$_args);
	}

	/**
		Return an array of DB records matching criteria
			@return array
			@param $_criteria mixed
			@param $_order mixed
			@param $_limit mixed
			@param $_ttl integer
			@public
	**/
	public function find(
		$_criteria=NULL,
		$_order=NULL,
		$_limit=NULL,
		$_ttl=0) {
			return $this->lookup('*',$_criteria,NULL,$_order,$_limit,$_ttl);
	}

	/**
		Return number of DB records that match criteria
			@return integer
			@param $_criteria mixed
			@public
	**/
	public function found($_criteria=NULL) {
		$_result=$this->lookup('COUNT(*) AS found',$_criteria);
		return $_result[0]['found'];
	}

	/**
		Hydrate Axon with elements from framework array variable, keys of
		which must be identical to field names in DB record
			@param $_name string
			@public
	**/
	public function copyFrom($_name) {
		foreach (array_keys($this->fields) as $_field)
			if (is_array(F3::get($_name)) &&
				array_key_exists($_field,F3::get($_name)))
					$this->fields[$_field]=F3::get($_name.'.'.$_field);
		$this->empty=FALSE;
	}

	/**
		Populate framework array variable with Axon properties, keys of
		which will have names identical to fields in DB record
			@param $_name string
			@param $_fields string
			@public
	**/
	public function copyTo($_name,$_fields=NULL) {
		if (is_string($_fields))
			$_list=explode('|',$_fields);
		foreach (array_keys($this->fields) as $_field)
			if (!isset($_list) || in_array($_field,$_list))
				F3::set($_name.'.'.$_field,$this->fields[$_field]);
	}

	/**
		Dehydrate Axon
			@public
	**/
	public function reset() {
		// Null out fields
		foreach (array_keys($this->fields) as $_field)
			$this->fields[$_field]=NULL;
		if ($this->keys)
			// Null out primary keys
			foreach (array_keys($this->keys) as $_field)
				$this->keys[$_field]=NULL;
		if ($this->virtual)
			// Null out primary keys
			foreach (array_keys($this->virtual) as $_field)
				unset($this->virtual[$_field]['value']);
		// Dehydrate Axon
		$this->empty=TRUE;
		$this->criteria=NULL;
		$this->order=NULL;
		$this->offset=NULL;
	}

	/**
		Retrieve first DB record that satisfies criteria
			@param $_criteria mixed
			@param $_order mixed
			@param $_offset integer
			@public
	**/
	public function load($_criteria=NULL,$_order=NULL,$_offset=0) {
		if (method_exists($this,'beforeLoad'))
			// Execute beforeLoad event
			$this->beforeLoad();
		if (!is_null($_offset) && $_offset>-1) {
			$_virtual='';
			foreach ($this->virtual as $_field=>$_value)
				$_virtual.=',('.$_value['expr'].') AS '.$_field;
			// Retrieve record
			$_result=$this->lookup(
				'*'.$_virtual,$_criteria,NULL,$_order,'1 OFFSET '.$_offset
			);
			$this->offset=NULL;
			if ($_result) {
				// Hydrate Axon
				foreach ($_result[0] as $_field=>$_value) {
					if (array_key_exists($_field,$this->fields)) {
						$this->fields[$_field]=$_value;
						if (array_key_exists($_field,$this->keys))
							$this->keys[$_field]=$_value;
					}
					else
						$this->virtual[$_field]['value']=$_value;
				}
				$this->empty=FALSE;
				$this->criteria=$_criteria;
				$this->order=$_order;
				$this->offset=$_offset;
			}
			else
				$this->reset();
		}
		else
			$this->reset();
		if (method_exists($this,'afterLoad'))
			// Execute afterLoad event
			$this->afterLoad();
	}

	/**
		Retrieve N-th record relative to current using the same criteria
		that hydrated the Axon
			@param $_count integer
			@public
	**/
	public function skip($_count=1) {
		if ($this->dry()) {
			trigger_error(self::TEXT_AxonEmpty);
			return;
		}
		$this->load($this->criteria,$this->order,$this->offset+$_count);
	}

	/**
		Insert/update DB record
			@public
	**/
	public function save() {
		if ($this->empty) {
			// Axon is empty
			trigger_error(self::TEXT_AxonEmpty);
			return;
		}
		if (method_exists($this,'beforeSave'))
			// Execute beforeSave event
			$this->beforeSave();
		$_new=TRUE;
		if ($this->keys)
			// If ALL primary keys are NULL, this is a new record
			foreach ($this->keys as $_value)
				if (!is_null($_value)) {
					$_new=FALSE;
					break;
				}
		if ($_new) {
			// Insert new record
			$_fields='';
			$_values='';
			foreach ($this->fields as $_field=>$_value) {
				$_fields.=($_fields?',':'').$_field;
				$_values.=($_values?',':'').':'.$_field;
				$_bind[':'.$_field]=array($_value,SQLdb::type($_value));
			}
			F3::sqlBind(
				'INSERT INTO '.$this->table.' ('.$_fields.') '.
					'VALUES ('.$_values.');',
				$_bind,$this->db
			);
		}
		else {
			// Update record
			$_set='';
			foreach ($this->fields as $_field=>$_value) {
				$_set.=($_set?',':'').($_field.'=:'.$_field);
				$_bind[':'.$_field]=array($_value,SQLdb::type($_value));
			}
			// Use prior primary key values (if changed) to find record
			$_cond='';
			foreach ($this->keys as $_key=>$_value) {
				$_cond.=($_cond?' AND ':'').($_key.'=:c_'.$_key);
				$_bind[':c_'.$_key]=array($_value,SQLdb::type($_value));
			}
			F3::sqlBind(
				'UPDATE '.$this->table.' SET '.$_set.
					(is_null($_cond)?'':(' WHERE '.$_cond)).';',
				$_bind,$this->db
			);
		}
		if ($this->keys) {
			// Update primary keys with new values
			foreach (array_keys($this->keys) as $_field)
				$this->keys[$_field]=$this->fields[$_field];
		}
		if (method_exists($this,'afterSave'))
			// Execute afterSave event
			$this->afterSave();
	}

	/**
		Delete DB record and reset Axon
			@public
	**/
	public function erase() {
		if ($this->empty) {
			trigger_error(self::TEXT_AxonEmpty);
			return;
		}
		if (method_exists($this,'beforeErase'))
			// Execute beforeErase event
			$this->beforeErase();
		$_cond=$this->criteria;
		F3::sql(
			'DELETE FROM '.$this->table.
				(is_null($_cond)?'':(' WHERE '.$_cond)).';',
			$this->db
		);
		$this->reset();
		if (method_exists($this,'afterErase'))
			// Execute afterErase event
			$this->afterErase();
	}

	/**
		Return TRUE if Axon is devoid of values in its properties
			@return boolean
			@public
	**/
	public function dry() {
		return $this->empty;
	}

	/**
		Synchronize Axon and table structure
			@param $_table string
			@param $_id string
			@public
	**/
	public function sync($_table,$_id='DB') {
		$_db=&F3::$global[$_id];
		// Can't proceed until DSN is set
		if (!$_db || !$_db['dsn']) {
			trigger_error(SQLdb::TEXT_DBConnect);
			return;
		}
		// MySQL schema
		if (preg_match('/^mysql\:/',$_db['dsn'])) {
			$_cmd='SHOW columns FROM '.$_table.';';
			$_fields=array('Field','Key','PRI');
		}
		// SQLite schema
		elseif (preg_match('/^sqlite[2]*\:/',$_db['dsn'])) {
			$_cmd='PRAGMA table_info('.$_table.');';
			$_fields=array('name','pk',1);
		}
		// SQL Server/Sybase/DBLib/ProgreSQL schema
		elseif (preg_match('/^(mssql|sybase|dblib|pgsql)\:/',$_db['dsn'])) {
			$_cmd='SELECT C.column_name AS field,T.constraint_type AS key '.
				'FROM information_schema.columns C '.
				'LEFT OUTER JOIN information_schema.key_column_usage K '.
					'ON C.table_name=K.table_name AND '.
						'C.column_name=K.column_name '.
				'LEFT OUTER JOIN information_schema.table_constraints T '.
					'ON K.table_name=T.table_name AND '.
						'K.constraint_name=T.constraint_name '.
				'WHERE C.table_name="'.$_table.'";';
			$_fields=array('field','key','PRIMARY KEY');
		}
		// Unsupported DB engine
		else {
			trigger_error(self::TEXT_AxonEngine);
			return;
		}
		if (method_exists($this,'beforeSync'))
			// Execute beforeSync event
			$this->beforeSync();
		$_result=F3::sql($_cmd,$_id,F3::$global['SYNC']);
		if (!$_result) {
			F3::$global['CONTEXT']=$_table;
			trigger_error(self::TEXT_AxonTable);
			return;
		}
		// Initialize Axon
		$this->db=$_id;
		$this->table=$_table;
		foreach ($_result as $_col) {
			// Populate properties
			$this->fields[$_col[$_fields[0]]]=NULL;
			if ($_col[$_fields[1]]==$_fields[2])
				// Save primary key
				$this->keys[$_col[$_fields[0]]]=NULL;
		}
		$this->empty=TRUE;
		if (method_exists($this,'afterSync'))
			// Execute afterSync event
			$this->afterSync();
	}

	/**
		Create a virtual field
			@param $_name string
			@param $_expr string
			@public
	**/
	public function def($_name,$_expr) {
		if (array_key_exists($_name,$this->fields)) {
			trigger_error(self::TEXT_AxonConflict);
			return;
		}
		if (!is_string($_expr) || !strlen($_expr)) {
			trigger_error(self::TEXT_AxonInvalid);
			return;
		}
		$this->virtual[$_name]['expr']=F3::resolve($_expr);
	}

	/**
		Destroy a virtual field
			@param $_name string
			@public
	**/
	public function undef($_name) {
		if (array_key_exists($_name,$this->fields)) {
			trigger_error(self::TEXT_AxonCantUndef);
			return;
		}
		if (!array_key_exists($_name,$this->virtual)) {
			F3::$global['CONTEXT']=$_name;
			trigger_error(self::TEXT_AxonNotMapped);
			return;
		}
		unset($this->virtual[$_name]);
	}

	/**
		Return TRUE if virtual field exists
			@param $_name
			@public
	**/
	public function isdef($_name) {
		return array_key_exists($_name,$this->virtual);
	}

	/**
		Return value of Axon-mapped/virtual field
			@return boolean
			@param $_name string
			@public
	**/
	public function __get($_name) {
		if (array_key_exists($_name,$this->fields))
			return $this->fields[$_name];
		if (array_key_exists($_name,$this->virtual))
			return $this->virtual[$_name]['value'];
		F3::$global['CONTEXT']=$_name;
		trigger_error(self::TEXT_AxonNotMapped);
	}

	/**
		Assign value to Axon-mapped field
			@return boolean
			@param $_name string
			@param $_value mixed
			@public
	**/
	public function __set($_name,$_value) {
		if (array_key_exists($_name,$this->fields)) {
			$this->fields[$_name]=is_string($_value)?
				F3::resolve($_value):$_value;
			if (!is_null($_value))
				// Axon is now hydrated
				$this->empty=FALSE;
			return;
		}
		if (array_key_exists($_name,$this->virtual)) {
			trigger_error(self::TEXT_AxonReadOnly);
			return;
		}
		F3::$global['CONTEXT']=$_name;
		trigger_error(self::TEXT_AxonNotMapped);
	}

	/**
		Clear value of Axon-mapped field
			@return boolean
			@param $_name string
			@public
	**/
	public function __unset($_name) {
		if (array_key_exists($_name,$this->fields)) {
			trigger_error(self::TEXT_AxonCantUnset);
			return;
		}
		F3::$global['CONTEXT']=$_name;
		trigger_error(self::TEXT_AxonNotMapped);
	}

	/**
		Return TRUE if Axon-mapped/virtual field exists
			@return boolean
			@param $_name string
			@public
	**/
	public function __isset($_name) {
		return array_key_exists(
			$_name,array_merge($this->fields,$this->virtual)
		);
	}

	/**
		Display class name if conversion to string is attempted
			@public
	**/
	public function __toString() {
		return get_class($this);
	}

	/**
		Mapper constructor
			@public
	**/
	public function __construct() {
		// Execute mandatory sync method of child class
		call_user_func_array(
			array(get_called_class(),'sync'),func_get_args()
		);
	}

	/**
		Intercept calls to undefined object methods
			@param $_func string
			@param $_args array
			@public
	**/
	public function __call($_func,array $_args) {
		F3::$global['CONTEXT']=$_func;
		trigger_error(F3::TEXT_Method);
	}

	/**
		Intercept calls to undefined static methods
			@return mixed
			@param $_func string
			@param $_args array
			@public
	**/
	public static function __callStatic($_func,array $_args) {
		F3::$global['CONTEXT']=__CLASS__.'::'.$_func;
		trigger_error(F3::TEXT_Method);
	}

}

?>
