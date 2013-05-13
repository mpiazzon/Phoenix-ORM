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

$entradas = Entradas::findAll();
foreach ($entradas as $entrada)
{
	echo $entrada->titulo.'<br>';
	foreach ($entrada->comentarios as $com)
		echo $com->id;
}

?>