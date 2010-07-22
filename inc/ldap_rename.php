<?php
require_once "inc/ldap_common.php";
require_once "inc/ldap_fun.php";

require "ldap_search.php";

$ds = myldap_connect($ldaphost, $ldapport);
$r  = myldap_bind ($ds, $ldapbdn, $ldappw);

$cn_old = "mahmut";
$cn_new = "murat";

echo "<hr /><b>cn = $cn_old kullanicisi \"$cn_new\" ile rename ediliyor ...</b><br />";

$r = @ldap_rename ($ds, "cn=$cn_old,ou=moodleusers,".$ldapdn, "cn=$cn_new", NULL, TRUE);
echo $r ? "Basarili" : "UYARI: boyle bir kayit bulunamadi";

echo "<hr />Dizinlerin guncel hali...<br />";
require "ldap_search.php";

@ldap_close($ds);
?>

