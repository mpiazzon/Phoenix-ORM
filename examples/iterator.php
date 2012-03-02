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
?>
<h1>Iteretor</h1>
<?php


$entradas = Entradas::factory()->limit(10)->offset(0);
foreach ($entradas as $e)
{
	echo $e->titulo;
	foreach ($e->comentarios as $com)
		echo $com->id;	
}
?>


