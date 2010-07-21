<?php

if (F3::get('SESSION.user')) {
	$kul=new Axon('kul');
	// Delete record from database
	$kul->load('tc="{@PARAMS.tc}"');
	$kul->erase();
	// Return to home page
	F3::reroute('/');
}
else {
	// Render blog.htm template
	F3::set('pagetitle','Kullanıcıyı sil');
	F3::set('template','kul');
	F3::call('render');
}

?>
