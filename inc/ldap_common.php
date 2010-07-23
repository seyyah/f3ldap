<?php

// LDAP variables

F3::set("ldaphost", "192.168.56.102");  // your ldap servers
F3::set("ldapport", 389);               // your ldap server's port number
F3::set("ldapdn", "dc=debuntu,dc=local");
F3::set("ldapbdn", "cn=admin," . F3::get("ldapdn"));
F3::set("ldappw", "secret");

// $ldaphost = "192.168.56.102";  // your ldap servers
// $ldapport = 389;               // your ldap server's port number
// $ldapdn = "dc=debuntu,dc=local";
// $ldapbdn = "cn=admin," . $ldapdn;
// $ldappw = "secret";
?>

