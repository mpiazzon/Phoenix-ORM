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

// crear un nuevo regitro (1)

$ee  = Entradas::factory(array("titulo"=> "un nuevo post"));
if ($ee->isValid()) {
	$ee->save();
	echo $ee->getLastId();	
} else { 
	print_r($ee->getErrors());
	echo "<br>";	
}


	
//crear un nuevo registro (2)
$e = Entradas::factory();
$e->titulo = '';
if ($e->isValid()) {
	$e->save();
	echo $e->getLastId();
} else {	
	print_r($e->getErrors());	
	echo "<br>";
}

// crea sin validar

$e = Entradas::factory();
$e->titulo = 'esto es ...';
$e->save(false);

?>