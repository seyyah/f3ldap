<?php
require_once "inc/ldap_common.php";
require_once "inc/ldap_fun.php";

require "ldap_search.php";

$ds = myldap_connect($ldaphost, $ldapport);
$r  = myldap_bind ($ds, $ldapbdn, $ldappw);

$data = array ("cn"=>"mahmut", "sn"=>"kuru", "telephoneNumber"=>1235, "postalCode"=>33321, "userPassword"=>"secret");

echo "<hr /><b>data = ";
echo myprint_r($data);
echo "<br /> verisi ekleniyor...</b><br />";

$r = @myldap_add ($ds, $ldapdn, $data);
echo $r ? "Eklendi": "UYARI: zaten varmis";

echo "<hr />Dizinlerin guncel hali...<br />";
require "ldap_search.php";

@ldap_close($ds);
?>

