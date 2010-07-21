<?php

// Reset previous error message, if any
F3::clear('message');

// Form field validation
F3::call(':common');

if (!F3::exists('message')) {
	// No input errors; update record
	$blog=new Axon('kul');
	$blog->load('tc="{@PARAMS.tc}"');
	$blog->copyFrom('REQUEST');
	$blog->save();
	// Return to home page
	F3::reroute('/');
}
// Display the blog form again
F3::call(':editkul');

?>
