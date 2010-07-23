<?php
F3::call(":ldap_search");

$cn = "mahmut";
$cn_new = "murat";

echo "<hr /><b>cn = $cn kullanicisi \"$cn_new\" ile rename ediliyor ...</b><br />";

$r = @ldap_rename (F3::get('LDAP.conn'), "cn=$cn," . F3::get('LDAP.ou'), "cn=$cn_new", NULL, TRUE);
echo $r ? "Basarili" : "UYARI: boyle bir kayit bulunamadi";

echo "<hr />Dizinlerin guncel hali...<br />";
F3::call(":ldap_search");

ldap_close(F3::get('LDAP.conn'));
?>
