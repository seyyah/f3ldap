<?php
F3::call(":ldap_search");

$cn = "mahmut";
$password = "secret";

echo "<hr /><b>cn = $cn kullanicisi icin password = $password karsilastiriliyor ...</b><br />";

$r = myldap_compare_password ($cn, $password);
echo ($r == 0) ? "Basarili" : "Basarisiz";

echo "<hr />";

$cn = "mahmut";
$password = "falanfilan";

echo "<hr /><b>cn = $cn kullanicisi icin password = $password karsilastiriliyor ...</b><br />";

$r = myldap_compare_password ($cn, $password);
echo ($r == 0) ? "Basarili" : "Basarisiz";

echo "<hr />Dizinlerin guncel hali...<br />";
F3::call(":ldap_search");

ldap_close(F3::get('LDAP.conn'));
?>
