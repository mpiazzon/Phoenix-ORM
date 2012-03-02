<?php
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

$entradas = Entradas::factory()
	->where('titulo = ?', 'martin')
  	->limit(2)
  	->orderBy('id','desc')
  	->findMany();
 
if (count($entradas) > 0)  
	foreach ($entradas as $entrada)
		echo $entrada->id.'<br>';

////////////////////////////////////////////////////////////////
/*
$entradas = Entradas::findAll();
foreach ($entradas as $entrada)
	echo $entrada->titulo.'<br>';
*/	
?>