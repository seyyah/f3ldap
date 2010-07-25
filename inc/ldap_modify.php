<?php
F3::call(":ldap_search");

$cn = "mahmut";
$newdata["sn"] = "ggg";

echo "<hr /><b>cn = $cn kullanicisi newdata = " ;
myprint_r($newdata);
echo " ile modifiye ediliyor ...</b><br />";

$r = @ldap_modify (F3::get('LDAP.conn'), "cn=$cn," . F3::get('LDAP.ou'), $newdata);
echo $r ? "Basarili" : "UYARI: boyle bir kayit yok";

echo "<hr />Dizinlerin guncel hali...<br />";
F3::call(":ldap_search");

ldap_close(F3::get('LDAP.conn'));
?>
