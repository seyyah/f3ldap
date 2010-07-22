<?php
require_once "inc/ldap_common.php";
require_once "inc/ldap_fun.php";

require "ldap_search.php";

$ds = myldap_connect($ldaphost, $ldapport);
$r  = myldap_bind ($ds, $ldapbdn, $ldappw);

$cn = "mahmut";

echo "<hr /><b>cn = $cn kullanicisi siliniyor ...</b><br />";

$r = @ldap_delete ($ds, "cn=$cn,ou=moodleusers,dc=debuntu,dc=local");
echo $r ? "Silindi" : "UYARI: kayit bulunamadi";

echo "<hr />Dizinlerin guncel hali...<br />";
require "ldap_search.php";

@ldap_close($ds);
?>

