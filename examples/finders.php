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
?>
<h1>Buscar todas las entradas</h1>
<?php
$entradas = Entradas::findAll();

foreach ($entradas as $entrada) {
	echo $entrada->id.'<br>';
}
?>

<h1>Buscar entrada donde id = 1</h1>
<?php
$entrada = Entradas::find(1);
if ($entrada)
	echo $entrada->titulo;
else
	echo "entrada no encontrada";	
?>
<h1>Otras</h1>
<?php
/// multiples condiciones 
/// titulo = ? AND publicado = ?", 'algun titulo',true 
$entradas = Entradas::factory()
	->where('titulo = ? AND usuario_id = ?', 'Entrada numero 1!!',1)
  	->limit(2)
  	->orderBy('id desc');
  	
foreach ($entradas as $entrada)
	echo $entrada->id.'<br>'; 
	 	
?>

<h1>first</h1>
<?php
$e = Entradas::first();
echo $e->titulo;
?>
<h1>last</h1>
<?php
$e = Entradas::last();
echo $e->titulo;
?>

<h1>::id()</h1>
<?php
$a = Entradas::id(1);
echo $a->titulo;
?>
<h1>find(1,2,12)</h1>
<?php
$mama = Entradas::find(1,2,11);
foreach ($mama as $v) {
	echo $v->titulo;
}
?>
