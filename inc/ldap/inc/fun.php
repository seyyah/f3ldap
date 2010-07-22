<?php
function myprint_r ($expression) {
	return nl2br(print_r($expression, TRUE));
}

function ldap_escape($str, $for_dn = false)
{
    // see:
    // RFC2254
    // http://msdn.microsoft.com/en-us/library/ms675768(VS.85).aspx
    // http://www-03.ibm.com/systems/i/software/ldap/underdn.html       
       
    if  ($for_dn)
        $metaChars = array(',','=', '+', '<','>',';', '\\', '"', '#');
    else
        $metaChars = array('*', '(', ')', '\\', chr(0));

    $quotedMetaChars = array();
    foreach ($metaChars as $key => $value) $quotedMetaChars[$key] = '\\'.str_pad(dechex(ord($value)), 2, '0');
    $str=str_replace($metaChars,$quotedMetaChars,$str); //replace them
    return ($str);
} 

function HashPassword($password)
{
	mt_srand((double)microtime()*1000000);
	$salt = mhash_keygen_s2k(MHASH_SHA1, $password, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
	$hash = "{SSHA}".base64_encode(mhash(MHASH_SHA1, $password.$salt).$salt);

	return $hash;
}

function ComparePassword($password, $hash)
{
	$hash = base64_decode(substr($hash, 6));
	$original_hash = substr($hash, 0, 20);
	$salt = substr($hash, 20);
	$new_hash = mhash(MHASH_SHA1, $password . $salt);

	return strcmp($original_hash, $new_hash);
}

function myldap_connect ($host, $port) {
	// Connecting to LDAP
	$ds = ldap_connect($host, $port)
		or die("Could not connect to $host");

	if (! $ds) 
	    trigger_error("Unable to connect to LDAP server", E_WARNING);
	
	return $ds;
}

function myldap_bind ($ds, $dn, $password) {
	$r = ldap_bind($ds, $dn, $password);

	if (! $r ) 
		trigger_error("LDAP bind failed...", E_WARNING);
	
	return $r;
}

function myldap_search ($ds, $dn, $filter) {
	$dn = ldap_escape($dn);
	//$filter = ldap_escape($filter);	

	$sr = ldap_search($ds, $dn, $filter);
	$info = ldap_get_entries($ds, $sr);

	$data = array();
	$data["count"] = $info["count"];
	for ($i=0; $i<$info["count"]; $i++) {
		$entry = $info[$i];
		$data[$i] = array();
		$data[$i]["cn"] 		= $entry["cn"][0];
		$data[$i]["sn"] 		= $entry["sn"][0];
		$data[$i]["telephonenumber"]	= $entry["telephonenumber"][0];
		$data[$i]["postalcode"] 	= $entry["postalcode"][0];
		$data[$i]["userpassword"] 	= $entry["userpassword"][0];
	}

	return $data;
}

function myldap_add ($ds, $dn, $data) {
	// prepare data
	$info = array();
	$info["objectclass"][0] = "organizationalPerson";
	$info["objectclass"][1] = "person";
	$info["objectclass"][2] = "inetOrgPerson";
	$info["objectclass"][3] = "top";

	$info["cn"] = $data["cn"];
	$info["sn"] = $data["sn"];
	$info["telephoneNumber"] = $data["telephoneNumber"];
	$info["postalCode"] = $data["postalCode"];
	$info['userPassword'] = HashPassword($data["userPassword"]);

	// add data to directory
	$r = ldap_add($ds, "cn=".$info["cn"].",ou=moodleusers,".$dn, $info);
	
	return $r;
}

function myldap_compare_password ($ds, $dn, $cn, $password, $attr="userPassword") {
	$attr = strtolower($attr);

	$data = myldap_search($ds, $dn, "cn=$cn");
	
	return ComparePassword($password, $data[0][$attr]);
}

?>
