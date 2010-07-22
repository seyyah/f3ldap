<?php
require_once "inc/common.php";
require_once "inc/fun.php";

$ds = myldap_connect($ldaphost, $ldapport);
$r  = myldap_bind ($ds, $ldapbdn, $ldappw);

$cn = "*";
$data = myldap_search($ds, "ou=moodleusers,".$ldapdn, "cn=$cn");

echo "<b>cn=$cn icin arama sonuclari ...</b> <br />";

for ($i=0; $i<$data["count"]; $i++) {
	$cn 		= $data[$i]["cn"];
	$sn 		= $data[$i]["sn"];
	$telephonenumber= $data[$i]["telephonenumber"];
	$postalcode 	= $data[$i]["postalcode"];
	$userpassword 	= $data[$i]["userpassword"];

	echo "CN = $cn, SN = $sn, TelephoneNumber = $telephonenumber, PostalCode = $postalcode, PASS = $userpassword";
	echo "<br />";
}

ldap_close($ds);
?>

