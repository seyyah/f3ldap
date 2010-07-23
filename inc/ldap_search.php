<?php
F3::call(':ldap_login');

$cn = "*";
$data = myldap_search("cn=$cn");

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

//ldap_close(F3::get('LDAP.conn'));
?>

