<?php

// Retrieve blog entries
$kul=new Axon('kul');
F3::set('entries',$kul->find());

// Use the home.htm template
F3::set('pagetitle','ana sayfa');
F3::set('template','home');
F3::call('render');

?>
