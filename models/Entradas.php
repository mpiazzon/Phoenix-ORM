<?php
class Entradas extends Model 
{
	protected $_tableName = "entradas";

	protected $_hasMany = array(
	  "comentarios" => "model: comentarios ; fk: entrada_id",
	);
	
	/*otra opcion seria*/
	//public $hasMany = array("comments"); // el modelo que se utiliza es comments, el mismo q se utlizara como atributo del objeto
	
	protected $_manyToMany = array(
	  "categorias" => "model: categorias ; fk: categoria_id ; key: entrada_id ; through: categorias_entradas",	
	);
	
	protected $_belongsTo = array(
		"usuario" => "model: usuarios ; fk: usuario_id",
	);

	protected $_rules = array(
           "field:titulo ; type:required ; msg:el campo title no puede estar vacio",
           "field:titulo ; type:length ; range:3-15 ; msg: El titulo debe tener entre 3 y 15 caracteres",
           "field:titulo ; type:unique ; msg:el campo esta repetido",
           );	
}
?>