<?php

// Reset previous error message, if any
F3::clear('message');

// Form field validation
F3::call(':common');

if (!F3::exists('message')) {
	F3::call(':ldap_login');
	
	$data = F3::get('REQUEST');

	$r = @ldap_modify (F3::get('LDAP.conn'), "cn=". $data["cn"] . "," . F3::get('LDAP.ou'), $data);

	F3::reroute('/sorgu');
}
// Display the blog form again
F3::call(':editkul');

?>
