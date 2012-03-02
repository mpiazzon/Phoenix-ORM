<?php
require_once 'Pdo.php';

class PdoMySQL extends DbPDO {

	protected $_rbdm = "mysql";

	protected $_port = 3306;

	public function initialize(){

	}

	public function tableExists($table, $schema='')
	{
		$table = addslashes("$table");
		if($schema==''){
			$num = $this->fetchOne("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'");
		} else {
			$schema = addslashes("$schema");
			$num = $this->fetchOne("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA = '$schema'");
		}
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

	public function describeTable($table) 
	{
		return $this->inQuery("SHOW columns FROM `$table`");
	}

}
