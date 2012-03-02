<?php

include '../libs/Db.php';

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
$e = Entradas::find(1);
if ($e) {
	echo "Titulo: ".$e->titulo.'<br>';
	echo "comentatios de entrda 1<br>";
	foreach ($e->comentarios as $comentario) {
		echo $comentario->comentario.'<br>';
	}
	echo "<br>nombre del usuario: ".$e->usuario->nombre;
	echo "<br>nombre del grupo del usuario: ".$e->usuario->grupo->nombre;
	echo '<br>categorias: <br>';
	foreach ($e->categorias as $cat) {
		echo $cat->nombre.'<br>';
}

}
else {
	echo "no existe entrada";
}
?>