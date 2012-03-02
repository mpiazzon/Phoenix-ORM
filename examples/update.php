<?php
/*
	Ejemplo 2 , crear un nuevo registro 
*/

include 'Db.php';

include 'Model.php';

include 'Validation.php';


Db::configure('host','localhost');
Db::configure('username','root');
Db::configure('password','');
Db::configure('name','orm');
Db::configure('type','mysql');


/// para autocarga de modelos indico su ubicacion
Model::register('models/');

// obtener entrada con ID = 1
$e = Entradas::find(11);
// si existe la entrada numero 1 , actualizo titulo
if ($e) { 
	$e->titulo = 'algo nuevo 2';
	$e->save();
	echo "se actualizo con exito";
}
else {
	echo "no existe entrada";
}

?>