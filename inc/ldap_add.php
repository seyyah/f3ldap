<?php
F3::call(":ldap_search");

$data = array ("cn"=>"mustafa", "sn"=>"kuru", "telephoneNumber"=>1235, "postalCode"=>33321, "userPassword"=>"secret");

echo "<hr /><b>data = " . myprint_r($data) . "<br /> verisi ekleniyor...</b><br />";

$r = @myldap_add ($data);
echo $r ? "Eklendi": "UYARI: zaten varmis";

echo "<hr />Dizinlerin guncel hali...<br />";
F3::call(":ldap_search");

ldap_close(F3::get('LDAP.conn'));
?>
