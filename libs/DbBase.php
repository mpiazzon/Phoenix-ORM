<?php
/*
 * This file is part of the Phoenix package
 *
 * (c) 2011 Martin Piazzon
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
class DbBase 
{
	public function inQuery($sql)
	{
		$q = $this->query($sql);
		$results = array();
		
		if ($q)
			while($row=$this->fetchArray($q))
				$results[] = $row;
		
		return $results;
	}

	public function insert($table, $fields , $values)
	{
		$insertSql = "";
		$values = $this->addQuotes($values);
		
		if(is_array($values)){
			if(is_array($fields)){
				$insertSql = "INSERT INTO $table (".implode(",", $fields).") VALUES (".implode(",", $values).")";
			} else {
				$insertSql = "INSERT INTO $table VALUES (".implode(",", $values).")";
			}
			
			return $this->query($insertSql);
		
		} else{
			die('El segundo parametro para insert no es un Array');
		}
	}

	public function update($table, $fields, $values, $where_condition=NULL)
	{
		$update_sql = "UPDATE $table SET ";
		
		if(count($fields)!=count($values)){
			die('en update');
		}
		
		$i = 0;
		$values = $this->addQuotes($values);
		$update_values = array();
		
		foreach($fields as $field){
			$update_values[] = $field.' = '.$values[$i];
			$i++;
		}
		
		$update_sql.= join(',', $update_values);
		
		if($where_condition!=null){
			$update_sql.= " WHERE $where_condition";
		}
		
		return $this->query($update_sql);
	}

	public function delete($table, $whereCondition)
	{
		if(trim($whereCondition))
			return $this->query("DELETE FROM $table WHERE $where_condition");
		else
			return $this->query("DELETE FROM $table");
	}

	static public function addQuotes($value)
	{
		if (is_array($value)){
			foreach ($value as $k => $v)
				$value[$k] = "'".addslashes($v)."'";
					
			return $value;	
		}
		else		
			return "'".addslashes($value)."'";
	}
}