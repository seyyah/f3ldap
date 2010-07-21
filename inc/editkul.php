<?php

// Retrieve matching record
$kul=new Axon('kul');
$kul->load('tc="{@PARAMS.tc}"');
if (!$kul->dry()) {
	// Populate REQUEST global with retrieved values
	$kul->copyTo('REQUEST');
	// Render blog.htm template
	F3::set('pagetitle','Kullanıcıyı güncelle');
	F3::set('template','kul');
	F3::call('render');
}
else
	// Invalid blog entry; display our 404 page
	F3::http404();

?>
