<?php

// LDAP variables
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
$ldaphost = "192.168.56.102";  // your ldap servers
$ldapport = 389;               // your ldap server's port number
$ldapdn = "dc=debuntu,dc=local";
$ldapbdn = "cn=admin," . $ldapdn;
$ldappw = "secret";

?>

