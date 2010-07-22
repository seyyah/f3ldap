<?php
require_once "inc/common.php";
require_once "inc/fun.php";

require "search.php";

$ds = myldap_connect($ldaphost, $ldapport);
$r  = myldap_bind ($ds, $ldapbdn, $ldappw);

$cn = "mahmut";
$newdata["sn"] = "ggg";

echo "<hr /><b>cn = $cn kullanicisi newdata = ";
echo myprint_r($newdata);
echo " ile modifiye ediliyor ...</b><br />";

$r = @ldap_modify ($ds, "cn=$cn,ou=moodleusers,".$ldapdn, $newdata);
echo $r ? "Basarili" : "UYARI: boyle bir kayit yok";

echo "<hr />Dizinlerin guncel hali...<br />";
require "search.php";

@ldap_close($ds);
?>

