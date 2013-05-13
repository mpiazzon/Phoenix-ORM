<?php

include '../libs/PhoModel.php';


PhoDb::configure('username','root');
PhoDb::configure('password','');
PhoDb::configure('mysql:host=localhost;dbname=phoenix');


/// para autocarga de modelos indico su ubicacion
PhoModel::register('../models/');



$e = Posts::find(1);
if ($e) {
	$e->title = 'algo nuevo 2';
	$e->save();
	echo "se actualizo con exito";
}
else {
	echo "no existe entrada";
}

?>