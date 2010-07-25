<?php

F3::call(':ldap_login');

if (F3::get('SESSION.user')) {
	$sorgu = "cn=" . F3::get('PARAMS.cn') . "," . F3::get('LDAP.ou');
	$r = @ldap_delete (F3::get('LDAP.conn'), $sorgu);

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
