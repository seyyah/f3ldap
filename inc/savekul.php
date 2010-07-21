<?php

// Reset previous error message, if any
F3::clear('message');

// Form field validation
F3::call(':common');

if (!F3::exists('message')) {
	// No input errors; add record to database
	$blog=new Axon('kul');
	$blog->copyFrom('REQUEST');
	$blog->save();
	// Return to home page; new blog entry should now be there
	F3::reroute('/');	

	/*$payload = json_encode(array(F3::get('REQUEST.title'), F3::get('REQUEST.entry')));
	F3::reroute('http://192.168.140.86/receiver.php?payload=' . $payload );*/
}
// Display the blog form again
F3::call(':createkul');

?>
