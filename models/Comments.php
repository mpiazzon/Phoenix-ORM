<?php
class Comentarios extends PhoModel
{
	protected $_tableName = 'comentarios';


	protected $_belongsTo = array(
		"entrada" => "model: entradas; fk: entrada_id",
	);
	/* otra opcion es esta, donde se toma como model a posts y como fk a posts_id
	public $belongsTo = array("posts");

	*/
}
?>