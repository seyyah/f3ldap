<?php
F3::call(":ldap_search");

$data = array ("cn"=>"mustafa", "sn"=>"kuru", "telephonenumber"=>1235, "postalcode"=>33321, "userpassword"=>"secret");
echo "<hr /><b>data = " . myprint_r($data) . "<br /> verisi ekleniyor...</b><br />";

$r = @myldap_add ($data);
echo $r ? "Eklendi": "UYARI: zaten varmis";

echo "<hr />Dizinlerin guncel hali...<br />";
F3::call(":ldap_search");

ldap_close(F3::get('LDAP.conn'));
?>
