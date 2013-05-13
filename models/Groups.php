<?php
class Grupos extends PhoModel
{
	protected $_tableName = "grupos";


	protected $_hasMany = array(
	  "usuarios" => "model: usuarios; fk: grupo_id",
	);
}

?>