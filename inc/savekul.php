<?php

// Reset previous error message, if any
F3::clear('message');

// Form field validation
F3::call(':common');


if (!F3::exists('message')) {
	F3::call(':ldap_login');

	$data = F3::get('REQUEST');
	/*
	print_r($data);
	Array ( [cn] => Asad [sn] => Aaaaa [telephonenumber] => Aaaaaaa [postalcode] => Aaaaaaa [userpassword] => Aaaaa [submit] => GÃ¶nder )
	*/
	$r = @myldap_add ($data);	

	// Return to home page; new blog entry should now be there
	F3::reroute('/');	
}
// Display the blog form again
F3::call(':createkul');

?>
