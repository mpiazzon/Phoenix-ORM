<?php
class Usuarios extends Model 
{
	protected $_tableName = "usuarios";
	
	
	protected $_belongsTo = array(
		"grupo" => "model: grupos; fk: grupo_id",
	);
}

?>