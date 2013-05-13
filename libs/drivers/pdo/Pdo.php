<?php

require_once 'PdoInterface.php';

abstract class DbPDO extends PhoDbBase implements PDOInterface
{

	protected $pdo;

	public $pdoStatement;

	protected $_lastQuery;

	protected $_lastError;

	protected $_affectedRows;

	const DB_ASSOC = PDO::FETCH_ASSOC;

	const DB_BOTH = PDO::FETCH_BOTH;

	const DB_NUM = PDO::FETCH_NUM;

	public function connect($config)
	{

		if(!extension_loaded('pdo')){
			throw new Exception('Debe cargar la extensión de PHP llamada php_pdo');
		}

		try {
			$this->pdo = new PDO($config['type'] . ":" . $config['dsn'], $config['username'], $config['password']);
			if(!$this->pdo){
				throw new Exception("No se pudo realizar la conexion con $this->db_rbdm");
			}
			if($this->_rbdm!='odbc'){
				$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
				$this->pdo->setAttribute(PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY);
			}
			$this->initialize();
			return true;
		} catch(PDOException $e) {
			throw new Exception($this->error($e->getMessage()));
		}

	}

	public function query($sqlQuery)
	{
		if(!$this->pdo){
			throw new Exception('No hay conexión para realizar esta acción');
		}
		$this->_lastQuery = $sqlQuery;
		$this->pdoStatement = null;
		try {
			if($pdoStatement = $this->pdo->query($sqlQuery)){
				$this->pdoStatement = $pdoStatement;
				return $pdoStatement;
			} else {
				return false;
			}
		}
		catch(PDOException $e) {
			throw new Exception($this->error($e->getMessage()." al ejecutar <em>\"$sql_query\"</em>"));
		}
	}

	public function exec($sqlQuery)
	{
		if(!$this->pdo){
			throw new Exception('No hay conexión para realizar esta acción');
		}
		$this->_lastQuery = $sqlQuery;
		$this->pdoStatement = null;
		try {
			$result = $this->pdo->exec($sqlQuery);
			$this->_affectedRows = $result;
			if($result===false){
				throw new Exception($this->error("$sqlQuery"));
			}
			return $result;
		}
		catch(PDOException $e) {
			throw new Exception($this->error("$sqlQuery"));
		}
	}

	public function close()
	{
		if($this->pdo) {
			unset($this->pdo);
			return true;
		}
		return false;
	}

	public function fetchArray($pdoStatement='', $opt='')
	{
		if($opt==='') {
			$opt = self::DB_BOTH;
		}
		if(!$this->pdo){
			throw new Exception('No hay conexión para realizar esta acción');
		}
		if(!$pdoStatement){
			$pdoStatement = $this->pdoStatement;
			if(!$pdoStatement){
				return false;
			}
		}
		try {
			$pdoStatement->setFetchMode($opt);
			return $pdoStatement->fetch();
		}
		catch(PDOException $e) {
			throw new Exception($this->error($e->getMessage()));
		}
	}

	public function __construct($config)
	{
		$this->connect($config);
	}

	public function numRows($pdoStatement='')
	{
		if($pdoStatement){
			$pdo = clone $pdoStatement;
			return count($pdo->fetchAll(PDO::FETCH_NUM));
		} else {
			return 0;
		}
	}

	public function error($err='')
	{
		if($this->pdo){
			$error = $this->pdo->errorInfo();
			$error = $error[2];
		} else {
			$error = "";
		}
		$this->_lastError.= $error." [".$err."]";

		return $this->_lastError;
	}

	public function noError($number=0)
	{
		if($this->pdo){
			$error = $this->pdo->errorInfo();
			$number = $error[1];
		}
		return $number;
	}

	public function lastInsertId($table='', $primaryKey='')
	{
		if(!$this->pdo){
			return false;
		}
		return $this->pdo->lastInsertId();
	}

	public function insert($table, $fields=null , $values)
	{
		$insertSql = "";
		$values = $this->addQuotes($values);
		if(is_array($values)) {
			if(!count($values)) {
				throw new Exception("Imposible realizar inserción en $table sin datos");
			}
			if(is_array($fields)) {
				$insertSql = "INSERT INTO $table (".join(",", $fields).") VALUES (".join(",", $values).")";
			} else {
				$insertSql = "INSERT INTO $table VALUES (".join(",", $values).")";
			}
			return $this->exec($insertSql);
		} else {
			throw new Exception('El segundo parametro para insert no es un Array');
		}
	}

	public function update($table, $fields, $values, $whereCondition = NULL)
	{
		$values = $this->addQuotes($values);
		$updateSql = "UPDATE $table SET ";
		if(count($fields)!=count($values)){
			throw new Exception('El número de valores a actualizar no es el mismo de los campos');
		}
		$i = 0;
		$updateValues = array();
		foreach($fields as $field){
			$updateValues[] = $field.' = '.$values[$i];
			$i++;
		}
		$updateSql.= join(',', $updateValues);
		if($whereCondition!=null){
			$updateSql.= " WHERE $whereCondition";
		}
		return $this->exec($updateSql);
	}

	public function delete($table, $whereCondition)
	{
		if($whereCondition){
			return $this->exec("DELETE FROM $table WHERE $whereCondition");
		} else {
			return $this->exec("DELETE FROM $table");
		}
	}
}
