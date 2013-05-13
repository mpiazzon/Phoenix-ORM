<?php
class Posts extends PhoModel
{
	//protected $_tableName = "";

	protected $_hasMany = array(
	  "comments" => "model: comments ; fk: post_id",
	);

	/*otra opcion seria*/
	//public $hasMany = array("comments"); // el modelo que se utiliza es comments, el mismo q se utlizara como atributo del objeto

	protected $_manyToMany = array(
	  "categories" => "model: categories ; fk: category_id ; key: post_id ; through: categories_posts",
	);

	protected $_belongsTo = array(
		"user" => "model: users ; fk: user_id",
	);

	protected $_rules = array(
           "field:titulo ; type:required ; msg:el campo title no puede estar vacio",
           "field:titulo ; type:length ; range:3-15 ; msg: El titulo debe tener entre 3 y 15 caracteres",
           "field:titulo ; type:unique ; msg:el campo esta repetido",
           );
}
?>