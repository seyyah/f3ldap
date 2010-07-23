<?php
	$ldap = &F3::$global['LDAP'];
	
	if ($ldap['conn'])
		@ldap_close(F3::get('LDAP.conn'));

	myldap_connect();
	myldap_bind ();
?>
