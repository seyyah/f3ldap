<?php
F3::call(":ldap_search");

$cn = "mahmut";

echo "<hr /><b>cn = $cn kullanicisi siliniyor ...</b><br />";

$r = @ldap_delete (F3::get('LDAP.conn'), "cn=$cn," . F3::get('LDAP.ou'));
echo $r ? "Silindi" : "UYARI: kayit bulunamadi";

echo "<hr />Dizinlerin guncel hali...<br />";
F3::call(":ldap_search");

ldap_close(F3::get('LDAP.conn'));
?>
