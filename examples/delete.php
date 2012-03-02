<?php

include '../libs/Model.php';

/*
Db::configure('host','localhost');
Db::configure('username','root');
Db::configure('password','');
Db::configure('name','orm');
Db::configure('type','mysql');
*/

Db::configure('username','root');
Db::configure('password','root');
Db::configure('mysql:host=localhost;dbname=orm');

/// para autocarga de modelos indico su ubicacion
Model::register('../models/');

// obtener entrada con ID = 1
$e = Entradas::find(2);
// si existe la entrada numero 1 la elimino
if ($e) { 
	$e->delete();
	echo "se elimino con exito";
}
else {
	echo "no existe entrada";
}

?>