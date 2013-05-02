<?php
/*
 * This file is part of the Phoenix package
 *
 * (c) 2011 Martin Piazzon
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'Db.php';
require_once 'Validation.php';

class Model implements Iterator
{
	static protected $_config = array();
	static protected $_autoLoad = true;
	public           $db;
	protected    	 $_primaryKey = 'id';
	protected        $_foreignKey = '_id';
	protected        $_attributes = array();
	protected        $_dirtyAttributes = array();
	protected        $_tableName;
	protected        $_new = true;
	protected        $_sql = array();
	protected        $_fields = array();
	protected        $_updatedFieldname = 'updated_at';
	protected        $_createdFieldname = 'created_at';
	protected        $_hasMany = array();
	protected        $_belongsTo = array();
	protected        $_hasOne = array();
	protected        $_manyToMany = array();
	protected 		 $_executed;
    protected 		 $_queryResults;
	protected        $_lastId;
	protected        $_valid = true; //falso si no pasa validacion
	protected        $_errors = array(); //errores de validacion

	public function __construct(array $attributes = array())
	{

		$this->_query = '';
        $this->_position = 0;
        $this->_executed = false;

		if (method_exists($this , 'init')) {
			$this->init();
		}

		$this->tableName = $this->_getTableName();
		$this->db = Db::factory();
		$this->_fields = $this->_describeTable($this->tableName);
		foreach ($attributes as $attribute => $value) {
			$this->_attributes[$attribute] = $value;
			$this->_dirtyAttributes[$attribute] = $value;
		}
	}

	/**
	 * permite la autocarga de modelos definiendo su ubicacion
	 *
	 * @param string $modelsDir
	 * @return void
	 */
	static public function register($modelsDir = 'models/')
	{
		self::$_config['models_dir'] = $modelsDir;
		spl_autoload_register('Model::loadClass');
	}

	/**
	 * realiza la carga de los modelos
	 *
	 * @param string $class
	 * @return void
	 */
	static public function loadClass($class)
	{
		$class = ucfirst($class);
		if (!class_exists($class)) {
			if (Model::$_autoLoad == true) {
				if (is_file(Model::$_config['models_dir'].$class.'.php')) {
					require_once (Model::$_config['models_dir'].$class.'.php');
				} else {
					die(Model::$_config['models_dir'].$class.'.php no se encontro');
				}
			}
		}
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param string $table
	 * @return array
	 */
	protected function _describeTable($table)
	{
		$info = $this->db->describetable($table);
		foreach ($info as $col) {
			$fields[$col['field']]['name'] = $col['field'];
			$fields[$col['field']]['type'] = $col['type'];
			$fields[$col['field']]['null'] = $col['null'];
			$fields[$col['field']]['key'] = $col['key'];
			$fields[$col['field']]['extra'] = $col['extra'];
			$fields[$col['field']]['default'] = $col['default'];
		}

		return $fields;
	}

	/**
	 * Devuelve el atributo si este existe, o busca si es una relacion
	 *
	 * @param string $name
	 * @return object
	 */
	public function __get($name)
	{
		if (array_key_exists($name , $this->_attributes))
			return $this->_attributes[$name];

		if (in_array($name , $this->_hasMany)) {
			return $this->_hasMany($name , get_class($this).$this->_foreignKey);
		} elseif (array_key_exists($name , $this->_hasMany)) {
			$opts = self::getOptions($this->_hasMany[$name]);
			if (!isset($opts['model']))
				$opts['model'] = $name;
			if (!isset($opts['fk']))
				$opts['fk'] = get_class($this).$this->_foreignKey;

			return $this->_hasMany($opts['model'] , $opts['fk']);
		}

		if (in_array($name , $this->_belongsTo)) {
			return $this->_belongsTo($name , $name.$this->_foreignKey);
		} elseif (array_key_exists($name , $this->_belongsTo)) {
			$opts = self::getOptions($this->_belongsTo[$name]);
			if (!isset($opts['model']))
				$opts['model'] = $name;
			if (!isset($opts['fk']))
				$opts['fk'] = $name.$this->_foreignKey;

			return $this->_belongsTo($opts['model'] , $opts['fk']);
		}

		if (in_array($name , $this->_hasOne)) {
			return $this->_hasOne($name , get_class($this).$this->_foreignKey);
		} elseif (array_key_exists($name , $this->_hasOne)) {
			$opts = self::getOptions($this->_hasOne[$name]);
			if (!isset($opts['model']))
				$opts['model'] = $name;
			if (!isset($opts['fk']))
				$opts['fk'] = get_class($this).$this->_foreignKey;

			return $this->_hasOne($opts['model'] , $opts['fk']);
		}

		if (array_key_exists($name , $this->_manyToMany)) {
			$opts = self::getOptions($this->_manyToMany[$name]);

			return $this->_manyToMany($opts['model'] , $opts['fk'] , $opts['key'] , $opts['through']);
		}
	}

	/**
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name , $value)
	{
		$this->_attributes[$name] = $value;
		$this->_dirtyAttributes[$name] = $value;

		// si es un atributo que representa una relacion hasMany lo prepara
		if ((in_array($name , $this->_hasMany)) OR (array_key_exists($name , $this->_hasMany))) {
			$this->_toDo[$name]['values'] = $value;

			$opts = self::getOptions($this->_hasMany[$name]);

			if (!isset($opts['model']))
				$opts['model'] = $name;
			if (!isset($opts['fk']))
				$opts['fk'] = get_class($this).$this->_foreignKey;
			$this->_toDo[$name]['has_many'] = $opts;
		}
		// si es un atributo que representa una relacion hasOne lo prepara
		if ((in_array($name , $this->_hasOne)) OR (array_key_exists($name , $this->_hasOne))) {
			$this->_toDo[$name]['values'] = $value;

			$opts = self::getOptions($this->_hasMany[$name]);

			if (!isset($opts['model']))
				$opts['model'] = $name;
			if (!isset($opts['fk']))
				$opts['fk'] = get_class($this).$this->_foreignKey;

			$this->_toDo[$name]['has_one'] = $opts;
		}

	}

	/**
	 * Retorna el nombre de la tabla asociada al modelo
	 * Ejemplo, si modelo es TipoAuto lo convierte en  tipo_auto.
	 * @return string
	 */
	protected function _getTableName()
	{
		if (!isset($this->_tableName))
			$this->_tableName = strtolower(preg_replace('/(?<=[a-z])([A-Z])/' , '_$1' , get_class($this)));

		return $this->_tableName;
	}


	/**
	 * retora instancia
	 * recibe nombre de la clase o array para crear nuevo registro
	 */
	static public function factory($param = '')
	{
		if (($param == '') or (is_array($param)))
			$className = get_called_class();
		else
			$className = $param;

		if (is_array($param))
			return new $className($param);
		else
			return new $className();
	}

	static public function __callStatic($method , $args)
	{
		if ($method == 'id') {
			return self::find($opts);
		} elseif (substr($method , 0 , 6) == 'findBy') {
			$attribute = strtolower(substr($method , 6));
			$opts['where'] = "$attribute = '$args[0]'";
			return self::find($opts);
		} elseif (substr($method , 0 , 9) == 'findAllBy') {
			$attribute = strtolower(substr($method , 9));
			$opts['where'] = "$attribute = '$args[0]'";

			if (array_key_exists('order_by' , $args[1]))
				$opts['limit'] = $args[1]['order_by'];
			if (array_key_exists('group_by' , $args[1]))
				$opts['limit'] = $args[1]['group_by'];
			if (array_key_exists('limit' , $args[1]))
				$opts['limit'] = $args[1]['limit'];

			return self::findAll($opts);
		}
	}

	static public function create($attributes = array())
	{
		$className = get_called_class();
		return new $className($attributes);
	}

	public function save($param = true)
	{
		$av = array_intersect_key($this->_attributes , $this->_fields);

		$keys = array_keys($av);
		$values = array_values($av);

		if (in_array($this->_primaryKey , $keys)) {
			$this->_new = false;
			$this->_attributes[$this->_primaryKey] = $av[$this->_primaryKey];
		}

		if ($param == false)
			$this->_valid = true;

		if ($this->_valid) {
			if ($this->_new == true) {
				if (isset($this->_fields[$this->_cretedFieldname]))
					if (!isset($av[$this->_cretedFieldname])) {
						array_push($keys , $this->_cretedFieldname);
						array_push($values , date('Y-m-d H:i:s'));
					}
				if (isset($this->_fields[$this->_updatedFieldname]))
					if (!isset($av[$this->_updatedFieldname])) {
						array_push($keys , $this->_updatedFieldname);
						array_push($values , date('Y-m-d H:i:s'));
					}

					// before save
					if (method_exists($this, "beforeSave")) {
						if ($this->beforeSave() == 'cancel') {
                			return false;
            			}
					}

				if ($this->db->insert($this->tableName , $keys , $values)) {
					$this->_lastId =  $this->db->lastInsertId();

					// after save
					if (method_exists($this, "afterSave")) {
						if ($this->afterSave() == 'cancel') {
                			return false;
            			}
					}
				}

			} else {
				$where = $this->_primaryKey." = ".$this->_attributes[$this->_primaryKey];
				$dirty = array_intersect_key($av , $this->_dirtyAttributes);
				if (isset($this->_fields[$this->_updatedFieldname]))
					if (!isset($dirty[$this->_updatedFieldname]))
						$dirty[$this->_updatedFieldname] = date('Y-m-d H:i:s');

				if (count($dirty) > 0) {
					$keys = array_keys($dirty);
					$values = array_values($dirty);

					// before update
					if (method_exists($this, "beforeUpdate")) {
						if ($this->beforeUpdate() == 'cancel') {
                			return false;
            			}
					}

					$this->db->update($this->tableName , $keys , $values , $where);

					// after update
					if (method_exists($this, "afterUpdate")) {
						if ($this->afterUpdate() == 'cancel') {
                			return false;
            			}
					}
				}
			}
		}
	}

	public function delete()
	{
		// before delete
		if (method_exists($this, "beforeDelete")) {
			if ($this->beforeDelete() == 'cancel') {
            	return false;
            }
		}

		$where = $this->_primaryKey." = ".$this->_attributes[$this->_primaryKey];
		$this->db->delete($this->tableName , $where);

		// after delete
		if (method_exists($this, "afterDelete")) {
			if ($this->afterDelete() == 'cancel') {
            	return false;
            }
		}
	}

	static public function sqlSanizite($sqlItem)
	{
		$sqlItem = trim($sqlItem);
		if ($sqlItem !== '' && $sqlItem !== null) {
			$sqlItem = preg_replace('/\s+/' , '' , $sqlItem);
			if (!preg_match('/^[a-zA-Z_0-9\,\(\)\.\*]+$/' , $sqlItem)) {
				die("Se esta tratando de ejecutar una operacion maliciosa!");
			}
		}
		return $sqlItem;
	}

	public function dumpResult($result)
	{
		$obj = clone $this;

		if (is_array($result))
			foreach ($result as $k => $r)
				if (!is_numeric($k))
					$obj->_attributes[$k] = stripslashes($r);

		return $obj;
	}

	public function getErrors()
	{
		return $this->_errors;
	}

	static public function getOptions($data)
	{
		$opts = explode(';' , $data);
		foreach ($opts as $opt) {
			$match = explode(':' , $opt , 2);
			if (isset($match[1]))
				$arr[trim($match[0])] = trim($match[1]);
		}
		return $arr;
	}


	/*
	 * usado internamente
	 *
	 */
	protected function _where($where , $value)
	{
		$this->_sql['where'] = ' WHERE '.str_replace('?' , "'".addslashes($value)."'" , $where);
		return $this;
	}

	/*
	 * usado internamente
	 *
	 */
	protected function _join($joinOperator , $table , $constraint , $tableAlias = NULL)
	{
		$joinOperator = trim("{$joinOperator} JOIN");

		if (!is_null($tableAlias)) {
			$table_alias = $this->_quote_identifier($tableAlias);
			$table .= " {$tableAlias}";
		}

		if (is_array($constraint)) {
			list($firstColumn , $operator , $secondColumn) = $constraint;
			$constraint = "{$firstColumn} {$operator} {$secondColumn}";
		}

		$this->_sql['join'] = " {$joinOperator} {$table} ON {$constraint}";

		return $this;
	}


	/* -----------------------------------------
	 * metodos estaticos para entontrar objetos
	 * -----------------------------------------
	 *
	 */

	static public function first()
	{
		$model = self::factory();
		$query = "SELECT * FROM ".Model::sqlSanizite($model->tableName);
		$query .= " LIMIT 1 ";
		$results = $model->db->inQuery($query);
		$r = array();
		if (count($results) == 0) {
			return NULL;
		} else {
			return $model->dumpResult($results[0]);
		}
	}

	static public function last()
	{
		$model = self::factory();
		$query = "SELECT * FROM ".Model::sqlSanizite($model->tableName)." ";
		$query .= "ORDER BY id DESC LIMIT 1 ";
		$results = $model->db->inQuery($query);
		$r = array();
		if (count($results) == 0) {
			return NULL;
		} else {
			return $model->dumpResult($results[0]);
		}
	}

	static public function find($opts = NULL)
	{
		$model = self::factory();
		$model->_executed = true;
		$query = "SELECT * FROM ".Model::sqlSanizite($model->tableName);
		if (func_num_args() > 1) {
			$args = implode("," , func_get_args());
			$query .= " WHERE (id IN  (" .$args. "))";
			$results = $model->db->inQuery($query);
			$r = array();
			foreach ($results as $result)
				$r[] = $model->dumpResult($result);
			return $r;
		} else {
			if (is_int($opts))
				$query .= " where $model->_primaryKey = $opts";

			if (is_array($opts))
				if (array_key_exists('where' , $opts))
					$query .= " WHERE ".$opts['where'];

			$query .= " LIMIT 1 ";
			$results = $model->db->inQuery($query);
			$r = array();
			if (count($results) == 0)
				return NULL;
			else
				return $model->dumpResult($results[0]);
		}
	}

	/*
	 * Model::findAll(array("where" => "title = 'martin'","order_by" => 'text','limit'=>2));
	 *
	 */
	static public function findAll($opts = NULL)
	{
		$model = self::factory();
		$model->_executed = true;
		$query = "SELECT * FROM ".Model::sqlSanizite($model->tableName);

		if (is_array($opts)) {
			if (array_key_exists('where' , $opts))
				$query .= " WHERE ".$opts['where'];

			if (array_key_exists('order_by' , $opts))
				$query .= " ORDER BY ".$opts['order_by'];
			if (array_key_exists('group_by' , $opts))
				$query .= " GROUP BY ".$opts['group_by'];
			if (array_key_exists('limit' , $opts))
				$query .= " LIMIT ".$opts['limit'];
		}
		$results = $model->db->inQuery($query);
		$r = array();
		foreach ($results as $result) {
			$r[] = $model->dumpResult($result);
		}
		return $r;
	}

	/*
	 * Model::distinct(array("field" => 'martin'));
	 *
	 */
	static public function distinct($opts = NULL)
	{
		$model = self::factory();
		$model->_executed = true;
		$query = "SELECT DISTINCT {$opts['fields']} FROM ".Model::sqlSanizite($model->tableName);

		if (is_array($opts)) {
			if (array_key_exists('where' , $opts))
				$query .= " WHERE ".$opts['where'];
			if (array_key_exists('order_by' , $opts))
				$query .= " ORDER BY ".$opts['order_by'];
			if (array_key_exists('group_by' , $opts))
				$query .= " GROUP BY ".$opts['group_by'];
			if (array_key_exists('limit' , $opts))
				$query .= " LIMIT ".$opts['limit'];
		}
		$results = $model->db->inQuery($query);
		$model->_new = false;
		$r = array();
		foreach ($results as $result) {
			$r[] = $model->dumpResult($result);
		}
		return $r;
	}

	/* -----------------------------------------
	 * metodos para entontrar objetos
	 * -----------------------------------------
	 *
	 */

	public function findOne()
	{
		$this->_executed = true;
		$query = "SELECT * FROM ".Model::sqlSanizite($this->tableName);
		if (isset($this->_sql['where'])) {
			$query .= $this->_sql['where'];
		}

		$results = $this->db->inQuery($query);
		$this->_new = false;
		$r = array();
		foreach ($results as $result) {
			$r[] = $this->dumpResult($result);
		}

		if (count($r))
			return $r[0];
		else
			return NULL;
	}

	public function findMany()
	{
		$this->_executed = true;
		$query = "SELECT * FROM ".Model::sqlSanizite($this->tableName);

		if (isset($this->_sql['join'])) {
			$query .= $this->_sql['join'];
		}

		if (isset($this->_sql['where'])) {
			$query .= $this->_sql['where'];
		}

		if (isset($this->_sql['and_where'])) {
			$query .= $this->_sql['and_where'];
		}

		if (isset($this->_sql['or_where'])) {
			$query .= $this->_sql['or_where'];
		}

		if (isset($this->_sql['order'])) {
			$query .= $this->_sql['order'];
		}

		if (isset($this->_sql['group'])) {
			$query .= $this->_sql['group'];
		}

		if (isset($this->_sql['limit'])) {
			$query .= $this->_sql['limit'];
		}
		$results = $this->db->inQuery($query);
		$this->_new = false;
		$r = array();
		foreach ($results as $result) {
			$r[] = $this->dumpResult($result);
		}

		return $r;
	}

	/*
	 * relaciones
	 * ------------------------------------------------------------------------------------------------------
	 *
	 */

	protected function _belongsTo($associatedClassName , $foreignKeyName)
	{
		$associatedObjectId = $this->$foreignKeyName;
		return self::factory($associatedClassName)->find(array('where' => $this->_primaryKey." = ".$this->_attributes[$foreignKeyName]));
	}

	protected function _hasOne($associatedClassName , $foreignKeyName)
	{
		return self::factory($associatedClassName)->find(array('where' => $foreignKeyName." = ".$this->_attributes[$this->_primaryKey]));
	}

	protected function _hasMany($associatedClassName , $foreignKeyName)
	{
		return self::factory($associatedClassName)->findAll(array('where' => $foreignKeyName." = ".$this->_attributes[$this->_primaryKey]));
	}

	protected function _manyToMany($associatedClassName , $foreignKeyName = NULL , $key , $through)
	{

		$base_table_name = $this->_tableName;
		$associatedModel = Model::factory($associatedClassName);
		if (isset($associatedModel->tableName))
			$associated_table_name = $associatedModel->tableName;
		else
			$associated_table_name = $associatedClassName;

		$join_table_name = $through;
		$key_to_base_table = $key;
		$key_to_associated_table = $foreignKeyName;

		return self::factory($associatedClassName)->_join('INNER' , $join_table_name , array("{$associated_table_name}.{$this->_primaryKey}" , '=' , "{$join_table_name}.{$key_to_associated_table}"))->_where("{$join_table_name}.{$key_to_base_table} = ?" , $this->_attributes[$this->_primaryKey])->findMany();

	}

	public function getLastId()
	{
		return $this->_lastId;
	}

	public function isValid()
	{
		if (array($this->_rules)) {
			$av = array_intersect_key($this->_attributes , $this->_fields);
			$validator = Validation::factory($this->_rules,$av,get_class($this));
			if (!$validator->getStatus())
				$this->_errors = $validator->getErrors();

			return $validator->getStatus();
		}
	}


	/* query builder */


	public function select($results)
    {
        $this->_query .= " SELECT  $results";
        $this->_sql['select'] = " SELECT $results";
        return $this;
    }

    public function from($param)
    {
       	$this->_sql['from'] = " FROM $param";
        return $this;
    }

    public function where()
    {
        $args = func_get_args();
        $countArgs = func_num_args();
		if ($countArgs > 1)
        {
        	$cont = 1;
        	$oc = explode("?",$args[0]);
        	foreach ($oc as $value)
        	{
        		if ($value != '')
        		{
        			$q .= $value . "'".addslashes($args[$cont])."'";
        			$cont++;
        		}
        	}
       	}
		$this->_sql['where'] = $q;
        return $this;
    }

	public function orderBy($param)
    {
        $this->_sql['order_by'] = " ORDER BY $param";
        return $this;
    }

	public function limit($param)
    {
        $this->_sql['limit'] = " LIMIT $param";
        return $this;
    }

    public function offset($results)
    {
        $this->_sql['offset'] = " OFFSET $param";
        return $this;
    }

    public function __toString()
    {
        return $this->_query;
    }

    public function __invoke()
    {
        $query = (string) $this->__toString();
        //echo "Executing $query";
        $this->_position = 0;
		$this->_executed = true;
		return $this->_queryResults;
    }

    public function isExecuted()
    {
        return $this->_executed;
    }

    public function execQuery()
    {
    	$query = "SELECT ";
		if (isset($this->_sql['select']))
			$query .= $this->_sql['select'];
		else
			$query .= "* ";
		if (isset($this->_sql['from']))
			$query .= $this->_sql['from'];
		else
			$query .= "FROM " . Model::sqlSanizite($this->tableName)." ";

		if (isset($this->_sql['where']))
    		$query .= "WHERE " .$this->_sql['where'];

		if (isset($this->_sql['order_by']))
    		$query .= $this->_sql['order_by'];

    	if (isset($this->_sql['limit']))
    		$query .= $this->_sql['limit'];

    	if (isset($this->_sql['offset']))
    		$query .= $this->_sql['offSet'];

        $this->_position = 0;
        $this->_executed = true;
        $results = $this->db->inQuery($query);
        $r = array();
		if (count($results) == 0) {
			return NULL;
		} else {
			foreach ($results as $result)
				$r[] = $this->dumpResult($result);


			$this->_queryResults =  $r;
		}
    }

    public function rewind()
    {
        if (!$this->isExecuted()) {
            $this->execQuery();
        }
        $this->_position = 0;
    }

    public function current()
    {
        return $this->_queryResults[$this->_position];
    }

    public function key()
    {
        return $this->_position;
    }

    public function next()
    {
        ++$this->_position;
    }

    public function valid()
    {
        return isset($this->_queryResults[$this->_position]);
    }
}
?>
