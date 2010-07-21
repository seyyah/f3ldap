<?php

/**
	SQL database pack for the PHP Fat-Free Framework

	The contents of this file are subject to the terms of the GNU General
	Public License Version 3.0. You may not use this file except in
	compliance with the license. Any of the license terms and conditions
	can be waived if you get permission from the copyright holder.

	Copyright (c) 2009-2010 F3 Factory
	Bong Cosca <bong.cosca@yahoo.com>

		@package SQLDB
		@version 1.3.22
**/

//! SQL database pack
class SQLdb {

	//! Minimum framework version required to run
	const F3_Minimum='1.3.21';

	//@{
	//! Locale-specific error/exception message
	const
		TEXT_DBConnect='Database connection failed',
		TEXT_DBError='Database error - {@CONTEXT}';
	//@}

	/**
		Retrieve from cache; or save SQL query results to cache if not
		previously executed
			@param $_cmd string
			@param $_bind mixed
			@param $_id string
			@param $_ttl integer
			@private
	**/
	private static function sqlCache($_cmd,$_bind=NULL,$_id='DB',$_ttl=0) {
		$_hash='sql.'.F3::hashCode($_cmd);
		$_db=&F3::$global[$_id];
		$_cached=Cache::cached($_hash);
		if ($_cached && (time()-$_cached['time'])<$_ttl) {
			// Gather cached SQL queries for profiler
			F3::$global['PROFILE'][$_id]['cache'][$_cmd]++;
			// Retrieve from cache, unserialize, and restore DB variable
			$_db=unserialize(gzinflate(Cache::fetch($_hash)));
		}
		else {
			self::sqlExec($_cmd,NULL,$_id);
			if (!F3::$global['ERROR']) {
				// Serialize, compress and cache
				unset($_db['pdo'],$_db['query']);
				Cache::store($_hash,gzdeflate(serialize($_db)));
			}
		}
	}

	/**
		Execute SQL statement
			@param $_cmd string
			@param $_bind mixed
			@param $_id string
			@private
	**/
	private static function sqlExec($_cmd,$_bind=NULL,$_id='DB') {
		// Execute SQL statement
		$_db=&F3::$global[$_id];
		if (is_null($_bind))
			$_db['query']=$_db['pdo']->query($_cmd);
		else {
			$_db['query']=$_db['pdo']->prepare($_cmd);
			if (is_object($_db['query'])) {
				foreach ($_bind as $_key=>$_val) {
					if (!is_array($_val))
						$_val=array($_val,PDO::PARAM_STR);
					$_db['query']->bindValue(
						$_key,$_val[0],(isset($_val[1])?$_val[1]:NULL)
					);
				}
				$_db['query']->execute();
			}
		}
		// Check SQLSTATE
		if ($_db['pdo']->errorCode()!='00000') {
			// Gather info about error
			$_error=$_db['pdo']->errorInfo();
			F3::$global['CONTEXT']=
				$_error[0].' ('.$_error[1].') '.$_error[2];
			trigger_error(self::TEXT_DBError);
			return;
		}
		// Gather real SQL queries for profiler
		F3::$global['PROFILE'][$_id]['queries'][$_cmd]++;
		// Save result
		$_db['result']=$_db['query']->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
		Bind values to parameters in SQL statement(s) and execute
			@return mixed
			@param $_cmds mixed
			@param $_bind mixed
			@param $_id string
			@param $_ttl integer
			@public
	**/
	public static function sqlBind($_cmds,$_bind=NULL,$_id='DB',$_ttl=0) {
		$_db=&F3::$global[$_id];
		// Connect to database once
		if (!$_db || !$_db['dsn']) {
			// Can't connect without a DSN!
			trigger_error(self::TEXT_DBConnect);
			return;
		}
		if (!isset($_db['pdo'])) {
			$_ext='pdo_'.stristr($_db['dsn'],':',TRUE);
			if (!in_array($_ext,get_loaded_extensions())) {
				// PHP extension not activated
				F3::$global['CONTEXT']=$_ext;
				trigger_error(F3::TEXT_PHPExt);
				return;
			}
			try {
				$_db['pdo']=new PDO(
					$_db['dsn'],$_db['user'],$_db['password'],
					isset($_db['options'])?:
						array(PDO::ATTR_EMULATE_PREPARES=>FALSE)
				);
			} catch (Exception $_xcpt) {}
			if (!isset($_db['pdo'])) {
				// Unable to connect
				trigger_error(self::TEXT_DBConnect);
				return;
			}
			// Define connection attributes
			$_attrs=explode('|',
				'AUTOCOMMIT|ERRMODE|CASE|CLIENT_VERSION|CONNECTION_STATUS|'.
				'PERSISTENT|PREFETCH|SERVER_INFO|SERVER_VERSION|TIMEOUT'
			);
			// Save attributes in DB global variable
			foreach ($_attrs as $_attr) {
				// Suppress warning if PDO driver doesn't support attribute
				$_val=@$_db['pdo']->
					getAttribute(constant('PDO::ATTR_'.$_attr));
				if ($_val)
					$_db['attributes'][$_attr]=$_val;
			}
		}
		if (!is_array($_cmds))
			// Convert to array to prevent code duplication
			$_cmds=array($_cmds);
		// Remove empty elements
		$_cmds=array_diff($_cmds,array(NULL));
		$_db['result']=NULL;
		if (count($_cmds)>1)
			// More than one SQL statement specified
			$_db['pdo']->beginTransaction();
		foreach ($_cmds as $_cmd) {
			if (F3::$global['ERROR'])
				break;
			$_cmd=F3::resolve($_cmd);
			if ($_ttl)
				// Cache results
				self::sqlCache($_cmd,$_bind,$_id,$_ttl);
			else
				// Execute SQL statement(s)
				self::sqlExec($_cmd,$_bind,$_id);
		}
		if (count($_cmds)>1) {
			$_func=F3::$global['ERROR']?'rollBack':'commit';
			call_user_func(array($_db['pdo'],$_func));
		}
		return $_db['result'];
	}

	/**
		Process SQL statement(s)
			@return mixed
			@param $_cmds mixed
			@param $_id string
			@param $_ttl integer
			@public
	**/
	public static function sql($_cmds,$_id='DB',$_ttl=0) {
		return self::sqlBind($_cmds,NULL,$_id,$_ttl);
	}

	/**
		Return PDO class constant corresponding to data type
			@return integer
			@param $_value mixed
			@public
	**/
	public static function type($_value) {
		if (is_null($_value))
			return PDO::PARAM_NULL;
		elseif (is_bool($_value))
			return PDO::PARAM_BOOL;
		elseif (is_int($_value))
			return PDO::PARAM_INT;
		elseif (is_string($_value))
			return PDO::PARAM_STR;
		return PDO::PARAM_LOB;
	}

	/**
		Class constructor
			@public
	**/
	public function __construct() {
		// Prohibit use of class as an object
		F3::$global['CONTEXT']=__CLASS__;
		trigger_error(F3::TEXT_Object);
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
