<?php


Db::configure('host','localhost');
Db::configure('username','root');
Db::configure('password','');
Db::configure('name','orm');
Db::configure('type','mysql');


/// para autocarga de modelos indico su ubicacion
Model::register('../models/');


$post = Entradas::find(11);
$post->titulo = 'some title 2';
/*
$com = array();
$com[0]['comentario'] = 'some comment';
$com[1]['comentario'] = 'another comment';
$post->comentarios = $com;
$post->save();	
*/
$com = Comentarios::factory(array('comentario'=>'some comment 2'));
$post->comentarios = $com;
$post->save();		
?>