<?php
/*
 * This file is part of the Phoenix package
 *
 * (c) 2011 Martin Piazzon
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Mysql extends DbBase implements DbBaseInterface  
{
	public $idConnection;
	public $lastResultQuery;
	private $lastQuery;
	public $lastError;

	public function connect($config)
	{
		if(!extension_loaded('mysql')){
			die('Debe cargar la extensión de PHP llamada php_mysql');
		}
		if(!isset($config['port']) || !$config['port']) {
			$config['port'] = 3306;
		}
		
		if($this->idConnection = mysql_connect("{$config['host']}:{$config['port']}", $config['username'], $config['password'], true)){
			if($config['name']!=='') {
				if(!mysql_select_db($config['name'], $this->idConnection)){
					die($this->error());
				}
			}
			return true;
		} else {
			die($this->error());
		}
	}

	
	public function query($sqlQuery)
	{
		
		if(!$this->idConnection){
			$this->connect();
			if(!$this->idConnection){
				return false;
			}
		}

		$this->lastQuery = $sqlQuery;
		if($result_query = mysql_query($sqlQuery, $this->idConnection)){
			$this->lastResultQuery = $resultQuery;
			return $resultQuery;
		} else {
			$this->lastResultQuery = false;
			die($this->error(" al ejecutar <em>\"$sql_query\"</em>"));
		}
	}

	
	public function close()
	{
		if($this->idConnection) {
			return mysql_close();
		}
		return false;
	}


	public function fetchArray($resultQuery='', $opt=MYSQL_BOTH)
	{
		if(!$this->idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		return mysql_fetch_array($resultQuery, $opt);
	}

	
	public function __construct($config)
	{
		$this->connect($config);
	}

	
	public function numRows($resultQuery='')
	{
		if(!$this->idConnection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = mysql_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			die($this->error());
		}
		return false;
	}

	



	public function error($err='')
	{
		if(!$this->idConnection){
			$this->lastError = mysql_error() ? mysql_error() : "[Error Desconocido en MySQL: $err]";
            
			return $this->lastError;
		}
		$this->lastError = mysql_error() ? mysql_error() : "[Error Desconocido en MySQL: $err]";
		$this->lastError.= $err;
       
		return $this->lastError;
	}

	
	public function noError()
	{
		if(!$this->idConnection){
			return false;
		}
		return mysql_errno();
	}

	
	public function lastInsertId($table='', $primaryKey='')
	{
		if(!$this->idConnection){
			return false;
		}
		return mysql_insert_id($this->idConnection);
	}

	
	public function tableExists($table)
	{
		$num = $this->fetchOne("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'");
		return $num[0];
	}

	public function dropTable($table, $ifExists=true)
	{
		if($ifExists){
			return $this->query("DROP TABLE IF EXISTS $table");
		} else {
			return $this->query("DROP TABLE $table");
		}
	}

	public function listTables()
	{
		return $this->inQuery("SHOW TABLES");
	}

	public function describeTable($table){
		return $this->inQuery("SHOW columns FROM `$table`");
	}
    
	
	public function fetchObject($resultQuery=NULL, $class='stdClass'){
		if(!$resultQuery){
			$resultQuery = $this->lastResultQuery;
		}
		return mysql_fetch_object($resultQuery, $class);
	}
	
	public function join()
	{
		
	}
}