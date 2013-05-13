<?php
class Users extends PhoModel
{
	//protected $_tableName = "blabla";

	protected $_belongsTo = array(
		"group" => "model: groups; fk: group_id",
	);
}

?>