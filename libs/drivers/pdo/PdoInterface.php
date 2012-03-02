<?php
interface PdoInterface {
	public function initialize();
	public function connect($config);
	public function query($sql);
	public function exec($sql);
	public function fetchArray($resultQuery='', $opt='');
	public function close();
	public function numRows($resultQuery='');
	public function error($err='');
	public function noError($number=0);
	public function inQuery($sql);
	public function insert($table, $fields, $value);
	public function update($table, $fields, $values, $where_condition=null);
	public function delete($table, $where_condition);
	public function describeTable ($table);
    public function lastInsertId ($table = '', $primary_key = '');
    public function dropTable ($table, $if_exists = false);
    public function tableExists ($table);

}
