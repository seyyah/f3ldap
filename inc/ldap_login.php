<?php
	$ldap = &F3::$global['LDAP'];
	
	if ($ldap['conn'])
		@ldap_close(F3::get('LDAP.conn'));

	$ldap['conn'] = myldap_connect($ldap['host'], $ldap['port']);
	$ldap['bind'] = myldap_bind ($ldap['conn'], $ldap['admin'], $ldap['passw']);
?>
