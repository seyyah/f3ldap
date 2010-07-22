<?php
require_once "inc/common.php";
require_once "inc/fun.php";

require "search.php";

$ds = myldap_connect($ldaphost, $ldapport);
$r  = myldap_bind ($ds, $ldapbdn, $ldappw);

$cn = "mahmut";

echo "<hr /><b>cn = $cn kullanicisi siliniyor ...</b><br />";

$r = @ldap_delete ($ds, "cn=$cn,ou=moodleusers,dc=debuntu,dc=local");
echo $r ? "Silindi" : "UYARI: kayit bulunamadi";

echo "<hr />Dizinlerin guncel hali...<br />";
require "search.php";

@ldap_close($ds);
?>

